{*
#####################################################################################################
#
#					Module pour la plateforme de paiement PayZen
#						Version : 1.3a (révision 25621)
#									########################
#					Développé pour cscart
#						Version : 2.0.12
#						Compatibilité plateforme : V1
#									########################
#					Développé par Lyra Network
#						http://www.lyra-network.com/
#						01/06/2011
#						Contact : support@payzen.eu
#
#####################################################################################################
*}
{assign var="default_return_url" value="`$config.http_location`/payments/vads_response.php"}
{assign var="check_url" value="`$config.http_location`/payments/vads_response.php"}

<p>{$lang.text_vads_notice} <b>{$check_url}<b/></p>
<hr/>

<div class="form-field">
	<label for="cgi_url">{$lang.vads_cgi_url}:</label>
	<input type="text" name="payment_data[processor_params][cgi_url]" id="cgi_url" size="60" {if $processor_params.cgi_url eq ""} value="https://secure.payzen.eu/vads-payment/" {else} value="{$processor_params.cgi_url}" {/if}  class="input-text" />
</div>

<div class="form-field">
	<label for="site_id">{$lang.vads_site_id}:</label>
	<input type="text" name="payment_data[processor_params][site_id]" id="site_id" size="60" {if $processor_params.site_id eq ""} value="123456" {else} value="{$processor_params.site_id}" {/if} class="input-text" />
</div>

<div class="form-field">
	<label for="ctx_mode">{$lang.vads_ctx_mode}:</label>
	<select name="payment_data[processor_params][ctx_mode]" id="ctx_mode">
		<option value="TEST"{if $processor_params.ctx_mode eq "TEST"} selected="selected"{/if}>{$lang.vads_ctx_mode_test}</option>
		<option value="PRODUCTION" {if $processor_params.ctx_mode eq "PRODUCTION"} selected="selected"{/if}> {$lang.vads_ctx_mode_prod}</option>
	</select>
</div>

<div class="form-field">
	<label for="test_key">{$lang.vads_test_key}:</label>
	<input type="text" name="payment_data[processor_params][test_key]" id="test_key" size="60" value="{$processor_params.test_key}" class="input-text" />
</div>

<div class="form-field">
	<label for="prod_key">{$lang.vads_prod_key}:</label>
	<input type="text" name="payment_data[processor_params][prod_key]" id="prod_key" size="60" value="{$processor_params.prod_key}" class="input-text" />
</div>

<div class="form-field">
	<label for="capture_delay">{$lang.vads_capture_delay}:</label>
	<input type="text" name="payment_data[processor_params][capture_delay]" id="capture_delay" size="60" value="{$processor_params.capture_delay}" class="input-text" />
</div>

<div class="form-field">
	<label for="currency">{$lang.vads_currency}:</label>
	<select name="payment_data[processor_params][currency]" id="currency">
		<option value="978"{if $processor_params.currency eq "978"} selected="selected"{/if}>{$lang.currency_code_eur}</option>
		<option value="036"{if $processor_params.currency eq "036"} selected="selected"{/if}>{$lang.currency_code_aud}</option>
		<option value="124"{if $processor_params.currency eq "124"} selected="selected"{/if}>{$lang.currency_code_cad}</option>
		<option value="756"{if $processor_params.currency eq "756"} selected="selected"{/if}>{$lang.currency_code_chf}</option>
		<option value="203"{if $processor_params.currency eq "203"} selected="selected"{/if}>{$lang.currency_code_czk}</option>
		<option value="208"{if $processor_params.currency eq "208"} selected="selected"{/if}>{$lang.currency_code_dkk}</option>
		<option value="826"{if $processor_params.currency eq "826"} selected="selected"{/if}>{$lang.currency_code_gbp}</option>
		<option value="344"{if $processor_params.currency eq "344"} selected="selected"{/if}>{$lang.currency_code_hkd}</option>
		<option value="348"{if $processor_params.currency eq "348"} selected="selected"{/if}>{$lang.currency_code_huf}</option>
		<option value="376"{if $processor_params.currency eq "376"} selected="selected"{/if}>{$lang.currency_code_ils}</option>
		<option value="392"{if $processor_params.currency eq "392"} selected="selected"{/if}>{$lang.currency_code_jpy}</option>
		<option value="440"{if $processor_params.currency eq "440"} selected="selected"{/if}>{$lang.currency_code_ltl}</option>
		<option value="428"{if $processor_params.currency eq "428"} selected="selected"{/if}>{$lang.currency_code_lvl}</option>
		<option value="484"{if $processor_params.currency eq "484"} selected="selected"{/if}>{$lang.currency_code_mxn}</option>
		<option value="578"{if $processor_params.currency eq "578"} selected="selected"{/if}>{$lang.currency_code_nok}</option>
		<option value="985"{if $processor_params.currency eq "985"} selected="selected"{/if}>{$lang.currency_code_pln}</option>
		<option value="643"{if $processor_params.currency eq "643"} selected="selected"{/if}>{$lang.currency_code_rur}</option>
		<option value="752"{if $processor_params.currency eq "752"} selected="selected"{/if}>{$lang.currency_code_sek}</option>
		<option value="764"{if $processor_params.currency eq "764"} selected="selected"{/if}>{$lang.currency_code_thb}</option>
		<option value="949"{if $processor_params.currency eq "949"} selected="selected"{/if}>{$lang.currency_code_try}</option>
		<option value="840"{if $processor_params.currency eq "840"} selected="selected"{/if}>{$lang.currency_code_usd}</option>
		<option value="710"{if $processor_params.currency eq "710"} selected="selected"{/if}>{$lang.currency_code_zar}</option>
	</select>
