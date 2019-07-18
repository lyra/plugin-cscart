<?php
/**
 * Copyright © Lyra Network.
 * This file is part of PayZen for CS-CART. See COPYING.md for license details.
 *
 * @author    Lyra Network <https://www.lyra.com>
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/mit-license.html The MIT License (MIT)
 */

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:    function.payzen_get_list.php
 * Type:    function
 * Name:    payzen_get_list
 * Purpose: Get gateway supported languages or card types according to 'what' parameter
 *          and assign it to a variable whose name is passed in 'to' parameter.
 * Parameter:
 *    - what = what to get (langs or cards) from gatway API (required)
 *    - to   = name of a variable to which assign result (if none return it)
 * -------------------------------------------------------------
 */

function smarty_function_payzen_get_list($params, $template)
{
    if (empty($params['what'])) {
        trigger_error('[plugin] payzen_get parameter "what" cannot be empty.', E_USER_NOTICE);
        return;
    }

    $class_registry = class_exists('\Tygh\Registry') ? '\Tygh\Registry' : 'Registry';
    require_once $class_registry::get('config.dir.payments') . 'payzen/payzen_api.php';

    $result = array();

    if ($params['what'] === 'langs') {
        $result = PayzenApi::getSupportedLanguages();
    } elseif ($params['what'] === 'cards') {
        $result = PayzenApi::getSupportedCardTypes();
    } else {
        trigger_error('[plugin] payzen_get parameter "what" is invalid, expected values are "langs" and "cards".', E_USER_NOTICE);
        return;
    }

    if (empty($params['to'])) {
        return $result;
    } else {
        $template->assign($params['to'], $result);
    }
}
