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

/**
 * Classe implémentant la génération de formulaire et la vérification de signature
 *
 */
class VADS_API
{
	//TODO il manque certains paramètres facultatifs (user_info, order_info2/3, theme_config...)
	
	/* ********* *
	 * ATTRIBUTS *
	 * ********* */
	/* PARAMETRES D'ENVOI OBLIGATOIRES */
	var $version='V1';		// Version de la plateforme de paiement
	var $currency='978';	// Monnaie à utiliser selon norme ISO 4217 (http://www.iso.org/iso/support/currency_codes_list-1.htm)
	var $payment_cards='';	// Liste des types de cartes pouvant être utilisées pour le paiement
							// vide = tout type accepté, sinon une combinaison des codes suivants séparés par ";" : AMEX;CB;MASTERCARD;VISA

	var $amount; 			// Montant de la trasaction (en cents)
	var $capture_delay; 	// Délais en jour avant remis en banque (si vide, paramètre par défaut défini dans le back office)
	var $ctx_mode;			// Mode de solicitation de la plateforme (TEST ou PRODUCTION)
	
	var $payment_config; 	// Type de paiement (SINGLE ou MULTI)
							/*Exemple pour un paiement de 10000 cents (100 euros) :
							 payment_config=SINGLE (en une fois)
							 ou
							 payment_config=MULTI:first=5000;count=3;period=30 (en plusieurs fois)
							 (Premier paiement de 5000 cents aujourd'hui + "capture_delay"
							 Deuxième paiement de 2500 cents à aujourd'hui + "capture_delay"+ 30 jours
							 Troisième paiement de 2500 cents à aujourd'hui + "capture_delay" + 60 jours)*/
	var $signature;			// Utilisée pour authentifier les échanges avec la plateforme (cf. calculSignature() )
	var $site_id;			// Disponible dans le back office de la plateforme de paiement
	var $trans_date;		// Date locale du site (format : AAAAMMJJHHMMSS)
	var $trans_id;			// Constitué de 6 caractères numériques, unique pour le site et pour la journée entière (cf. function calculTransId() )
	var $validation_mode;	// Si validation manuelle du commerçant. Par défaut paramètre défini dans le backoffice

	var $platform_url;	// Url de la plateforme de paiement

	var $key_test;			// Clé fournie par la plateforme servant à calculer la signature
	var $key_prod;			// idem en mode production

	/* PARAMETRES D'ENVOI FACULTATIFS */
	var $cust_id;			// Identifiant client pour le marchand
	var $cust_name;			// Nom du client
	var $cust_title;		// Civilité du client
	var $cust_email;		// Adresse e-mail du client (envoi d'un mail récapitulatif de la transaction)
	var $cust_address;		// Adresse du client
	var $cust_zip;			// Code postal du client
	var $cust_city;			// Ville du client
	var $cust_phone;		// Numéro de téléphone du client
	var $cust_country;		// Pays du client (Norme ISO 3166 http://www.iso.org/iso/fr/country_codes/iso_3166_code_lists/english_country_names_and_code_elements.htm)
	var $language;			// Langue de la page de paiement Norme ISO 639-1 (par défaut le français est sélectionné)
							//valeurs possibles : fr (défaut), de, en, zh, es, fr, it, ja
	var $order_id;			// Numéro de commande (rappelé dans l'e-mail de confirmation du client), 32 caractères alpha-numériques maximum
	var $order_info;		// Résumé de la commande
	var $url_return;		// Url par défaut (après appui du bouton "retourner à la boutique" par le client sur la plateforme)
	var $url_success;		// Url en cas de succès du paiement (après appui du bouton "retourner à la boutique")
	var $url_referral;		// Url en cas de refus d'autorisation, code 02 "referral" (après appui du bouton "retourner à la boutique")
	var $url_refused;		// Url en cas de refus autre que "referral" (après appui du bouton "retourner à la boutique")
	var $url_cancel;		// Url en cas d'annulation par le client (après appui sur "annuler et retourner sur la boutique")
	var $url_error;			// Url en cas d'erreur interne
	var $url_check;			// Url appelée par la plateforme de paiement pour confirmer le paiement
							// Pour une sécurité optimale, ne pas utiliser ce paramètre. Le configurer dans le backoffice de la plateforme
	var $contrib='cscart2.0.12_1.3a';	// code identifiant le plugin de paiement (par ex. "thelia_v2.0")
	var $redirect_enabled;	// Activer ou non la redirection automatique du client vers la boutique
	var $redirect_success_timeout;	// si $redirect_enabled, définit le temps en secondes (0-300) avant la redirection automatique
	var $redirect_success_message;	// si $redirect_enabled, définit le message affiché avant la redirection automatique
	var $redirect_error_timeout;	// si $redirect_enabled, définit le temps avant redirection automatique, lorsque le paiement a échoué
	var $redirect_error_message;	// si $redirect_enabled, définit le message affiché avant redirection automatique, lorsque le paiement a échoué
	var $return_mode;		// POST (défaut), GET ou NONE : façon dont les paramètres seront transmis lorsque le client clique sur "retour à la boutique"