</div>

<div class="form-field">
	<label for="language">{$lang.vads_language}:</label>
	<select name="payment_data[processor_params][language]" id="language">
		<option value="fr"{if $processor_params.language eq "fr"} selected="selected"{/if}>{$lang.vads_lang_fr}</option>
		<option value="de"{if $processor_params.language eq "de"} selected="selected"{/if}>{$lang.vads_lang_de}</option>
		<option value="en"{if $processor_params.language eq "en"} selected="selected"{/if}>{$lang.vads_lang_en}</option>
		<option value="zh"{if $processor_params.language eq "zh"} selected="selected"{/if}>{$lang.vads_lang_zh}</option>
		<option value="es"{if $processor_params.language eq "es"} selected="selected"{/if}>{$lang.vads_lang_es}</option>
		<option value="it"{if $processor_params.language eq "it"} selected="selected"{/if}>{$lang.vads_lang_it}</option>
		<option value="ja"{if $processor_params.language eq "ja"} selected="selected"{/if}>{$lang.vads_lang_ja}</option>
	</select>
</div>

<div class="form-field">
	<label for="payment_cards">{$lang.vads_payment_cards}:</label>
	<input type="text" name="payment_data[processor_params][payment_cards]" id="payment_cards" size="60" value="{$processor_params.payment_cards}" class="input-text" />
</div>

<div class="form-field">
	<label for="payment_config">{$lang.vads_payment_config}:</label>
	<input type="text" name="payment_data[processor_params][payment_config]" id="payment_config" size="60" {if $processor_params.payment_config eq ""} value="SINGLE" {else} value="{$processor_params.payment_config}" {/if} class="input-text" />
</div>

<div class="form-field">
	<label for="validation_mode">{$lang.vads_validation_mode}:</label>
	<select name="payment_data[processor_params][validation_mode]" id="validation_mode">
		<option value="" {if $processor_params.validation_mode eq ""} selected="selected"{/if}>{$lang.vads_valid_mode_default}</option>
		<option value="0" {if $processor_params.validation_mode eq "0"} selected="selected"{/if}>{$lang.vads_valid_mode_auto}</option>
		<option value="1" {if $processor_params.validation_mode eq "1"} selected="selected"{/if}>{$lang.vads_valid_mode_manual}</option>		
	</select>
</div>

<div class="form-field">
	<label for="redirect_enable">{$lang.vads_redirect_enable}:</label>
	<select name="payment_data[processor_params][redirect_enable]" id="redirect_enable">
		<option value="false" {if $processor_params.redirect_enable eq "false"} selected="selected"{/if}>{$lang.disabled}</option>
		<option value="true" {if $processor_params.redirect_enable eq "true"} selected="selected"{/if}>{$lang.enabled}</option>		
	</select>
</div>

<div class="form-field">
	<label for="redirect_success_msg">{$lang.vads_redirect_success_msg}:</label>
	<input type="text" name="payment_data[processor_params][redirect_success_msg]" id="redirect_success_msg" size="60" {if $processor_params.success_url eq ""} value="{$lang.vads_redirect_success_default_msg}" {else} value="{$processor_params.redirect_success_msg}" {/if} class="input-text" />
</div>

<div class="form-field">
	<label for="redirect_success_timeout">{$lang.vads_redirect_success_timeout}:</label>
	<input type="text" name="payment_data[processor_params][redirect_success_timeout]" id="redirect_success_timeout" size="60" {if $processor_params.redirect_success_timeout eq ""} value="5" {else} value="{$processor_params.redirect_success_timeout}" {/if} class="input-text" />
</div>

<div class="form-field">
	<label for="redirect_error_msg">{$lang.vads_redirect_error_msg}:</label>
	<input type="text" name="payment_data[processor_params][redirect_error_msg]" id="redirect_error_msg" size="60" {if $processor_params.success_url eq ""} value="{$lang.vads_redirect_error_default_msg}" {else} value="{$processor_params.redirect_error_msg}" {/if} class="input-text" />
</div>

<div class="form-field">
	<label for="redirect_error_timeout">{$lang.vads_redirect_error_timeout}:</label>
	<input type="text" name="payment_data[processor_params][redirect_error_timeout]" id="redirect_error_timeout" size="60" {if $processor_params.redirect_error_timeout eq ""} value="5" {else} value="{$processor_params.redirect_error_timeout}" {/if} class="input-text" />
</div>

<div class="form-field">
	<label for="return_url">{$lang.vads_return_url}:</label>
	<input type="text" name="payment_data[processor_params][return_url]" id="return_url" size="60" {if $processor_params.return_url eq ""} value="{$default_return_url}" {else} value="{$processor_params.return_url}" {/if} class="input-text" />
</div>
