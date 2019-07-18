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
 * File:     function.payzen_get_plugin_features.php
 * Type:     function
 * Name:     payzen_get_plugin_features
 * Purpose: Get gateway features list.
 * Parameter:
 *    - var = name of a variable to which assign result
 * -------------------------------------------------------------
 */

function smarty_function_payzen_get_plugin_features($params, $template)
{
    $class_registry = class_exists('\Tygh\Registry') ? '\Tygh\Registry' : 'Registry';
    require_once $class_registry::get('config.dir.functions') . 'fn.payzen.php';

    global $payzen_plugin_features;
    $template->assign($params['var'], $payzen_plugin_features);
}