	/* PARAMETRES DE REPONSE DE LA PLATEFORME */
	var $auth_result;		// Code retour de la demande d'autorisation retournée par la banque émettrice, si disponible (vide sinon).
	var $auth_mode;			// Indique comment a été réalisée la demande d?autorisation. Ce champ peut prendre les valeurs suivantes :
							#- FULL : correspond à une autorisation du montant total de la transaction dans le cas d?un paiement unitaire avec remise à moins de 6 jours,
							# 		ou à une autorisation du montant du premier paiement dans le cas du paiement en N fois, dans le cas d?une remise de ce premier paiement à moins de 6 jours.
							#- MARK : correspond à une prise d?empreinte de la carte, dans le cas ou le paiement est envoyé en banque à plus de 6 jours.
	var $auth_number;		// Numéro d'autorisation retourné par le serveur bancaire, si disponible (vide sinon).
	var $card_brand;		// Type de carte utilisé pour le paiement, si disponible (vide sinon).
	var $card_number;		// Numéro de carte masqué.
	var $extra_result;		// Code complémentaire de réponse. Sa signification dépend de la valeur renseignée dans result.
							# Lorsque result vaut 30 (erreur de requête), alors extra_result contient un code indiquant quel champ a été mal rempli
	var $warranty_result;	// Si l?autorisation a été réalisée avec succès, indique la garantie du paiement, liée à 3D-Secure :
							# YES => Le paiement est garanti
							# NO => Le paiement n?est pas garanti
							# UNKNOWN => Suite à une erreur technique, le paiement ne peut pas être garanti
							# Non valorisé => Garantie de paiement non applicable
	var $payment_certificate;// Si l?autorisation a été réalisée avec succès, la plateforme de paiement délivre un certificat de paiement. Pour toute question concernant un paiement réalisé sur la plateforme, cette information devra être communiquée.
	var $result;			// Code retour.
							#- 00 : Paiement réalisé avec succès.
							#- 02 : Le commerçant doit contacter la banque du porteur.
							#- 05 : Paiement refusé.
							#- 17 : Annulation client.
							#- 30 : Erreur de format de la requête. A mettre en rapport avec la valorisation du champ extra_result.
							#- 96 : Erreur technique lors du paiement.
	var $hash;				// Valeur retour de serveur à serveur permettant de calculer la signature de retour
	
	var $received_signature;// Signature reçue en POST au retour de la plateforme

	
	/* VARIABLES LIEES L'OBJET */
	var $timestamp='';		//Timestamp lors de la création du formulaire

	var $tab_auth_result;	// Tableau de réponses de confirmation (code => traduction)
	var $tab_warranty_result;// Tableau de réponses 3D Secure
	var $tab_result;		// Tableau de réponses globales
	var $tab_extra_result;	// Tableau de réponses globales détaillées

	
	/* LISTES DES ATTRIBUTS DE L'OBJET utiles pour les foreach... */
	// liées à la requête (sauf key et signature)
	var $request_mandatory = array(
		'amount','capture_delay','currency','ctx_mode','payment_cards','payment_config','site_id','trans_date','trans_id',
		'validation_mode','version'
	);
	var $request_optionnal = array(
		'cust_id','cust_name','cust_title','cust_address','cust_zip','cust_city','cust_phone','cust_country','language','order_id','order_info',
		'cust_email','url_return','url_success','url_referral','url_refused','url_cancel','url_error','url_check','contrib',
		'redirect_success_timeout','redirect_success_message','redirect_error_timeout','redirect_error_message','return_mode'
	);
	var $request_signature = array(
		'version','site_id','ctx_mode','trans_id','trans_date','validation_mode','capture_delay','payment_config','payment_cards','amount',
		'currency'
	);
	
