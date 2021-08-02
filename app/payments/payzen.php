<?php
/**
 * Copyright © Lyra Network.
 * This file is part of PayZen for CS-CART. See COPYING.md for license details.
 *
 * @author    Lyra Network <https://www.lyra.com>
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/mit-license.html The MIT License (MIT)
 */

$context_initialized = false;

if (!defined('BOOTSTRAP')) {
    if (isset($_POST['vads_hash']) && !empty($_POST['vads_hash']) &&
        isset($_POST['vads_order_id']) && !empty($_POST['vads_order_id']) &&
        isset($_POST['vads_ext_info_session_id']) && !empty($_POST['vads_ext_info_session_id']) &&
        isset($_POST['vads_ext_info_company_id']) && !empty($_POST['vads_ext_info_company_id'])) {
        // Set flag to allow session loading.
        define('FORCE_SESSION_START', true);
        define('SKIP_SESSION_VALIDATION', true); // For backward compatibility.

        require './init_payment.php';

        $class_session = class_exists('\Tygh\Session') ? '\Tygh\Session' : 'Session';
        $class_registry = class_exists('\Tygh\Registry') ? '\Tygh\Registry' : 'Registry';

        // Restore initial session.
        $class_session::resetId($_POST['vads_ext_info_session_id']);

        // Restore initial shop.
        $company_id = $_POST['vads_ext_info_company_id'];
        if (function_exists('fn_payments_set_company_id')) {
            fn_payments_set_company_id(0, $company_id);
        } else {
            $class_registry::set('runtime.company_id', $company_id); // For backward compatibility.
        }

        $context_initialized = true;

        // Set info that lets this script complete the process.
        define('PAYMENT_NOTIFICATION', true);
        $mode = 'process';
    } else {
        die('Access denied');
    }
}

if (!$context_initialized) {
    $class_session = class_exists('\Tygh\Session') ? '\Tygh\Session' : 'Session';
    $class_registry = class_exists('\Tygh\Registry') ? '\Tygh\Registry' : 'Registry';
}

require_once $class_registry::get('config.dir.functions') . 'fn.payzen.php';
$logger = fn_payzen_logger();

global $payzen_plugin_features, $payzen_default_values;

