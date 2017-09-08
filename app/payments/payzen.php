<?php
/**
 * PayZen V2-Payment Module version 2.0.0 for CS-Cart 4.x. Support contact : support@payzen.eu.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Lyra Network (http://www.lyra-network.com/)
 * @copyright 2014-2017 Lyra Network and contributors
 * @license   https://opensource.org/licenses/mit-license.html  The MIT License (MIT)
 * @category  payment
 * @package   payzen
 */

use Tygh\Registry;
use Tygh\Session;

if (!defined('BOOTSTRAP')) {
    if (!empty($_POST['vads_hash']) && !empty($_POST['vads_order_id']) && !empty($_POST['vads_order_info'])
        && !empty($_POST['vads_order_info2'])) {

        // set flag to allow session loading
        define('FORCE_SESSION_START', true);
        define('SKIP_SESSION_VALIDATION', true); // for backward compatibility

        require './init_payment.php';

        // restore initial session
        $id = substr($_POST['vads_order_info'], strlen('session_id='));
        Session::resetId($id);

        // restore initial shop
        $company_id = substr($_POST['vads_order_info2'], strlen('company_id='));
        if (function_exists('fn_payments_set_company_id')) {
            fn_payments_set_company_id(0, $company_id);
        } else {
            Registry::set('runtime.company_id', $company_id); // for backward compatibility
        }

        // set info that lets this script complete the process
        define('PAYMENT_NOTIFICATION', true);
        $mode = 'process';
    } else {
        die('Access denied');
    }
}