	// liées à la réponse (sauf key, signature et hash)
	var $response_specific = array(
		'auth_result','auth_mode','auth_number','card_brand','card_number','extra_result','warranty_result','payment_certificate','result'
	);
	var $response_requestlike = array(
		// same as response
		'amount','contrib','ctx_mode','currency','payment_config','site_id','trans_date','trans_id','version','order_id','order_info',
		'cust_address','cust_country','cust_email','cust_id','cust_name','cust_phone','cust_title','cust_city','cust_zip','payment_src',
		// same as response or default
		'capture_delay','validation_mode','language'
	);
	var $response_signature = array(
		'version','site_id','ctx_mode','trans_id','trans_date','validation_mode','capture_delay','payment_config','card_brand','card_number',
		'amount','currency','auth_mode','auth_result','auth_number','warranty_result','payment_certificate','result'
	);
	
	// Autres attributs
	var $misc = array('key_test','key_prod','hash','redirect_enabled','platform_url','signature','received_signature');
	
	
	/* ********************************* *
	 * GETTERS/SETTERS SUR LES VARIABLES *
	 * ********************************* */
	/**
	 * renvoie un tableau contenant les noms des attributs liés à telle ou telle fonction de l'objet
	 */
	function getAttributeList($type=null)
	{
		switch($type)
		{
			case "request_mandatory":
				return $this->request_mandatory;
			case "request_optionnal":
				return $this->request_optionnal;
			case "request_all":
				return array_merge($this->request_mandatory,$this->request_optionnal);
				
			case "response_specific":
				return $this->response_specific;
			case "response_requestlike":
				return $this->response_requestlike;
			case "response_all":
				return array_merge($this->response_specific, $this->response_requestlike);
				
			case "request_signature":
				return $this->request_signature;
			case "response_signature":
				return $this->response_signature;
				
			case "misc":
				return $this->misc;
			case "all":
			default:
				return array_unique(array_merge(
					$this->request_mandatory, $this->request_optionnal, $this->request_signature,
					$this->response_specific, $this->response_requestlike, $this->response_signature,
					$this->misc
				));
		}
	}
	
	/**
	 * renvoie la valeur d'un attribut public
	 */
	function get($name='')
	{
		$result = null;
		if( in_array($name, $this->getAttributeList("all"),true) )
		{
			$result = $this->$name;
		}
		if($name==="key")
		{
			$result = ($this->ctx_mode=="TEST") ? $this->key_test : $this->key_prod;
		}
		return $result;
	}
	
	/**
	 * Renvoie la liste des codes iso des langues supportées par la plateforme de paiement
	 * @return multitype:string
	 */
	function getSupportedLanguages() {
		return array('fr','de','en','es','zh','it','ja','pt');
	}
	
	/**
	 * Récupération du code numérique ISO 4217 d'une devise à partir de son code à 3 lettres.
	 * @param string $alpha3 code alphabétique de la devise (ex:EUR,USD,JPY...)
	 */
	function convertAlphaCurrency($alpha3) {
		$currencies = array (
			"AUD" => "036", //Dollar australien
			"CAD" => "124", //Dollar canadien
			"CNY" => "156", //Renminbi yuan chinois
			"DKK" => "208", //Couronne danoise
			"JPY" => "392", //Yen
			"SEK" => "752", //Couronne suédoise
			"CHF" => "756", //Franc suisse
			"GBP" => "826", //Livre sterling
			"USD" => "840", //Dollar des États-Unis
            "EUR" => "978"  //Euro            
		);
		if(array_key_exists($alpha3, $currencies)) {
			return $currencies[$alpha3];
		}
		else {
			return false;
		}
	}
	
	/**
	 * Modifie la valeur d'un attribut public
	 * @return boolean true si réussite, false sinon
	 */
	function set($name='',$value=null)
	{
		if( in_array($name, $this->getAttributeList("all")) )
		{
			$this->$name = $value;
			return true;
		}
		return false;
	}
	
	/**
	 * Modifie les valeurs d'attributs publics à partir d'un tableau
	 * @param $params tableau de paramètres format nom=>valeur
	 * @return boolean true si toutes les valeurs du tableau ont pu être enregistrées, false si non
	 */
	function setFromArray($params)
	{
		$result = true;
		foreach ($params as $name => $value)
		{
			$temp_result = $this->set($name,$value);
			if( $temp_result == false )
				$result = false;
		}
		return $result;
	}
	
