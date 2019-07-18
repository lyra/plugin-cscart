{*
 * Copyright © Lyra Network.
 * This file is part of PayZen for CS-CART. See COPYING.md for license details.
 *
 * @author    Lyra Network <https://www.lyra.com>
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/mit-license.html The MIT License (MIT)
 *}

{assign var="check_url" value="`$config.current_location`/app/payments/payzen.php"}

{payzen_get_default_values var="payzen_default_values"}

<fieldset>
    <legend style="margin-bottom: 0;">
        <font style="font-size: 14px; font-weight: bold;">{__("payzen_module_information")}</font>
    </legend>

    <table>
        <tr>
            <td style="width: 200px; text-align:right;">{__("payzen_developed_by")} : </td>
            <td><a href="https://www.lyra.com/" target="_blank">Lyra network</a></td>
        </tr>
        <tr>
            <td style="width: 200px; text-align:right;">{__("payzen_contact_email")} : </td>
            <td><a href="mailto:{$payzen_default_values.support_email}">{$payzen_default_values.support_email}</a></td>
        </tr>
        <tr>
            <td style="width: 200px; text-align:right;">{__("payzen_contrib_version")} : </td>
            <td>{$payzen_default_values.plugin_version}</td>
        </tr>
        <tr>
            <td style="width: 200px; text-align:right;">{__("payzen_gateway_version")} : </td>
            <td>{$payzen_default_values.gateway_version}</td>
        </tr>
    </table>
</fieldset>


<fieldset>
    <legend style="margin-bottom: 0;">
        <font style="font-size: 14px; font-weight: bold;">{__("payzen_gateway_access")}</font>
    </legend>

    <div class="control-group">
        <label class="control-label" for="payzen_site_id">{__("payzen_site_id")}</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][payzen_site_id]" id="payzen_site_id" style="width: 100px;" {if !$processor_params.payzen_site_id} value="{$payzen_default_values.site_id}" {else} value="{$processor_params.payzen_site_id}" {/if} autocomplete="off">
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_site_id_desc")}</p>
        </div>
    </div>

    {payzen_get_plugin_features var="payzen_plugin_features"}

    {if !$payzen_plugin_features.qualif}
        <div class="control-group">
            <label class="control-label" for="payzen_key_test">{__("payzen_key_test")}</label>
            <div class="controls">
                <input type="text" name="payment_data[processor_params][payzen_key_test]" id="payzen_key_test" style="width: 200px;" {if !$processor_params.payzen_key_test} value="{$payzen_default_values.key_test}" {else} value="{$processor_params.payzen_key_test}" {/if} autocomplete="off">
                <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_key_test_desc")}</p>
            </div>
        </div>
    {/if}

    <div class="control-group">
        <label class="control-label" for="payzen_key_prod">{__("payzen_key_prod")}</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][payzen_key_prod]" id="payzen_key_prod" style="width: 200px;" {if !$processor_params.payzen_key_prod} value="{$payzen_default_values.key_prod}" {else} value="{$processor_params.payzen_key_prod}" {/if} autocomplete="off">
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_key_prod_desc")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="payzen_ctx_mode">{__("payzen_ctx_mode")}</label>
        <div class="controls">
            <select name="payment_data[processor_params][payzen_ctx_mode]" id="payzen_ctx_mode">
                {if !$payzen_plugin_features.qualif}
                    <option value="TEST" {if (!isset($processor_params.payzen_ctx_mode) && $payzen_default_values.ctx_mode === 'TEST') || $processor_params.payzen_ctx_mode === "TEST"} selected="selected" {/if}>{__("payzen_ctx_mode_test")}</option>
                {/if}
                <option value="PRODUCTION" {if (!isset($processor_params.payzen_ctx_mode) && $payzen_default_values.ctx_mode === 'PRODUCTION') || $processor_params.payzen_ctx_mode === "PRODUCTION"} selected="selected" {/if}>{__("payzen_ctx_mode_prod")}</option>
            </select>
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_ctx_mode_desc")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="payzen_sign_algo">{__("payzen_sign_algo")}</label>
        <div class="controls">
            <select name="payment_data[processor_params][payzen_sign_algo]" id="payzen_sign_algo">
                <option value="SHA-1" {if (!isset($processor_params.payzen_sign_algo) && $payzen_default_values.sign_algo === 'SHA-1') || $processor_params.payzen_sign_algo === "SHA-1"} selected="selected" {/if}>SHA-1</option>
                <option value="SHA-256" {if (!isset($processor_params.payzen_sign_algo) && $payzen_default_values.sign_algo === 'SHA-256') || $processor_params.payzen_sign_algo === "SHA-256"} selected="selected" {/if}>HMAC-SHA-256</option>
            </select>
            <p style="font-size: 12px; font-style: italic; color: #666666;">
            {__("payzen_sign_algo_desc1")}
            {if !$payzen_plugin_features.shatwo}
                {__("payzen_sign_algo_desc2")}
            {/if}
            </p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="payzen_check_url">{__("payzen_check_url")}</label>
        <div class="controls">
            <b>{$check_url}</b>
            <p style="font-size: 12px; font-style: italic; color: #666666;">
                <span class="ui-icon ui-icon-alert" style="vertical-align: middle; display: inline-block;"></span><span class="text">{__("payzen_check_url_desc")}</span>
            </p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="payzen_platform_url">{__("payzen_platform_url")}</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][payzen_platform_url]" id="payzen_platform_url" style="width: 400px;" {if !$processor_params.payzen_platform_url} value="{$payzen_default_values.gateway_url}" {else} value="{$processor_params.payzen_platform_url}" {/if}>
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_platform_url_desc")}</p>
        </div>
    </div>
