<?php
/**
 * Copyright © Lyra Network.
 * This file is part of PayZen for CS-CART. See COPYING.md for license details.
 *
 * @author    Lyra Network <https://www.lyra.com>
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/mit-license.html The MIT License (MIT)
 */
$class_session = class_exists('\Tygh\Session') ? '\Tygh\Session' : 'Session';
$class_registry = class_exists('\Tygh\Registry') ? '\Tygh\Registry' : 'Registry';

require_once $class_registry::get('config.dir.functions') . 'fn.payzen.php';
$logger = fn_payzen_logger();

global $payzen_default_values;

if (!defined('PAYMENT_NOTIFICATION')) {
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

    // set multi payment options
    $first = (isset($params['payzen_multi_first']) && $params['payzen_multi_first']) ?
        (int) (string) (($params['payzen_multi_first'] / 100) * $data['amount']) /* amount is in cents*/ : null;

    $payzen_request->setMultiPayment(
        null /* use already set amount */,
        $first, $params['payzen_multi_count'],
        $params['payzen_multi_period']
    );

    // Log data that will be sent to payment gateway.
    $logger->write('here we Data to be sent to payment gateway : ' . print_r($payzen_request->getRequestFieldsArray(true /* To hide sensitive data. */), true));

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