	/* ************ *
	 * CONSTRUCTEUR *
	 * ************ */
	function VADS_API()
	{
		// Intialisation des variables privées
		$this->timestamp=time();
		$this->loadResponsesTranslation();
		$auth_result='';
		$auth_mode='';
		$auth_number='';
		$card_brand='';
		$card_number='';
		$extra_result='';
		$warranty_result='';
		$payment_certificate='';
		$result='';
		$hash='';

		//Calcul
		$this->generateTrans_id();
		$this->getTrans_date();
	}

	/**
	 * Chargement des traductions des codes retour dans tab_result, tab_auth_result et tab_warranty_result
	 */
	function loadResponsesTranslation($lang='fr'){
		$translations = array();
		//NB : translations have to be complete !
		$translations['fr'] = array(
//			'MISSING_RESULT_TRANSLATION' => "Traduction manquante pour le code de retour ",
//			'MISSING_EXTRA_RESULT_TRANSLATION' => "Traduction manquante pour le code de retour complémentaire ",
			
			# warranty_result
			'PAIEMENT_GARANTI' => 'Le paiement est garanti',
			'PAIEMENT_PAS_GARANTI' => 'Le paiement n\'est pas garanti',
			'INCIDENT_TECHNIQUE_PAIEMENT_PAS_GARANTI' => 'Suite à une erreur technique, le paiement ne peut pas être garanti',
			
			# result
			'PAIEMENT_REALISE_SUCCES' => 'Paiement réalisé avec succès',
			'COMMERCANT_CONTACTER_BANQUE_PORTEUR' => 'Le commerçant doit contacter la banque du porteur',
			'PAIEMENT_REFUSE' => 'Paiement refusé',
			'ANNULATION_CLIENT' => 'Annulation client',
			'ERREUR_FORMAT_REQUETE' => 'Erreur de format de la requête',
			'ERREUR_TECHNIQUE_LORS_PAIEMENT' => 'Erreur technique lors du paiement',
			
			# extra_result
			'VERSION_MODE_PAIEMENT_BPL' => "Version du module de paiement (normalement : 1.3a)",
			'ATTRIBUER_LORS_INSCIPTION_COMMERCANT' => "Attribué lors de l'inscription du commerçant",
			'UNIQUE_POUR_SITE_POUR_1_JOURNEE' => "Unique pour le site et pour la journée",
			'DATE_LOCALE_SITE' => "Date locale du site",
			'SI_VALIDATION_MANUELLE_COMMERCANT' => "Si validation manuelle du commerçant",
			'DELAI_NB_JOUR_REMISE_BANQUE' => "Délais en jour avant remise en banque",
			'TYPE_PAIEMENT' => "Type de paiement (en une ou plusieurs fois)",
			'LISTE_CARTES_DISPO' => "Liste des types de cartes disponibles",
			'MONTANT_TRANSACTION' => "Montant de la trasaction (en cents)",
			'MONNAIE_UTILISER_ISO' => "Monnaie à utiliser selon norme ISO 4217",
			'MODE_PLATEFORME' => "Mode de solicitation de la plateforme",
			'LANGUE_PAGE_PAIEMENT'=> "Langue de la page de paiement Norme ISO 639-1",
			'NUMERO_COMMANDE' => "Numéro de commande",
			'RESUME_COMMANDE' => "Résumé de la commande",
			'ADRESSE_EMAIL_CLIENT' => "Adresse e-mail du client",
			'IDENTIFIANT_CLIENT_POUR_MARCHANT' => "Identifiant client pour le marchand",
			'CIVILITE_CLIENT' => "Civilité du client",
			'NOM_CLIENT' => "Nom du client",
			'ADRESSE_CLIENT' => "Adresse du client",
			'CODE_POSTAL_CLIENT' => "Code postal du client",
			'VILLE_CLIENT' => "Ville du client",
			'PAYS_CLIENT_ISO' => "Pays du client (Norme ISO 3166 )",
			'TELEPHONE_CLIENT' => "Téléphone du client",
			'ERREUR_INCONNUE_DANS_REQUETE' => "Erreur inconnue dans la requête",
			'URL_SUCCESS' => "Url de retour lorsque le paiement est réussi",
			'URL_REFUS' => "Url de retour lorsque le paiement est refusé",
			'URL_REFUS_AUTORISATION' => "Url de retour lorsque le paiement n'a pas été autorisé",
			'URL_ANNULATION' => "Url de retour lorsque le client annule le paiement",
			'URL_DEFAUT' => "Url de retour par défaut",
			'URL_ERREUR' => "Url de retour en cas d'erreur",
		);
		
		$translation = array_key_exists($lang,$translations) ? $translations[$lang] : $translations['fr'];
		
		
//		$missing_msg = $translation['MISSING_RESULT_TRANSLATION'];

		# warranty_result
		$this->tab_warranty_result['YES'] = $translation['PAIEMENT_GARANTI'];
		$this->tab_warranty_result['NO'] = $translation['PAIEMENT_PAS_GARANTI'];
		$this->tab_warranty_result['UNKNOWN'] = $translation['INCIDENT_TECHNIQUE_PAIEMENT_PAS_GARANTI'];

		# result
		$this->tab_result['00'] = $translation['PAIEMENT_REALISE_SUCCES'];
		$this->tab_result['02'] = $translation['COMMERCANT_CONTACTER_BANQUE_PORTEUR'];
		$this->tab_result['05'] = $translation['PAIEMENT_REFUSE'];
		$this->tab_result['17'] = $translation['ANNULATION_CLIENT'];
		$this->tab_result['30'] = $translation['ERREUR_FORMAT_REQUETE'];
		$this->tab_result['96'] = $translation['ERREUR_TECHNIQUE_LORS_PAIEMENT'];

//		$missing_msg = $translation['MISSING_EXTRA_RESULT_TRANSLATION'];
		# extra_result
		$this->tab_extra_result['01'] = 'version => '.$translation['VERSION_MODE_PAIEMENT_BPL'];
		$this->tab_extra_result['02'] = 'site_id => '.$translation['ATTRIBUER_LORS_INSCIPTION_COMMERCANT'];
		$this->tab_extra_result['03'] = 'trans_id => '.$translation['UNIQUE_POUR_SITE_POUR_1_JOURNEE'];
		$this->tab_extra_result['04'] = 'trans_date => '.$translation['DATE_LOCALE_SITE'];
		$this->tab_extra_result['05'] = 'validation_mode => '.$translation['SI_VALIDATION_MANUELLE_COMMERCANT'];
		$this->tab_extra_result['06'] = 'capture_delay => '.$translation['DELAI_NB_JOUR_REMISE_BANQUE'];
		$this->tab_extra_result['07'] = 'payment_config => '.$translation['TYPE_PAIEMENT'];
		$this->tab_extra_result['08'] = 'payment_cards => '.$translation['LISTE_CARTES_DISPO'];
		$this->tab_extra_result['09'] = 'amount => '.$translation['MONTANT_TRANSACTION'];
		$this->tab_extra_result['10'] = 'currency => '.$translation['MONNAIE_UTILISER_ISO'];
		$this->tab_extra_result['11'] = 'ctx_mode => '.$translation['MODE_PLATEFORME'];
		$this->tab_extra_result['12'] = 'language => '.$translation['LANGUE_PAGE_PAIEMENT'];
		$this->tab_extra_result['13'] = 'order_id => '.$translation['NUMERO_COMMANDE'];
		$this->tab_extra_result['14'] = 'order_info => '.$translation['RESUME_COMMANDE'];
		$this->tab_extra_result['15'] = 'cust_email => '.$translation['ADRESSE_EMAIL_CLIENT'];
		$this->tab_extra_result['16'] = 'cust_id => '.$translation['IDENTIFIANT_CLIENT_POUR_MARCHANT'];
		$this->tab_extra_result['17'] = 'cust_title => '.$translation['CIVILITE_CLIENT'];
		$this->tab_extra_result['18'] = 'cust_name => '.$translation['NOM_CLIENT'];
		$this->tab_extra_result['19'] = 'cust_address => '.$translation['ADRESSE_CLIENT'];
		$this->tab_extra_result['20'] = 'cust_zip => '.$translation['CODE_POSTAL_CLIENT'];
		$this->tab_extra_result['21'] = 'cust_city => '.$translation['VILLE_CLIENT'];
		$this->tab_extra_result['22'] = 'cust_country => '.$translation['PAYS_CLIENT_ISO'];
		$this->tab_extra_result['23'] = 'cust_phone => '.$translation['TELEPHONE_CLIENT'];
		$this->tab_extra_result['24'] = 'url_success => '.$translation['URL_SUCCESS'];
		$this->tab_extra_result['25'] = 'url_refused => '.$translation['URL_REFUS'];
		$this->tab_extra_result['26'] = 'url_referral => '.$translation['URL_REFUS_AUTORISATION'];
		$this->tab_extra_result['27'] = 'url_cancel => '.$translation['URL_ANNULATION'];
		$this->tab_extra_result['28'] = 'url_return => '.$translation['URL_DEFAUT'];
		$this->tab_extra_result['29'] = 'url_error => '.$translation['URL_ERREUR'];
		$this->tab_extra_result['99'] = $translation['ERREUR_INCONNUE_DANS_REQUETE'];
	}