</fieldset>


<fieldset>
    <legend style="margin-bottom: 0;">
        <font style="font-size: 14px; font-weight: bold;">{__("payzen_payment_page")}</font>
    </legend>

    {payzen_get_list what="langs" to="supported_languages"}
    <div class="control-group">
        <label class="control-label" for="payzen_language">{__("payzen_language")}</label>
        <div class="controls">
            <select name="payment_data[processor_params][payzen_language]" id="payzen_language">
                {foreach from=$supported_languages key="code" item="lang"}
                <option value="{$code}" {if (!isset($processor_params.payzen_language) && $payzen_default_values.language === $code) || $processor_params.payzen_language === $code} selected="selected" {/if}>{__("payzen_lang_`$code`")}</option>
                {/foreach}
            </select>
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_language_desc")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="payzen_available_languages">{__("payzen_available_languages")}</label>
        <div class="controls">
            <select name="payment_data[processor_params][payzen_available_languages][]" id="payzen_available_languages" multiple="multiple">
                {foreach from=$supported_languages key="code" item="lang"}
                <option value="{$code}" {if $code|in_array:$processor_params.payzen_available_languages} selected="selected" {/if}>{__("payzen_lang_`$code`")}</option>
                {/foreach}
            </select>
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_available_languages_desc")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="payzen_capture_delay">{__("payzen_capture_delay")}</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][payzen_capture_delay]" id="payzen_capture_delay" style="width: 100px;" value="{$processor_params.payzen_capture_delay}">
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_capture_delay_desc")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="payzen_validation_mode">{__("payzen_validation_mode")}</label>
        <div class="controls">
            <select name="payment_data[processor_params][payzen_validation_mode]" id="payzen_validation_mode">
                <option value="" {if !isset($processor_params.payzen_validation_mode) || $processor_params.payzen_validation_mode === ""} selected="selected"{/if}>{__("payzen_valid_mode_default")}</option>
                <option value="0" {if $processor_params.payzen_validation_mode === "0"} selected="selected"{/if}>{__("payzen_valid_mode_automatic")}</option>
                <option value="1" {if $processor_params.payzen_validation_mode === "1"} selected="selected"{/if}>{__("payzen_valid_mode_manual")}</option>
            </select>
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_validation_mode_desc")}</p>
        </div>
    </div>

    {payzen_get_list what="cards" to="supported_cards"}
    <div class="control-group">
        <label class="control-label" for="payzen_payment_cards">{__("payzen_payment_cards")}</label>
        <div class="controls">
            <select name="payment_data[processor_params][payzen_payment_cards][]" id="payzen_payment_cards" multiple="multiple">
                {foreach from=$supported_cards key="code" item="card"}
                <option value="{$code}" {if $code|in_array:$processor_params.payzen_payment_cards} selected="selected" {/if}>{$card}</option>
                {/foreach}
            </select>
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_payment_cards_desc")}</p>
        </div>
    </div>
</fieldset>

