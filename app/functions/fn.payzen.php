<?php
/**
 * Copyright © Lyra Network.
 * This file is part of PayZen for CS-CART. See COPYING.md for license details.
 *
 * @author    Lyra Network <https://www.lyra.com>
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/mit-license.html The MIT License (MIT)
 */

global $payzen_plugin_features, $payzen_default_values;

$payzen_plugin_features = array(
    'qualif' => false,
    'prodfaq' => true,
    'shatwo' => true
);

$payzen_default_values = array(
    'gateway_code' => 'PayZen',
    'gateway_name' => 'PayZen',
    'backoffice_name' => 'PayZen',
    'gateway_url' => 'https://secure.payzen.eu/vads-payment/',
    'site_id' => '12345678',
    'key_test' => '1111111111111111',
    'key_prod' => '2222222222222222',
    'ctx_mode' => 'TEST',
    'sign_algo' => 'SHA-256',
    'language' => 'fr',

    'cms_identifier' => 'CS-Cart_4.x',
    'support_email' => 'support@payzen.eu',
    'plugin_version' => '2.1.0',
    'gateway_version' => 'V2'
);

function fn_payzen_convert_trans_status($payzen_status, $success_status = 'P')
{
    switch ($payzen_status) {
        case 'INITIAL':
        case 'WAITING_AUTHORISATION_TO_VALIDATE':
        case 'WAITING_AUTHORISATION':
            return 'O'; // Open.

        case 'AUTHORISED_TO_VALIDATE':
        case 'AUTHORISED':
        case 'CAPTURE_FAILED':
        case 'CAPTURED':
            return $success_status; // Status configured in plugin backend.

        case 'CANCELLED':
        case 'ABANDONED':
            return 'I'; // Canceled.

        case 'EXPIRED':
        case 'NOT_CREATED':
        case 'REFUSED':
        default :
            return 'F'; // Failed.
    }
}

function fn_payzen_logger()
{
    $class_registry = class_exists('\Tygh\Registry') ? '\Tygh\Registry' : 'Registry';
    $class_logger = class_exists('\Tygh\Logger') ? '\Tygh\Logger' : 'Logger';

    $logger = $class_logger::instance();
    $log_dir = $class_registry::get('config.dir.var'). 'logs/';
    if (!is_dir($log_dir)) {
        fn_mkdir($log_dir);
    }

    $logger->__set('logfile', $log_dir . 'payzen-' . date('Y-m') . '.log');

    return $logger;
}