if (defined('PAYMENT_NOTIFICATION')) {
    // Retrieve payment processor data.
    $processor_data = array();

    //check order id
    $req_order_id = (isset($_REQUEST['vads_order_id']) && is_numeric($_REQUEST['vads_order_id'])) ? $_REQUEST['vads_order_id'] : '';
    if (($mode === 'process') && (fn_check_payment_script('payzen.php', $req_order_id, $processor_data)
        || fn_check_payment_script('payzen_multi.php', $req_order_id, $processor_data))) {
        // Load gateway response.
        require_once 'payzen/payzen_response.php';

        $payzen_response = new PayzenResponse(
            $_REQUEST,
            $processor_data['processor_params']['payzen_ctx_mode'],
            $processor_data['processor_params']['payzen_key_test'],
            $processor_data['processor_params']['payzen_key_prod'],
            $processor_data['processor_params']['payzen_sign_algo']
        );

        $from_server = $payzen_response->get('hash') !== null;

        if (!$payzen_response->isAuthentified()) {
            $logger->write('Authentication failed: received invalid response with parameters: ' . print_r($_REQUEST, true));
            $logger->write('Signature algorithm selected in module settings must be the same as one selected in gateway Back Office.');

            if ($from_server) {
                $logger->write('IPN URL PROCESS END.');
                fn_payzen_die($payzen_response->getOutputForGateway('auth_fail'));
            } else {
                fn_delete_notification('');
                fn_set_notification('E', __('error'), __('payzen_tech_error_msg'), 'S');

                $logger->write('RETURN URL PROCESS END.');
                fn_redirect(fn_url('checkout.cart'));
                fn_payzen_die();
            }
        }

        // Retrieve order info from DB.
        $order_id = $payzen_response->get('order_id');
        $order_info = fn_get_order_info($order_id);

        if (!is_array($order_info) || empty($order_info)) {
            $logger->write("Error: order #$order_id not found in database.");

            if ($from_server) {
                $logger->write('IPN URL PROCESS END.');
                fn_payzen_die($payzen_response->getOutputForGateway('order_not_found'));
            } else {
                fn_delete_notification('');
                fn_set_notification('E', __('error'), __('payzen_tech_error_msg'), 'S');

                $logger->write('RETURN URL PROCESS END.');
                fn_redirect(fn_url('checkout.cart'));
                fn_payzen_die();
            }
        }

        // Get the choice of the card.
        $brand_info = json_decode($payzen_response->get('brand_management'));
        $msg_brand_choice = "\n";
        if (isset($brand_info->userChoice) && $brand_info->userChoice) {
            $msg_brand_choice .= '(' . __('payzen_buyer_choice') . ')';
        } else {
            $msg_brand_choice .= '(' . __('payzen_default_choice') . ')';
        }

        // Show passing to production notification in TEST mode.
        if (!$from_server && ($processor_data['processor_params']['payzen_ctx_mode'] === 'TEST') && $payzen_plugin_features['prodfaq']) {
            fn_set_notification(
                'N',
                __('notice'),
                __('payzen_pass_to_prod_info'),
                'S'
            );
        }

        // Get status configured in module backend.
        $success_status = $processor_data['processor_params']['payzen_registered_status'];

        $new_status = fn_payzen_convert_trans_status($payzen_response->getTransStatus(), $success_status);

        if (($order_info['status'] !== $success_status) && ($order_info['status'] !== $new_status)) { // Status changed.
            // Order not processed yet.
            $pp_response = array();

            if (!$payzen_response->isCancelledPayment()) {
                $pp_response['transaction_id'] = $payzen_response->get('trans_id');
                $pp_response['order_status'] = $new_status;

                $pp_response['card'] = $payzen_response->get('card_brand') . $msg_brand_choice;
                $pp_response['card_number'] = $payzen_response->get('card_number');
                $pp_response['expiry_month'] = $payzen_response->get('expiry_month');
                $pp_response['expiry_year'] = $payzen_response->get('expiry_year');
            }

            fn_finish_payment($order_id, $pp_response, true);

            if ($from_server) {
                if ($payzen_response->isAcceptedPayment()) {
                    $logger->write("Payment processed successfully by IPN URL call for order #$order_id.");
                    $msg = 'payment_ok';
                } else {
                    $logger->write("Payment failed or cancelled for order #$order_id. {$payzen_response->getLogMessage()}");
                    $msg = 'payment_ko';
                }

                $logger->write('IPN URL PROCESS END.');
                fn_payzen_echo($payzen_response->getOutputForGateway($msg));
            } else {
                if ($payzen_response->isAcceptedPayment()) {
                    $logger->write("Warning! IPN URL call has not worked. Payment completed by return URL call for order #$order_id.");
                } else {
                    $logger->write("Payment failed or cancelled for order #$order_id. {$payzen_response->getLogMessage()}");
                }

                // Display a warning about check URL not working.
                if (!$payzen_response->isCancelledPayment() && ($processor_data['processor_params']['payzen_ctx_mode'] === 'TEST')) {
                    fn_set_notification('W', '', __('payzen_url_check_warn') . '<br />' .  __('payzen_url_check_details'), 'S');
                }

                $logger->write('RETURN URL PROCESS END.');
            }

            fn_order_placement_routines('route', $order_id, false, $payzen_response->isAcceptedPayment());
        } else {
            // Order already processed.
            $logger->write("Order #$order_id is already saved.");

            if ($order_info['status'] === $success_status) {
                if ($payzen_response->isAcceptedPayment()) {
                    $logger->write("Payment successful confirmed for order #$order_id.");
                    if ($from_server){
                        $logger->write('IPN URL PROCESS END.');
                        fn_payzen_die ($payzen_response->getOutputForGateway('payment_ok_already_done'));
                    } else {
                        $logger->write('RETURN URL PROCESS END.');
                        fn_order_placement_routines('route', $order_id, false, true);
                    }
                } else {
                    $logger->write("Error! Invalid payment result received for already saved order #$order_id. Payment result : {$payzen_response->getTransStatus()}, Order status : {$order_info['status']}.");
                    if ($from_server){
                        $logger->write('IPN URL PROCESS END.');
                        fn_payzen_die ($payzen_response->getOutputForGateway('payment_ko_on_order_ok'));
                    } else {
                        fn_delete_notification('');
                        fn_set_notification('E', __('error'), __('payzen_tech_error_msg'), 'S');

                        $logger->write('RETURN URL PROCESS END.');
                        fn_redirect(fn_url('checkout.cart'));
                        fn_payzen_die();
                    }
                }
            } else {
                $logger->write("Payment failed or cancelled confirmed for order #$order_id.");

                if ($from_server){
                    $logger->write('IPN URL PROCESS END.');
                    fn_payzen_die ($payzen_response->getOutputForGateway('payment_ko_already_done'));
                } else {
                    $logger->write('RETURN URL PROCESS END.');
                    fn_order_placement_routines('route', $order_id, false, false);
                }
            }
        }
    }
} else {
    // Use our custom class to generate the HTML.
    require_once 'payzen/payzen_request.php';
    $payzen_request = new PayzenRequest();

    $params = $processor_data['processor_params'];

    $logger->write('Generating payment form for order #' . $order_info['order_id'] . '.');

    // Admin configuration parameters.
    $config_params = array(
        'site_id', 'key_test', 'key_prod', 'ctx_mode', 'platform_url', 'available_languages',
        'capture_delay', 'validation_mode', 'payment_cards', 'redirect_enabled',
        'redirect_success_timeout', 'redirect_success_message', 'redirect_error_timeout',
        'redirect_error_message', 'return_mode','sign_algo'
    );

    foreach ($config_params as $name) {
        $value = key_exists('payzen_' . $name, $params) ? $params['payzen_' . $name] : '';
        if (is_array($value)) {
            $value = implode(';', $value);
        }

        $payzen_request->set($name, $value);
    }

    // Get the shop language code.
    $lang = strtolower($order_info['lang_code']);
    $payzen_language = PayzenApi::isSupportedLanguage($lang) ? $lang : $params['payzen_language'];

    // Get the currency to use.
    $payzen_currency = PayzenApi::findCurrencyByAlphaCode(CART_SECONDARY_CURRENCY);
    if (!$payzen_currency) {
        // Current currency is not supported, use the default shop currency.
        $payzen_currency = PayzenApi::findCurrencyByAlphaCode(CART_PRIMARY_CURRENCY);
    }

    // CS-Cart currency info.
    $currencies = $class_registry::get('currencies');
    $currency = $currencies[$payzen_currency->getAlpha3()];

    // Calculate float amount.
    $total = round($order_info['total'] * 1 / $currency['coefficient'], $currency['decimals']);

    // Activate 3DS?
    $threeds_mpi = null;
    if ($params['payzen_3ds_min_amount'] && $order_info['total'] < $params['payzen_3ds_min_amount']) {
        $threeds_mpi = '2';
    }

    $plugin_param = $payzen_default_values['cms_identifier'] . '_' . $payzen_default_values['plugin_version'];

    // Other parameters.
    $data = array(
        // Order info.
        'amount' => $payzen_currency->convertAmountToInteger($total), // Amount in cents.
        'order_id' => $order_info['order_id'],
        'contrib' => $plugin_param . '/' . PRODUCT_VERSION . '/' . PayzenApi::shortPhpVersion(),

        // Misc data.
        'currency' => $payzen_currency->getNum(),
        'language' => $payzen_language,
        'threeds_mpi' => $threeds_mpi,
        'url_return' => fn_url('payment_notification.process?payment=payzen'),

        // Customer info.
        'cust_id' => $order_info['user_id'],
        'cust_email' => $order_info['email'],

        'cust_first_name' => $order_info['b_firstname'],
        'cust_last_name' => $order_info['b_lastname'],
        'cust_address' => $order_info['b_address'] . ' ' . $order_info['b_address_2'],
        'cust_city' => $order_info['b_city'],
        'cust_state' => $order_info['b_state'],
        'cust_zip' => $order_info['b_zipcode'],
        'cust_country' => $order_info['b_country'],
        'cust_phone' => $order_info['b_phone']
    );

    // Delivery data.
    if ($order_info['need_shipping']) {
        $data['ship_to_first_name'] = $order_info['s_firstname'];
        $data['ship_to_last_name'] = $order_info['s_lastname'];
        $data['ship_to_street'] = $order_info['s_address'];
        $data['ship_to_street2'] = $order_info['s_address_2'];
        $data['ship_to_city'] = $order_info['s_city'];
        $data['ship_to_state'] = $order_info['s_state'];
        $data['ship_to_country'] = $order_info['s_country'];
        $data['ship_to_zip'] = $order_info['s_zipcode'];
        $data['ship_to_phone_num'] = $order_info['s_phone'];
    }

    $payzen_request->setFromArray($data);

    $payzen_request->addExtInfo('session_id', $class_session::getId());
    $payzen_request->addExtInfo('company_id', $class_registry::get('runtime.company_id'));

    // Log data that will be sent to payment gateway.
    $logger->write('Data to be sent to payment gateway : ' . print_r($payzen_request->getRequestFieldsArray(true /* To hide sensitive data. */), true));

    // Message to be shown when forwarding to payment platform.
    $msg = __('text_cc_processor_connection', array('[processor]' => 'PayZen'));

    $form_content = <<<EOT
        <form action="{$payzen_request->get('platform_url')}" method="POST" name="payzen_form">
            {$payzen_request->getRequestHtmlFields()}
        </form>

        <div style="text-align: center;">{$msg}</div>

        <script type="text/javascript">
            window.onload = function() {
                document.payzen_form.submit();
            };
        </script>
    </body>
</html>
EOT;

    fn_payzen_echo($form_content);
}

fn_payzen_exit(0);