	/* ******************* *
	 * CALCUL DE SIGNATURE *
	 * ******************* */
	/**
	 * Génère la signature à envoyer à la plateforme à partir des champs enregistrés, la stocke
	 * dans $this->signature et la renvoie.
	 * @param $hashed boolean true par défaut ; mettre à false pour obtenir la signature avant hachage
	 * @return string la signature calculée
	 */
	function generateRequestSignature($hashed=true)
	{
		$sign_content = "";
		foreach ($this->request_signature as $field)
		{
			$sign_content .= $this->$field;
			$sign_content .= "+";
		}
		$sign_content .= $this->get("key");
		$this->signature = $hashed ? sha1($sign_content) : $sign_content;
		return $this->signature;
	}
	
	/**
	 * Génère la signature de la réponse à partir des champs enregistrés, la stocke
	 * dans $this->signature et la renvoie.
	 * @param $hashed boolean true par défaut ; mettre à false pour obtenir la signature avant hachage
	 * @return string la signature calculée
	 */
	function generateResponseSignature($hashed=true)
	{
		$sign_content = "";
		foreach ($this->response_signature as $field)
		{
			$sign_content .= $this->$field;
			$sign_content .= "+";
		}
		$sign_content .= ($this->hash != '') ? $this->hash."+" : "";
//		$sign_content .= ($this->contrib=='cscart2.0.12_1.3a') ? '' : '+'.$this->get('contrib');
		$sign_content .= $this->get("key");
		$this->signature = $hashed ? sha1($sign_content) : $sign_content;
		return $this->signature;
	}
	
	
	/* ****************************************** *
	 * CONSTRUCTION DE LA REQUETE A LA PLATEFORME *
	 * ****************************************** */
	/**
	 * Renvoie true si tous les champs obligatoires de la requête ont été préparés, false sinon
	 * @return boolean
	 */
	function isRequestReady()
	{
		foreach ($this->getAttributeList("request_mandatory") as $field_name)
		{
			$value = $this->$field_name;
			if($value === null)
				return false;
		}
		return true;
	}
	 
