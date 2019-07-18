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
