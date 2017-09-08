<?php
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

if ( !defined('AREA') ) { die('Access denied'); }

include_once 'vads_api.php';

if (defined('PAYMENT_NOTIFICATION')) {// return from payment server website
	if ($mode == 'notify') {
		fn_order_placement_routines($_REQUEST['order_id']);
	}
	exit;
	
} else {
	// Use our custom class to generate the html
	$vad_object = new VADS_API();
	
	$vad_object->set('platform_url', $processor_data['params']['cgi_url']);
	$vad_object->set('version', 'V1');
	$vad_object->set('key_test', $processor_data['params']['test_key']);
	$vad_object->set('key_prod', $processor_data['params']['prod_key']);
	$vad_object->set('amount', number_format($order_info['total'] * 100, 0, '', ''));
	$vad_object->set('capture_delay', $processor_data['params']['capture_delay']);
	$vad_object->set('currency', $processor_data['params']['currency']);
	$vad_object->set('ctx_mode', $processor_data['params']['ctx_mode']);
	$vad_object->set('payment_cards', $processor_data['params']['payment_cards']);
	$vad_object->set('payment_config', $processor_data['params']['payment_config']);
	$vad_object->set('site_id', $processor_data['params']['site_id']);
	$vad_object->set('validation_mode', $processor_data['params']['validation_mode']);
	$vad_object->set('cust_id', $order_info['user_id']);
	$vad_object->set('cust_email', $order_info['email']);
	$vad_object->set('cust_name', $order_info['b_firstname'] . ' ' . $order_info['b_lastname']);
	$vad_object->set('cust_address', $order_info['b_address'] . ' ' . $order_info['b_state']);
	$vad_object->set('cust_zip', $order_info['b_zipcode']);
	$vad_object->set('cust_city',  $order_info['b_city']);
	$vad_object->set('cust_phone',  $order_info['phone']);
	$vad_object->set('cust_country', $order_info['b_country']);
	$vad_object->set('language', $processor_data['params']['language']);
	$vad_object->set('order_id', $order_id);
	$vad_object->set('url_return', $processor_data['params']['return_url']);
	$vad_object->set('url_success', $processor_data['params']['return_url']);
	$vad_object->set('url_referral', $processor_data['params']['return_url']);
	$vad_object->set('url_refused', $processor_data['params']['return_url']);
	$vad_object->set('url_cancel', $processor_data['params']['return_url']);
	$vad_object->set('url_error', $processor_data['params']['return_url']);
	$vad_object->set('contrib', 'cscart2.0.12_1.3a'); 
	$vad_object->set('redirect_enabled', $processor_data['params']['redirect_enable']);
	if($vad_object->isRedirectEnabled()){
		$vad_object->set('return_mode', 'GET');
	}
	$vad_object->set('redirect_success_timeout', $processor_data['params']['redirect_success_timeout']);
	$vad_object->set('redirect_success_message', utf8_encode($processor_data['params']['redirect_success_msg']));
	$vad_object->set('redirect_error_timeout', $processor_data['params']['redirect_error_timeout']);
	$vad_object->set('redirect_error_message', utf8_encode($processor_data['params']['redirect_error_msg']));
	
	$process_button_string = '<form action="' . $processor_data['params']['cgi_url'] . '" method="post" name="vads_form">'
			. $vad_object->getRequestHtmlInputs() . '</form>';
	
	//Message to be shown when forwarding to payment platform
	$msg = fn_get_lang_var('text_cc_processor_connection');
	$msg = str_replace('[processor]', 'PayZen server', $msg); 
echo <<<EOT
	<html>
	<body onLoad="document.vads_form.submit();">
	$process_button_string			
	<p><div align=center>{$msg}</div></p>
 	</body>
	</html>
EOT;
	exit;
}

?>