<fieldset>
    <legend style="margin-bottom: 0;">
        <font style="font-size: 14px; font-weight: bold;">{__("payzen_selective_3ds")}</font>
    </legend>

    <div class="control-group">
        <label class="control-label" for="payzen_3ds_min_amount">{__("payzen_3ds_min_amount")}</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][payzen_3ds_min_amount]" id="payzen_3ds_min_amount" style="width: 100px;" value="{$processor_params.payzen_3ds_min_amount}">
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_3ds_min_amount_desc")}</p>
        </div>
    </div>
</fieldset>

<fieldset>
    <legend style="margin-bottom: 0;">
        <font style="font-size: 14px; font-weight: bold;">{__("payzen_return_options")} </font>
    </legend>

    <div class="control-group">
        <label class="control-label" for="payzen_redirect_enabled">{__("payzen_redirect_enabled")}</label>
        <div class="controls">
            <select name="payment_data[processor_params][payzen_redirect_enabled]" id="payzen_redirect_enabled">
                <option value="false" {if !isset($processor_params.payzen_redirect_enabled) || $processor_params.payzen_redirect_enabled === "false"} selected="selected" {/if}>{__("payzen_disabled")}</option>
                <option value="true" {if $processor_params.payzen_redirect_enabled === "true"} selected="selected" {/if}>{__("payzen_enabled")}</option>
            </select>
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_redirect_enabled_desc")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="payzen_redirect_success_timeout">{__("payzen_redirect_success_timeout")}</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][payzen_redirect_success_timeout]" id="payzen_redirect_success_timeout" style="width: 100px;" {if !$processor_params.payzen_redirect_success_timeout} value="5" {else} value="{$processor_params.payzen_redirect_success_timeout}" {/if} />
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_redirect_success_timeout_desc")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="payzen_redirect_success_message">{__("payzen_redirect_success_message")}</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][payzen_redirect_success_message]" id="payzen_redirect_success_message" style="width: 400px;" {if !$processor_params.payzen_redirect_success_message} value="{__("payzen_redirect_success_message_defaut")}" {else} value="{$processor_params.payzen_redirect_success_message}" {/if} />
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_redirect_success_message_desc")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="payzen_redirect_error_timeout">{__("payzen_redirect_error_timeout")}</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][payzen_redirect_error_timeout]" id="payzen_redirect_error_timeout" style="width: 100px;" {if !$processor_params.payzen_redirect_error_timeout} value="5" {else} value="{$processor_params.payzen_redirect_error_timeout}" {/if} />
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_redirect_error_timeout_desc")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="payzen_redirect_error_message">{__("payzen_redirect_error_message")}</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][payzen_redirect_error_message]" id="payzen_redirect_error_message" style="width: 400px;" {if !$processor_params.payzen_redirect_error_message} value="{__("payzen_redirect_error_message_defaut")}" {else} value="{$processor_params.payzen_redirect_error_message}" {/if} />
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_redirect_error_message_desc")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="payzen_return_mode">{__("payzen_return_mode")}</label>
        <div class="controls">
            <select name="payment_data[processor_params][payzen_return_mode]" id="payzen_return_mode">
                <option value="GET" {if !isset($processor_params.payzen_return_mode) || $processor_params.payzen_return_mode === "GET"} selected="selected" {/if}>GET</option>
                <option value="POST" {if $processor_params.payzen_return_mode === "POST"} selected="selected" {/if}>POST</option>
            </select>
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_return_mode_desc")}</p>
        </div>
    </div>

    {assign var="order_paid_statuses" value=fn_get_order_paid_statuses()}
    {assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_statuses:$order_paid_statuses}

    {* selected status *}
    {if isset($processor_params.payzen_registered_status)}
        {assign var="selected" value=$processor_params.payzen_registered_status}
    {else}
        {assign var="selected" value="P"}
    {/if}

    <div class="control-group">
        <label class="control-label" for="payzen_registered_status">{__("payzen_registered_status")}</label>
        <div class="controls">
            <select name="payment_data[processor_params][payzen_registered_status]" id="payzen_registered_status">
                {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if ($selected === $k)} selected="selected" {/if}>{$s.description}</option>
                {/foreach}
            </select>
            <p style="font-size: 12px; font-style: italic; color: #666666;">{__("payzen_registered_status_desc")}</p>
        </div>
    </div>
</fieldset>