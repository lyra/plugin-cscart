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
    'shatwo' => true,

    'multi' => true,
    'restrictmulti' => false
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
    'plugin_version' => '2.2.0',
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

function fn_payzen_get_class($classname){
    switch ($classname){
        case 'Session':
            return class_exists('\Tygh\Session') ? '\Tygh\Session' : 'Session';

        case 'Registry':
            return class_exists('\Tygh\Registry') ? '\Tygh\Registry' : 'Registry';

        case 'Logger':
            return class_exists('\Tygh\Logger') ? '\Tygh\Logger' : 'Logger';

        default:
            return '';
    }
}

function fn_payzen_get_logger_path(){
    $class_registry = fn_payzen_get_class('Registry');

    $log_dir = $class_registry::get('config.dir.var'). 'logs/';
    if (!is_dir($log_dir)) {
        fn_mkdir($log_dir);
    }

    return $log_dir . 'payzen-' . date('Y-m') . '.log';
}

function fn_payzen_logger()
{
    $class_logger = fn_payzen_get_class('Logger');

    $logger = $class_logger::instance();
    $logger->__set('logfile', fn_payzen_get_logger_path());

    return $logger;
}

function fn_payzen_die($message)
{
    die($message);
}

function fn_payzen_exit($message)
{
    exit($message);
}

function fn_payzen_echo($param)
{
    echo $param;
}