	/**
	 * Renvoie le code html du formulaire utilisé pour rediriger le client vers la plateforme de paiement
	 * @param $enteteMethod	POST ou GET ; utilisez de préférence POST, sinon voir aussi getRequestUrlEncodedFields
	 * @param $enteteAdd attributs supplémentaires pour la balise <form>
	 * @param $inputType type des entrées, hidden par défaut
	 * @param $buttonValue texte du bouton
	 * @param $buttonAdd attributs supplémentaires du bouton
	 * @param $buttonType type du bouton, par défaut submit
	 * @return string
	 */
	function getRequestHtmlForm($enteteAdd='',$inputType='hidden',
								$buttonValue='Aller sur la plateforme de paiement',$buttonAdd='',$buttonType='submit')
	{
		if(!$this->isRequestReady()) { return false; }
		
		$html  = "";
		$html .= '<form action="'.$this->platform_url.'" method="POST" '.$enteteAdd.'>';
		$html .= "\n";
		$html .= $this->getRequestHtmlInputs($inputType);
		$html .= '<input type="'.$buttonType.'" value="'.$buttonValue.'" '.$buttonAdd.'/>';
		$html .= "\n";
		$html .= '</form>';
		return $html;
	}
	
	/**
	 * Renvoie le code html des inputs du formulaire de redirection vers la plateforme
	 * @param $inputType par défaut hidden
	 * @return string
	 */
	function getRequestHtmlInputs($inputType='hidden')
	{
		if(!$this->isRequestReady()) { return false; }
		
		$html = "";
		foreach ($this->getAttributeList("request_mandatory") as $field_name)
		{
			$value = $this->$field_name;
			$html .= '<input type="'.$inputType.'" name="'.$field_name.'" value="'.$value.'" />';
			$html .= "\n";
		}
		foreach ($this->getAttributeList("request_optionnal") as $field_name)
		{
			if( substr($field_name,0,8) == 'redirect' && !$this->isRedirectEnabled() )
			{
				continue;
			}
			$value = $this->$field_name;
			if($value !== null)
			{
				$html .= '<input type="'.$inputType.'" name="'.$field_name.'" value="'.$value.'" />';
				$html .= "\n";
			}
		}
		$sign = $this->generateRequestSignature();
		$html .= '<input type="'.$inputType.'" name="signature" value="'.$sign.'" />';
		$html .= "\n";
		return $html;
	}
	
