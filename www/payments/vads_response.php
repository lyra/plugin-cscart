<?php
#####################################################################################################
#
#					Module pour la plateforme de paiement PayZen
#						Version : 1.3a (r�vision 25621)
#									########################
#					D�velopp� pour cscart
#						Version : 2.0.12
#						Compatibilit� plateforme : V1
#									########################
#					D�velopp� par Lyra Network
#						http://www.lyra-network.com/
#						01/06/2011
#						Contact : support@payzen.eu
#
#####################################################################################################

define('AREA', 'C');
define('AREA_NAME' ,'customer');

// prendre en compte la possibilité d'envoi par GET
$data = (isset($_POST['order_id'])) ? $_POST : $_GET; 

// le parametre version est utilisé par les fonction cs-cart
unset($_POST['version']);
unset($_GET['version']);

require './../prepare.php';
require './../init.php';

include_once 'vads_api.php';

// récupérer les parametres de ce mode de paiement
$payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $data['order_id']);
$processor_data = fn_get_processor_data($payment_id);

// utiliser l'api vads pour traiter la reponse
$vad_object = new VADS_API();
$vad_object->setResponseFromPost(
	$data,
	$processor_data['params']['test_key'],
	$processor_data['params']['prod_key'],
	$processor_data['params']['ctx_mode']
);

$msg_text = $vad_object->getResponseMessage('detail') . 
			($vad_object->getReponse3DSec() != '' ? '<br/>' . $vad_object->getReponse3DSec() : '');

$from_server = isset($data['hash']);

$index_script = "index.php";

if(!$vad_object->isAuthentifiedResponse()){
	if($from_server){
		die($vad_object->getCheckUrlResponse('auth_fail'));
	}
	else {
		fn_set_notification('E', fn_get_lang_var('error'), $msg_text, true);
		fn_redirect($http_location . "/$index_script?dispatch=checkout.cart", false);
		die();	
	}
}

// récupérer la commande de la bdd
$order_info = fn_get_order_info($data['order_id']); 

if(! $order_info){
	// Commande non trouvée, c'est une erreur
	if($from_server){
		die($vad_object->getCheckUrlResponse('order_not_found'));
	}
	else{
		fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('vads_order_not_found'), true);
		fn_redirect($http_location . "/$index_script?dispatch=checkout.cart", false);
		die();
	}
}

$statuses = array('00' => 'P', '05' => 'D', '17' => 'I');
$status = $statuses[$vad_object->get('result')] ? $statuses[$vad_object->get('result')] : 'F';

if($order_info['status'] != 'P' && $order_info['status'] != $status) {
	//commande non enregistrée
	$pp_response = array();
	$pp_response['transaction_id'] = $data['trans_id'];
	$pp_response['reason_text'] = $msg_text;
	$pp_response['order_status'] = $status;
			
	if (fn_check_payment_script('vads.php', $data['order_id'])) {
		fn_finish_payment($data['order_id'], $pp_response, false);
	}
	
	if($vad_object->isAcceptedPayment()) {
		if($from_server) {
			die($vad_object->getCheckUrlResponse('payment_ok'));
		}
		else {
			// Avertissement en mode TEST : url serveur n'a pas fonctionné
			if($processor_data['params']['ctx_mode'] == 'TEST') {
				fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('vads_check_url_failed'), true);
			}
			fn_redirect(Registry::get('config.current_location') . "/$index_script?dispatch=payment_notification.notify&payment=vads&order_id=" . $data['order_id']);
		}
	}
	else {
		if($from_server){
			die($vad_object->getCheckUrlResponse('payment_ko'));
		}
		else {
			if ($vad_object->isCancelledPayment()){
				fn_redirect(Registry::get('config.current_location') . "/$index_script?dispatch=orders.details&order_id=" . $data['order_id'] . "&confirmation=Y", true);
			}
			else {
				// Avertissement en mode TEST : url serveur n'a pas fonctionné
				if($processor_data['params']['ctx_mode'] == 'TEST') {
					fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('vads_check_url_failed'), true);
				}
				fn_redirect(Registry::get('config.current_location') . "/$index_script?dispatch=payment_notification.notify&payment=vads&order_id=" . $data['order_id']);
			}
		}
	}
	
}
else {
	//commande déjà enregistrée
	if($vad_object->isAcceptedPayment()) {
		if($from_server){
			die ($vad_object->getCheckUrlResponse('payment_ok_already_done'));
		}
		else {	
			fn_redirect(Registry::get('config.current_location') . "/$index_script?dispatch=payment_notification.notify&payment=vads&order_id=" . $data['order_id']);
		}
	}
	else {
		if($from_server){
			if($order_info['status'] == 'P'){
				die ($vad_object->getCheckUrlResponse('payment_ko_on_order_ok'));
			} else {
				die($vad_object->getCheckUrlResponse('ok', 'Echec de paiement ou annulation déja enregistré.'));
			}
		}
		else {
			if ($vad_object->isCancelledPayment()){
				fn_redirect(Registry::get('config.current_location') . "/$index_script?dispatch=orders.details&order_id=" . $data['order_id'] . "&confirmation=Y", true);
			}
			else {
				fn_redirect(Registry::get('config.current_location') . "/$index_script?dispatch=payment_notification.notify&payment=vads&order_id=" . $data['order_id']);
			}
		}
	}	
}

?>