if (defined('PAYMENT_NOTIFICATION')) {
    // retrieve payment processor data
    $processor_data = array();

    if ($mode == 'process' && fn_check_payment_script('payzen.php', $_REQUEST['vads_order_id'], $processor_data)) {
        // load PayZen Response
        require_once 'payzen/payzen_response.php';
        $payzenResponse = new PayzenResponse(
                $_REQUEST,
                $processor_data['processor_params']['payzen_ctx_mode'],
                $processor_data['processor_params']['payzen_key_test'],
                $processor_data['processor_params']['payzen_key_prod']
        );

        $fromServer = $payzenResponse->get('hash') != null;

        if (!$payzenResponse->isAuthentified()) {
            if ($fromServer) {
                die($payzenResponse->getOutputForPlatform('auth_fail'));
            } else {
                fn_delete_notification('');
                fn_set_notification('E', __('error'), __('payzen_tech_error_msg'), 'S');
                fn_redirect(fn_url('checkout.cart'));
                die();
            }
        }

        // retrieve order info from DB
        $order_id = $payzenResponse->get('order_id');
        $order_info = fn_get_order_info($order_id);

        if (!is_array($order_info) || empty($order_info)) {
            if ($fromServer) {
                die($payzenResponse->getOutputForPlatform('order_not_found'));
            } else {
                fn_delete_notification('');
                fn_set_notification('E', __('error'), __('payzen_tech_error_msg'), 'S');
                fn_redirect(fn_url('checkout.cart'));
                die();
            }
        }

        // show passing to production notification in TEST mode
        if (!$fromServer && $processor_data['processor_params']['payzen_ctx_mode'] == 'TEST') {
            fn_set_notification(
                'N',
                __('notice'),
                __('payzen_pass_to_prod_info') . ' <a href="https://secure.payzen.eu/html/faq/prod" target="_blank">https://secure.payzen.eu/html/faq/prod</a>',
                'S'
            );
        }

        // get status configured in module backend
        $successStatus = $processor_data['processor_params']['payzen_registered_status'];

        require_once Registry::get('config.dir.functions') . 'fn.payzen.php';
        $newStatus = fn_payzen_convert_trans_status($payzenResponse->getTransStatus(), $successStatus);

        if ($order_info['status'] != $successStatus && $order_info['status'] != $newStatus /* status changed */) {
            // order not processed yet
            $pp_response = array();
            $pp_response['transaction_id'] = $payzenResponse->get('trans_id');
            $pp_response['reason_text'] = $payzenResponse->getMessage();
            $pp_response['order_status'] = $newStatus;

            $pp_response['card'] = $payzenResponse->get('card_brand');
            $pp_response['card_number'] = $payzenResponse->get('card_number');
            $pp_response['expiry_month'] = $payzenResponse->get('expiry_month');
            $pp_response['expiry_year'] = $payzenResponse->get('expiry_year');

            fn_finish_payment($order_id, $pp_response, true);

            if ($fromServer) {
                $msg = $payzenResponse->isAcceptedPayment() ? 'payment_ok' : 'payment_ko';
                echo $payzenResponse->getOutputForPlatform($msg);
            } else {
                // display a warning about check URL not working
                if (!$payzenResponse->isCancelledPayment() && $processor_data['processor_params']['payzen_ctx_mode'] == 'TEST') {
                    fn_set_notification('W', '', __('payzen_url_check_warn') . '<br />' .  __('payzen_url_check_details'), 'S');
                }
            }

            fn_order_placement_routines('route', $order_id, false, $payzenResponse->isAcceptedPayment());
        } else {
            // order already processed

            if ($order_info['status'] == $successStatus) {
                if ($payzenResponse->isAcceptedPayment()) {
                    if ($fromServer){
                        die ($payzenResponse->getOutputForPlatform('payment_ok_already_done'));
                    } else {
                        fn_order_placement_routines('route', $order_id, false, true);
                    }
                } else {
                    if ($fromServer){
                        die ($payzenResponse->getOutputForPlatform('payment_ko_on_order_ok'));
                    } else {
                        fn_delete_notification('');
                        fn_set_notification('E', __('error'), __('payzen_tech_error_msg'), 'S');
                        fn_redirect(fn_url('checkout.cart'));
                        die();
                    }
                }
            } else {
                if ($fromServer){
                    die ($payzenResponse->getOutputForPlatform('payment_ko_already_done'));
                } else {
                    fn_order_placement_routines('route', $order_id, false, false);
                }
            }
        }
    }
} else {
    // use our custom class to generate the HTML
    require_once 'payzen/payzen_request.php';
    $payzenRequest = new PayzenRequest('UTF-8');

    $params = $processor_data['processor_params'];

    // admin configuration parameters
    $configParams = array(
            'site_id', 'key_test', 'key_prod', 'ctx_mode', 'platform_url', 'available_languages',
            'capture_delay', 'validation_mode', 'payment_cards', 'redirect_enabled',
            'redirect_success_timeout', 'redirect_success_message', 'redirect_error_timeout',
            'redirect_error_message', 'return_mode'
    );

    foreach ($configParams as $name) {
        $value = key_exists('payzen_' . $name, $params) ? $params['payzen_' . $name] : '';
        if (is_array($value)) {
            $value = implode(';', $value);
        }

        $payzenRequest->set($name, $value);
    }

    // get the shop language code
    $lang = strtolower($order_info['lang_code']);
    $payzenLanguage = PayzenApi::isSupportedLanguage($lang) ? $lang : $params['payzen_language'];

    // get the currency to use
    $payzenCurrency = PayzenApi::findCurrencyByAlphaCode(CART_SECONDARY_CURRENCY);
    if (!$payzenCurrency) {
        // current currency is not supported, use the default shop currency
        $payzenCurrency = PayzenApi::findCurrencyByAlphaCode(CART_PRIMARY_CURRENCY);
    }

    // cs cart currency info
    $currencies = Registry::get('currencies');
    $currency = $currencies[$payzenCurrency->getAlpha3()];

    // calculate float amount ...
    $total = round($order_info['total'] * 1 / $currency['coefficient'], $currency['decimals']);

    // activate 3ds ?
    $threedsMpi = null;
    if ($params['payzen_3ds_min_amount'] != '' && $order_info['total'] < $params['payzen_3ds_min_amount']) {
        $threedsMpi = '2';
    }

    // other parameters
    $data = array(
            // order info
            'amount' => $payzenCurrency->convertAmountToInteger($total), // amount in cents
            'order_id' => $order_info['order_id'],
            'contrib' => 'CS-Cart4.x_2.0.0/' . PRODUCT_VERSION,
            'order_info' => 'session_id=' . Session::getId(),
            'order_info2' => 'company_id=' . Registry::get('runtime.company_id'),

            // misc data
            'currency' => $payzenCurrency->getNum(),
            'language' => $payzenLanguage,
            'threeds_mpi' => $threedsMpi,
            'url_return' => fn_url('payment_notification.process?payment=payzen'),

            // customer info
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

    // delivery data
    if ($order_info['need_shipping']) {
        $data['ship_to_first_name'] = $order_info['s_firstname'];
        $data['ship_to_last_name'] = $order_info['s_lastname'];
        $data['ship_to_street'] = $order_info['s_address'];
        $data['ship_to_street2'] = $order_info['s_address_2'];
        $data['ship_to_city'] = $order_info['s_city'];
        $data['ship_to_state'] = $order_info['s_state'];
        $data['ship_to_country'] = $order_info['s_country'];
        $data['ship_to_zip'] = $order_info['s_zipcode'];
    }

    $payzenRequest->setFromArray($data);

    // message to be shown when forwarding to payment platform
    $msg = __('text_cc_processor_connection', array('[processor]' => 'PayZen'));

echo <<<EOT
        <form action="{$payzenRequest->getRequestUrl()}" method="POST" name="payzen_form">
            {$payzenRequest->getRequestHtmlFields()}
        </form>

        <div align=center>{$msg}</div>

        <script type="text/javascript">
            window.onload = function() {
                document.payzen_form.submit();
            };
        </script>
    </body>
</html>
EOT;
}

exit;