	/**
	 * Renvoie l'url de la plateforme avec les paramètres à transmettre encodés (méthode GET)
	 * Utiliser de préférence un formulaire POST (url plus propre, pas de limite à la longueur des paramètres),
	 * cf. getRequestHtmlForm
	 * @return unknown_type
	 */
	function getRequestUrl()
	{
		if(!$this->isRequestReady()) { return false; }
		
		return $this->platform_url . '?' . $this->getRequestUrlEncodedFields();
	}
	
	/**
	 * Renvoie les paramètres url encodés à transmettre lors de la redirection vers la plateforme,
	 * si vous utilisez un lien plutôt qu'un formulaire.
	 * Utilisez de préférence un formulaire POST (url plus propre, pas de limite à la longueur de paramètres) ou
	 * évitez d'utiliser les paramètres optionnels trop longs
	 * @return string ex : amount=1000&order_id=123&order_info=10%20euros%20%E0%20payer&...
	 */
	function getRequestUrlEncodedFields()
	{
		if(!$this->isRequestReady()) { return false; }
		
		$fields = "";
		foreach ($this->getAttributeList("request_mandatory") as $field_name)
		{
			$value = $this->$field_name;
			$fields .= $field_name."=".rawurlencode($value);
			$fields .= "&";
		}
		foreach ($this->getAttributeList("request_optionnal") as $field_name)
		{
			if( substr($field_name,0,8) == 'redirect' && !$this->isRedirectEnabled() )
			{
				continue;
			}
			$value = $this->$field_name;
			if($value !== null)
			{
				$fields .= $field_name."=".rawurlencode($value);
				$fields .= "&";
			}
		}
		$sign  = $this->generateRequestSignature();
		$fields .= "signature=$sign";
		return $fields;
	}
	
	/**
	 * return whether the automatic redirection is enabled or not
	 * (true if redirect_enabled = true or c.i. "true")
	 * @return boolean
	 */
	function isRedirectEnabled()
	{
		// accept true, 1, '1', "True"(ci)
		return (strval($this->redirect_enabled) === "1"
			|| strtolower($this->redirect_enabled) === "true");
	}
	
	/**
	 * Génération d'un trans_id unique pour la transaction de ce site pour la journée
	 * Le renvoie et le stocke dans $this->trans_id
	 * @return string
	 */
	function generateTrans_id()
	{
		/*
		 * On utilise le nombre de dixièmes de seconde depuis minuit, qui respecte la spéc :
		 * - 6 chiffres
		 * - de 000000 à 864000, donc inférieur à 899999
		 * - unique sur une journée (sauf si 2 clients paient à moins de 0.1 seconde d'intervalle)
		 */
		list($usec, $sec) = explode(" ", microtime());	// microsecondes, compatible php4
		$temp = ($this->timestamp + $usec - strtotime('today 00:00')) * 10;
		$temp = sprintf('%06d',$temp);
		
		$this->trans_id = $temp;
		return $this->trans_id;
	}
	
