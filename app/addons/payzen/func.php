<?php
/**
 * Copyright © Lyra Network.
 * This file is part of PayZen for CS-CART. See COPYING.md for license details.
 *
 * @author    Lyra Network <https://www.lyra.com>
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/mit-license.html The MIT License (MIT)
 */

function fn_payzen_order_placement_routines($order_id, $force_notification, $order_info, $_error)
{
    if (key_exists('vads_hash', $_POST) && isset($_POST['vads_hash'])) {
        // IPN call : delete notifications and skip redirection.
        fn_delete_notification('');
        exit;
    }
}

$class_registry = class_exists('\Tygh\Registry') ? '\Tygh\Registry' : 'Registry';
require_once $class_registry::get('config.dir.functions') . 'fn.payzen.php';

function fn_payzen_install_payment_processors()
{
    global $payzen_plugin_features;

    db_query("REPLACE INTO ?:payment_processors (`processor`, `processor_script`, `processor_template`, `admin_template`, `callback`, `type`)
            VALUES ('PayZen - Standard payment', 'payzen.php', 'views/orders/components/payments/cc_outside.tpl', 'payzen.tpl', 'N', 'P');");

    if ($payzen_plugin_features['multi']) {
        db_query("REPLACE INTO ?:payment_processors (`processor`, `processor_script`, `processor_template`, `admin_template`, `callback`, `type`)
                VALUES ('PayZen - Payment in installments', 'payzen_multi.php', 'views/orders/components/payments/cc_outside.tpl', 'payzen_multi.tpl', 'N', 'P');");
    }
}

function fn_payzen_delete_payment_processors()
{
    db_query("DELETE FROM ?:payment_processors WHERE `processor_script` IN ('payzen.php', 'payzen_multi.php');");
}