	/**
	 * Renvoie et stocke dans $this->trans_date la date au format AAAAMMJJHHmmSS
	 * @return string
	 */
	function getTrans_date(){
		$temp = gmdate('YmdHis',$this->timestamp);

		$this->trans_date=$temp;
		return $this->trans_date;
	}
	
	
	/* ********************************** *
	 * ANALYSE DE LA REPONSE DE LA BANQUE *
	 * ********************************** */
	/**
	 * Set the response-related attributes from given tab ($_REQUEST by default)
	 * @param $tab
	 */
	function setResponseFromPost($tab=null, $key_test=null, $key_prod=null, $ctx_mode=null)
	{
		$tab = isset($tab) ? $tab : $_REQUEST;
		foreach ( $this->getAttributeList("response_all") as $field_name )
		{
			$this->$field_name = isset($tab[$field_name]) ? $tab[$field_name] : null;
		}
		if (isset($key_test)) {$this->key_test = $key_test;}
		if (isset($key_prod)) {$this->key_prod = $key_prod;}
		if (isset($ctx_mode)) {$this->ctx_mode = $ctx_mode;}
		$this->hash = isset($tab['hash']) ? $tab['hash'] : null;
		$this->received_signature = isset($tab['signature']) ? $tab['signature'] : null;
	}
	
	/**
	 * Compare la signature soumise avec celle calculée à partir des champs
	 * @param string $post_signature
	 * @return boolean true si les deux signatures sont identiques
	 */
	function isAuthentifiedResponse()
	{
		return ($this->generateResponseSignature() == $this->received_signature);
	}
	
	/**
	 * Renvoie true si le code retour enregistré correspond à un paiement réussi, false sinon
	 * @return boolean
	 */
	function isAcceptedPayment()
	{
		return ($this->result == '00');
	}
	
	/**
	 * Renvoie true si le code retour enregistré correspond à une annulation client, false sinon
	 * @return boolean
	 */
	function isCancelledPayment()
	{
		return ($this->result == '17');
	}
	
	/**
	 * Renvoie, selon le paramètre type, le code retour de la réponse de la banque ou sa traduction
	 * @param $type 'detail' : renvoie le message et les détails éventuels ; 'id' : renvoie le code retour ; sinon juste le message
	 * @return string
	 */
	function getResponseMessage($type='',$lang='fr'){
		// on demande le code...
		if($type == 'id')
			return $this->result;
		
		// ...ou sa traduction ?
		$this->loadResponsesTranslation($lang);
		$return = $this->tab_result[$this->result];
		if( $type == 'detail' && $this->result=='30')
		{
			// avec des détails le cas échéant
			$return .= $this->tab_extra_result[$this->extra_result];
		}

		return $return;
	}
	
	/**
	 * Renvoie la traduction du code retour 3D secure
	 * @return string
	 */
	function getReponse3DSec($lang='fr'){
		$this->loadResponsesTranslation($lang);
		$return = '';
		if($this->warranty_result!=''){
			$return .= $this->tab_warranty_result[$this->warranty_result];
		}

		return $return;
	}
	
	/* ********************************** *
	 * REPONSE A L'APPEL DE L'URL SERVEUR *
	 * ********************************** */
	/**
	 * Renvoie une réponse interprétable par la plateforme dans le cadre de l'appel de l'url serveur
	 * @param $success vrai pour donner une réponse positive à la plateforme, faux sinon ; si null, on utilise la méthode isAuthentifiedResponse
	 * @param $message informations complémentaires sur le résultat du traitement de l'appel serveur
	 * @return string
	 */
	function getCheckUrlResponse($code='', $extra_msg="")
	{
		$success = false;
		$message  = '';
		
		// Messages prédéfinis selon le cas
		$cases = array(
			'payment_ok' 				=> array(true,'Paiement valide traité'),
			'payment_ko' 				=> array(true,'Paiement invalide traité'),
			'payment_ok_already_done' 	=> array(true,'Paiement valide traité, déjà enregistré'),
			'order_not_found' 			=> array(false,'Impossible de retrouver la commande'),
			'payment_ko_on_order_ok' 	=> array(false,'Code paiement invalide reçu pour une commande déjà validée'),
			'auth_fail' 				=> array(false,'Echec authentification'),
			'ok' 						=> array(true,''),
			'ko' 						=> array(false,'')
		);
		
		if(array_key_exists($code,$cases))
		{
			$success = $cases[$code][0];
			$message = $cases[$code][1];
		}
		
		$message .= ' '.$extra_msg;
		$message = str_replace("\n",'',$message);
		
		$response  = "";
		$response = '<span style="display:none">';
		$response .= $success ? "OK-" : "KO-";
		$response .= $this->get('hash');
		$response .= ($message===' ') ? "\n" : "=$message\n";
		$response .= '</span>';
		return $response;
	}
}

?>