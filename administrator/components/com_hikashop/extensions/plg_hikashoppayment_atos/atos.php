<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashoppaymentAtos extends hikashopPaymentPlugin
{
	var $multiple = true;
	var $name = 'atos';
	var $uniq_merchant = true;

	var $sync_currencies = array(
		'EUR'=>'978','USD'=>'840','GBP'=>'826','JPY'=>'392','CAD'=>'124','AUD'=>'036','CHF'=>'756',
		'MXN'=>'484','TRY'=>'949','NZD'=>'554','NOK'=>'578','BRL'=>'986','ARS'=>'032','KHR'=>'116',
		'TWD'=>'901','SEK'=>'752','DKK'=>'208','KRW'=>'410','SGD'=>'702','XAF'=>'952'
	);
	var $accepted_currencies = array(
		'EUR','USD','GBP','JPY','CAD','AUD','CHF',
		'MXN','TRY','NZD','NOK','BRL','ARS','KHR',
		'TWD','SEK','DKK','KRW','SGD','XAF'
	);

	var $cards = array(
		'CB','VISA','MASTERCARD','AMEX','DINERS','FINAREF','FNAC','CYRILLUS',
		'PRINTEMPS','KANGOUROU','SURCOUF','POCKETCARD','CONFORAMA','NUITEA','AURORE',
		'PASS','PLURIEL','TOYSRUS','CONNEXION','HYPERMEDIA','DELATOUR','NORAUTO','NOUVFRONT',
		'SERAP','BOURBON','COFINOGA','COFINOGA_BHV','COFINOG _CASINOGEANT','COFINOGA_DIAC',
		'COFINOGA_GL','COFINOGA_GOSPORT','COFINOGA_MONOPRIX','COFINOGA_MRBRICOLAGE','COFINOGA_SOFICARTE',
		'COFINOGA_SYGMA','JCB','DELTA','SWITCH','SOLO'
	);

	var $messageEng = array(
		'invalid_transaction' => 'Invalid transaction', 'invalid_amount' => 'Invalid amount', 'refused_payment' => 'Refused payment',
		'invalid_card_number' => 'Invalid card number', 'unknow_transmitter' => 'Unknow transmitter', 'format_error' => 'Format error',
		'expired_card' => 'Expired card', 'fraud_suspected' => 'Fraud suspected', 'lost_card'=>'Lost card', 'stolen_card' => 'Stolen card',
		'unauthorized_transac' => 'Unauthorized transaction', 'security_rules' => 'Security rules non respected', 'server_error' => 'Server error',
		'error_response' => 'error call response\n', 'response_not_found' => 'executable response not found', 'api_error' => 'API call error\n',
		'error_msg' => 'Error message: ', 'accepted' => 'Accepted', 'payment_pending' => 'Payment is pending',	'pending' => 'Pending',
		'cannot_run_binaries' => 'It seems that the safe mode option of php is active. You are not able to run binairies files into the specified upload folder. Try to use this one: ',
		'path_too_long' => 'The cancel url is probably too long and will cause errors. Please change it to a shorter one. (Actual url: %s - Maximum size: 78)',
		'cancel_path_too_long' => 'The cancel url is probably too long and will cause errors. Please change it to a shorter one. (Actual url: %s - Maximum size: 78)',
		'return_path_too_long' => 'The return url is probably too long and will cause errors. Please change it to a shorter one. (Actual url: %s - Maximum size: 78)',
		'upload_path_too_long' => 'The path to the folder where files have to be uploaded is probably too long and will cause errors. Please change it to a shorter one. (Actual path: %s - Maximum size: 78)',
		'autoresponse_cannot_be_created' => 'The autoresponse file doesn\'t exists and cannot be generated. Please read the documentation to see how to create this file.',
		'logo_path_outside' => 'Wrong logo folder path. The logo folder have to be inside the website folder.',
		'error_copy_logos' => 'Error: one or more pictures cannot be copied in the specified folder. Please copy these pictures from %s to %s via FTP',
		'wrong_file' => 'Error: wrong file uploaded', 'binary_folder_not_exists' => 'The specified folder for binary files doesn\'t exist (%s',
		'request_not_exists' => 'The request file doesn\'t exist (%srequest', 'response_not_exists' => 'The request file doesn\'t exist (%sresponse',
		'certif_not_exists' => 'The certificate file doesn\'t exist ( %s ).', 'logo_folder_error' => 'The specified folder for cards\' pictures doesn\'t exist (%s)',
		'file_x_not_exists' => 'The %s file doesn\'t exist (%s)', 'file_generated' => ' This file name has been automatically generated using your merchant id so please verify it!',
		'param_folder_not_exists' => 'The specified folder for parameters files doesn\'t exist (%s)',
		'wrong_path_to_x' => 'The path to the %s in your pathfile is not the same as the one you specified here (Found: %s. It should be: %s)',
		'safe_mode_activated' => 'It seems that the safe mode option of php is active. You have to upload manually (for example via FTP) the binary files (request, response) and the parameters files (pathfile, parcom.[service], certif.xx.xxxxxxxxxxxxx and parmcom.xxxxxxxxxxxxx). Please specify the folder where this files are uploaded to allow a validation.',
		'missing_merchant_coutry' => 'Please specify your merchant country before uploading your certificate',
		'missing_merchant_id' => 'Please specify your merchant id before uploading your certificate',
		'copy_logo' => 'The specified logo folder is empty. Please copy this pictures from %s to %s'
	);

	var $messageFr = array(
		'invalid_transaction' => 'Transaction invalide', 'invalid_amount' => 'Montant invalide', 'refused_payment' => 'Paiement refusee',
		'invalid_card_number' => 'Numero de carte non valide', 'unknow_transmitter' => 'Emetteur de carte inconnu', 'format_error' => 'Erreur de format',
		'expired_card' => 'Carte expiree', 'fraud_suspected' => 'Fraude suspectee', 'lost_card'=>'Carte perdue', 'stolen_card' => 'Carte volee',
		'unauthorized_transac' => 'Transaction non autorisee', 'security_rules' => 'Regles de securite non respectes', 'serveur_error' => 'Erreur du serveur',
		'error_response' => 'erreur appel response\n', 'response_not_found' => 'executable response non trouve',
		'api_error' => 'Erreur lors de l\'appel de l\'API\n', 'error_msg' => 'Message d\'erreur: ',	'accepted' => 'Accepte', 'payment_pending' => 'Paiement en attente',
		'pending' => 'En attente',
		'cannot_run_binaries' => 'Il semble que le safe mode de php soit actif, vous n\'etes donc pas autorise a executer les fichiers binaires dans le dossier specifie. Essayez avec celui-ci: ',
		'cancel_path_too_long' => 'L\'url d\annulation est trop longue et peut causer des erreurs. Changez la pour une url plus courte. (Actuelement: %s - Taille maximum: 78)',
		'return_path_too_long' => 'L\'url de retour est trop longue et peut causer des erreurs. Changez la pour une url plus courte. (Actuelement: %s - Taille maximum: 78)',
		'upload_path_too_long' => 'Le chemin vers le dossier de téléchargement est trop long et peut causer des erreurs. Changez le pour une chemin plus courte. (Actuelement: %s - Taille maximum: 78)',
		'autoresponse_cannot_be_created' => 'Le fichier de reponse automatique n\'existe pas et ne peut pas être créé. Veuillez vous référer à la documentation pour savoir comment créer ce fichier.',
		'logo_path_outside' => 'Le chemin du dossier des logos est incorrect. Ce dossier doit être à l\interieur de votre dossier principal (dossier du site).',
		'error_copy_logos' => 'Erreur: une ou plusieurs images ne peuvent être copiées dans le dossier spécifié. Veuillez copier ces images du dossier %s au dossier %s.',
		'wrong_file' => 'Erreur: le fichier séléctionné n\'est pas celui demandé', 'binary_folder_not_exists' => 'Le dossier spécifié pour les fichiers binaires n\'existe pas (%s',
		'request_not_exists' => 'Le fichier request n\'existe pas (%srequest', 'response_not_exists' => 'Le fichier response n\'existe pas (%sresponse',
		'certif_not_exists' => 'Le fichier de certificat n\'existe pas ( %s ).', 'logo_folder_error' => 'Le dossier spécifié pour les logos de carte n\'existe pas (%s)',
		'file_x_not_exists' => 'Le fichier %s n\'existe pas (%s)', 'file_generated' => ' Ce nom de fichier a été détecté en utilisant votre id marchant. Verifiez donc que ce dernier est bon!',
		'param_folder_not_exists' => 'Le dossier spécifié pour les fichiers de paramètre n\'existe pas (%s)',
		'wrong_path_to_x' => 'Le chemin vers le %s trouvé dans votre fichier pathfile n\'est pas le même que celui que vous avez spécifié ici (Trouvé: %s. Cela devrait être: %s)',
		'safe_mode_activated' => 'Il semble que le safe mode de php soit activé. Vous devez donc télécharger manuellement (par exemple par FTP) les fichiers binaires (request, reponse) ainsi que les fichiers de parametres (pathfile, parcom.e-transaction, certif.xx.xxxxxxxxxxxxxxx et parcom.xxxxxxxxxxxxxxxx). Merci de spécifier les dossiers où ces fichiers seront téléchargés de façon à valider vos paramètres.',
		'missing_merchant_coutry' => 'Merci de préciser votre pays avant de télécharger votre certificat',
		'missing_merchant_id' => 'Merci de préciser votre id marchand avant de télécharger votre certificat',
		'copy_logo' => 'Le dossier spécifié pour les logos de carte est vide. Merci de copier les images necessaire du dossier %s vers le dossier %s'
	);

	var $debugData = array();

	function needCC(&$method){
		if(@$method->payment_params->period<100 && @$method->payment_params->period>0 && @$method->payment_params->instalments<=3 && @$method->payment_params->instalments>=2 && @$method->payment_params->force_instalments==0){
			$onclick = '';
			$config = hikashop_config();
			if($config->get('auto_submit_methods',1)){
				$onclick = ' onclick="this.form.action=this.form.action+\'#hikashop_payment_methods\';this.form.submit(); return false;"';
			}
			$method->custom_html='<span style="margin-left:10%">'.JHTML::_('hikaselect.booleanlist', "hikashop_multiple_instalments", '',  $onclick, JText::sprintf( 'PAYMENT_IN_X_TIME' , $method->payment_params->instalments ), JText::sprintf( 'PAY_FULL_ORDER' , '1') ).'</span>';
		}
	}

	function onPaymentSave(&$cart, &$rates, &$payment_id) {
		$_SESSION['hikashop_multiple_instalments'] = @$_REQUEST['hikashop_multiple_instalments'];
		return parent::onPaymentSave($cart, $rates, $payment_id);
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order, $methods, $method_id);
		$tax_total = '';
		$discount_total = '';
		$vars = array(
			"currency_code" => @$this->sync_currencies[$this->currency->currency_code],
			"amount" => str_replace(array('.',','),'',round($order->cart->full_total->prices[0]->price_value_with_tax,2)*100),
		);

		$router = $this->app->getRouter();
		$mode_sef = ($router->getMode() == JROUTER_MODE_SEF) ? true : false;

		if(HIKASHOP_J16){
			$db = JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('extensions', false).' WHERE name=\'plg_system_languagefilter\' AND folder=\'system\' AND enabled=1';
			$db->setQuery($query);
			$plugin = $db->loadResult();
		}
		if(HIKASHOP_J16 && !empty($plugin)){
			if($mode_sef) {
				$vars["automatic_response_url"] = HIKASHOP_LIVE.'atos.php/'.$this->locale;
				$vars["cancel_return_url"] = HIKASHOP_LIVE.'atos.php/'.$this->locale;
				$vars["return_url"] = HIKASHOP_LIVE.'success.php/'.$this->locale;
			}
			else{
				$vars["automatic_response_url"] = HIKASHOP_LIVE.'atos.php?lang='.$this->locale;
				$vars["cancel_return_url"] = HIKASHOP_LIVE.'atos.php?lang='.$this->locale;
				$vars["return_url"] = HIKASHOP_LIVE.'success.php?lang='.$this->locale;
			}
		}
		else{
			$vars["automatic_response_url"] = HIKASHOP_LIVE.'atos.php';
			$vars["cancel_return_url"] = HIKASHOP_LIVE.'atos.php';
			$vars["return_url"] = HIKASHOP_LIVE.'success.php';
		}

		$vars["caddie"] = $order->order_id;
		$vars["customer_email"]=$this->user->user_email;
		$vars["merchant_id"]=$this->payment_params->merchant_id;
		$vars["merchant_country"]=$this->payment_params->merchant_country;
		$vars["payment_means"]=$this->payment_params->payment_means;

		if($this->payment_params->delay<1 || $this->payment_params->delay>99)
			$vars["delay"]=null;
		else
			$vars["delay"]=$this->payment_params->delay;

		if($this->payment_params->enable_validation==1)
			$vars["capture_mode"]="VALIDATION";
		else
			$vars["capture_mode"]="AUTHOR_CAPTURE";

		if($this->payment_params->instalments>0 && $this->payment_params->instalments<4 && ($this->payment_params->force_instalments==1 || @$_SESSION['hikashop_multiple_instalments']==1)){
			$vars["capture_mode"]="PAYMENT_N";
			$vars["data"]="NB_PAYMENT=".$this->payment_params->instalments."\;PERIOD=".$this->payment_params->period."\;INITIAL_AMOUNT=".round($order->cart->full_total->prices[0]->price_value_with_tax/$this->payment_params->instalments,(int)$this->currency->currency_locale['int_frac_digits'])*100;
			if(empty($vars["delay"])){
				$vars["delay"]=1;
			}
		}
		$vars["user_id"]=$this->user->user_id;
		$vars["customer_ip"]=$this->user->user_created_ip;
		$safe_mode = ini_get('safe_mode') == 1 || !strcasecmp(ini_get('safe_mode'), 'On');

		if($safe_mode){
			$vars["upload_folder"]=$this->payment_params->param_folder;
			$vars["bin_folder"]=$this->payment_params->binaries_folder;
		}
		else{
			$os=substr(PHP_OS, 0, 3);
			$os=strtolower($os);
			if($os=='win'){
				if($this->payment_params->upload_folder_relative[1]==':')
					$path=$this->payment_params->upload_folder;
				else
					$path=JPATH_ROOT.DS.$this->payment_params->upload_folder_relative;
			}
			else{
				if($this->payment_params->upload_folder_relative[0]==DS)
					$path=$this->payment_params->upload_folder;
				else
					$path=JPATH_ROOT.DS.$this->payment_params->upload_folder_relative;
			}
			$vars["upload_folder"]=$path;
			$vars["bin_folder"]=$path.'b'.DS;
		}
		$vars["upload_folder"]=$this->_addLastSlash($vars["upload_folder"]);
		$vars["bin_folder"]=$this->_addLastSlash($vars["bin_folder"]);

		$locale = $this->locale;
		switch($this->locale){
			case 'es':
				$locale='sp';
				break;
			case 'ja':
				$locale='jp';
				break;
			case 'nl':
				$locale='du';
				break;
			case 'nb':
				$locale='no';
				break;
			case 'pt':
				$locale='po';
				break;
			case 'sv':
				$locale='su';
				break;
			case 'zh':
				$lang = JFactory::getLanguage();
				if(substr($lang->get('tag'),3)=='TW'){
					$locale='ct';
				}else{
					$locale='cs';
				}
				break;
			case 'de':
				$locale='ge';
				break;
			case 'fr':
			case 'fi':
			case 'pl':
			case 'da':
			case 'ko':
				break;
			default:
				$locale="en";
				break;
		}
		$vars["language"]=$locale;
		$vars["return_url_text"]=JText::_('RETURN_TO_THE_STORE');

		$address=$this->app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		$type = 'billing';
		if(empty($address)){
			$address=$this->app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
			if(!empty($address)){
				$type='shipping';
			}
		}
		if(!empty($address)){
			$address_type = $type.'_address';
			$vars["title"]=substr(@$order->cart->$address_type->address_title,0,3);
			$vars["firstname"]=substr(@$order->cart->$address_type->address_firstname,0,20);
			$vars["lastname"]=substr(@$order->cart->$address_type->address_lastname,0,50);
			$address1 = '';
			$address2 = '';
			if(!empty($order->cart->$address_type->address_street)){
				if(strlen($order->cart->$address_type->address_street)>100){
					$address1 = substr($order->cart->$address_type->address_street,0,100);
					$address2 = substr($order->cart->$address_type->address_street,100,200);
				}else{
					$address1 = $order->cart->$address_type->address_street;
				}
			}
			$vars["address"]=$address1;
			$vars["address2"]=$address2;
			$vars["country"]=@$order->cart->$address_type->address_country->zone_code_3;
			$vars["postal_code"]=substr(@$order->cart->$address_type->address_post_code,0,9);
			$vars["city"]=substr(@$order->cart->$address_type->address_city,0,50);
			$vars["state"]=substr(@$order->cart->$address_type->address_state->zone_name_english,0,50);
			$vars["phone_number"]=substr(@$order->cart->$address_type->address_telephone,0,20);
		}
		if(!empty($this->payment_params->logo_url)){
			$vars['logo_url']=$this->payment_params->logo_url;
		}

		$vars["detail1_description"]=JText::_('ORDER_NUMBER').' :';
		$vars["detail1_text"]=$order->order_number;
		$vars["receipt_complement"]=@$this->payment_params->information;

		$this->vars = $vars;
		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses){
		$element=$this->getMethod();

		$safe_mode = ini_get('safe_mode') == 1 || !strcasecmp(ini_get('safe_mode'), 'On');
		if($safe_mode){
			if($element->payment_params->binaries_folder_relative==$element->payment_params->binaries_folder && $element->payment_params->binaries_folder[0]=='/')
				$binaries_path=$element->payment_params->binaries_folder;
			else
				$binaries_path=JPATH_ROOT.DS.$element->payment_params->binaries_folder_relative;
			$path=JPATH_ROOT.DS.$element->payment_params->param_folder_relative;
			$response_path = $binaries_path;
		}
		else{
			$os=substr(PHP_OS, 0, 3);
			$os=strtolower($os);
			if($os=='win'){
				if($element->payment_params->upload_folder_relative[1]==':')
					$path=$element->payment_params->upload_folder;
				else
					$path=JPATH_ROOT.DS.$element->payment_params->upload_folder_relative;
			}
			else{
				if($element->payment_params->upload_folder_relative[0]==DS)
					$path=$element->payment_params->upload_folder;
				else
					$path=JPATH_ROOT.DS.$element->payment_params->upload_folder_relative;
			}
			$response_path=$path.'b'.DS;
		}

		if(!preg_match('#^[a-zA-Z0-9]+$#', $_POST['DATA']))
			$data = '';
		else
			$data = $_POST['DATA'];

		$message="message=".$data;
		$pathfile="pathfile=".$path."pathfile";
		$path_bin = $response_path."response";

		if($this->payment_params->debug){
			echo print_r("$path_bin $pathfile $message",true)."\n\n\n";
		}
		$result=exec("$path_bin $pathfile $message");
		$table = explode ("!", $result);

		$code = $table[1];
		$error = $table[2];
		$logfile=$path."log.txt";

		$tableau = explode ("!", $result);
		$lang = JFactory::getLanguage();
		$message=$this->_getLanguage();

		if (( $code == "" ) && ( $error == "" ) ){
			echo $message['error_response'];
			echo $message['response_not_fount']. $path_bin."\n";
		}
		else if ( $code != 0 ){
			echo $message['api_error'];
			echo $message['error_msg'].$error."\n";
		}

		$vars = array(
			'code' => $table[1],
			'error' => $table[2],
			'merchant_id' => $table[3],
			'merchant_country' => $table[4],
			'amount' => $table[5],
			'transaction_id' => $table[6],
			'payment_means' => $table[7],
			'transmission_date' => $table[8],
			'payment_time' => $table[9],
			'payment_date' => $table[10],
			'response_code' => $table[11],
			'payment_certificate' => $table[12],
			'authorisation_id' => $table[13],
			'currency_code' => $table[14],
			'card_number' => $table[15],
			'cvv_flag' => $table[16],
			'cvv_response_code' => $table[17],
			'bank_response_code' => $table[18],
			'complementary_code' => $table[19],
			'complementary_info' => $table[20],
			'return_context' => $table[21] ,
			'caddie' => $table[22] ,
			'receipt_complement' => $table[23],
			'merchant_language' => $table[24],
			'language' => $table[25],
			'customer_id' => $table[26],
			'order_id' => $table[27],
			'customer_email' => $table[28],
			'customer_ip_address' => $table[29],
			'capture_day' => $table[30],
			'capture_mode' => $table[31],
			'data' => $table[32]
		);

		if(!empty($vars['caddie'])) {
			$arrayCaddie = unserialize(base64_decode($vars['caddie']));
		}
		$dbOder = null;
		if(!empty($arrayCaddie)) {
			$dbOrder = $this->getOrder((int)@$arrayCaddie['caddie']);
		}
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		if($this->payment_params->notification==0){
			return true;
		}

		$data = array();
		if($this->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
		}

		$order_id = @$dbOrder->order_id;
		if(!empty($dbOrder)){
			$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
			$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
			$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		}else{
			echo "Could not load any order for your notification ".@$vars['transaction_id'];
			return false;
		}

		if($this->payment_params->debug){
			echo print_r($dbOrder,true)."\n\n\n";
		}

		if(!empty($vars['bank_response_code'])){
			$vars['status'] = (int)$vars['bank_response_code'];
		}else{
			$vars['status'] = (int)@$vars['response_code'];
		}

		$history = new stdClass();
		$email = new stdClass();

		if((int)$vars['response_code'] == 17 || (int)$vars['response_code'] == 75) {

			if($dbOrder->order_status == $this->payment_params->verified_status) {
				$this->app->redirect(hikashop_completeLink('checkout&task=after_end&order_id='.$order_id, false, true));
				return true;
			}

			$order_status = $this->payment_params->invalid_status;
			$history->data = ob_get_clean();
			$history->data .= JText::sprintf('ORDER_CANCEL_BY_USER');

			$this->modifyOrder($order_id, $order_status, $history, false);

			$this->app->redirect(hikashop_completeLink('order&task=cancel_order&order_id='.$order_id,false,true));
			return true;
		}

		if(!in_array($vars['status'],array(00))) {
			if($vars['status']==12){
				$vars['message']=$message['invalid_transaction'];
			}elseif($vars['status']==13){
				$vars['message']=$message['invalid_amount'];
			}elseif($vars['status']==05 || $vars['status']==02 || $vars['status']==03 || $vars['status']==04){
				$vars['message']=$message['refused_payment'];
			}elseif($vars['status']==14){
				$vars['message']=$message['invalid_card_number'];
			}elseif($vars['status']==15){
				$vars['message']=$message['unknow_transmitter'];
			}elseif($vars['status']==30){
				$vars['message']=$message['format_error'];
			}elseif($vars['status']==33 || $vars['status']==54){
				$vars['message']=$message['expired_card'];
			}elseif($vars['status']==34 || $vars['status']==-59){
				$vars['message']=$message['fraud_suspected'];
			}elseif($vars['status']==41){
				$vars['message']=$message['lost_card'];
			}elseif($vars['status']==43){
				$vars['message']=$message['stolen_card'];
			}elseif($vars['status']==51){
				$vars['message']=$message['unauthorized_transac'];
			}elseif($vars['status']==61){
				$vars['message']=$message['security_rules'];
			}elseif($vars['status']==90 || $vars['status']==91 || $vars['status']==96 || $vars['status']==97 || $vars['status']==98 || $vars['status']==99){
				$vars['message']=$message['server_error'];
			}else{
				$vars['message']=JText::sprintf('Other error');
			}

			$email = new stdClass();
			$email->body = JText::sprintf('PAYMENT_NOTIFICATION_ERROR',$vars['message'], $vars['status']).'. '.JText::sprintf( 'ORDER_HAVE_BEEN', $this->payment_params->invalid_status )."\r\n\r\n".$order_text;
			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_PROBLEM','Atos',$vars['message']);
			if($this->payment_params->debug){
				echo 'payment with code '.@$vars['status'].(!empty($vars['failed_reason_code'])?' : '.@$vars['failed_reason_code']:'')."\n\n\n";
			}
			$history = new stdClass();
			$history->data = ob_get_clean().'  Bank_response_code:'.$vars['status'].'  Message:'.$vars['message'];
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, $history, $email);

			JError::raiseError(403, JText::_('Access Forbidden'));
			return false;
		}

		$currency_code=$vars['currency_code'];

		$history = new stdClass();
		$history->notified = 0;
		$history->amount = @$vars['amount']. array_search($currency_code, $this->sync_currencies);
		$history->data = ob_get_clean().'Id de la transaction: '.@$vars['transaction_id'];

		if(empty($vars['capture_day']) && $vars['capture_mode']!='VALIDATION'){
			 $order_status = $this->payment_params->verified_status;
			 $vars['payment_status']=$message['accepted'];
		}else{
			 $order_status = $this->payment_params->pending_status;
			 $order_text =$message['payment_pending']."\r\n\r\n".$order_text;
			 $vars['payment_status']=$message['pending'];
		}

		$config =& hikashop_config();
		if($config->get('order_confirmed_status','confirmed') == $order_status) {
			$history->notified = 1;
		}

		$email = new stdClass();
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Atos',$vars['payment_status'],$dbOrder->order_number);
		$email->body = JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Atos',$vars['payment_status']).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order_status])."\r\n\r\n".$order_text;
		$this->modifyOrder($order_id, $order_status, $history, $email);
		return true;
	}

	function onPaymentConfiguration(&$element){
		if(JRequest::getCmd('subtask','')=='logos'){
			$app = JFactory::getApplication();
			$this->view = 'logos';
			$this->noForm=true;

			usort($this->cards, "strcasecmp");
			$element = reset($element);
			$safe_mode = ini_get('safe_mode') == 1 || !strcasecmp(ini_get('safe_mode'), 'On');
			if($safe_mode){
				if(strlen($element->payment_params->logo_folder)>78){
					echo 'the destination folder path is probably too long, try a shorter one';
					$destFolder=JPATH_ROOT.DS.$element->payment_params->logo_folder_relative;
				}
				else{
					$destFolder=JPATH_ROOT.DS.$element->payment_params->logo_folder_relative;
				}
			}
			else{
				if(strlen($element->payment_params->upload_folder_relative)>78){
					echo 'the destination folder path is probably too long, try a shorter one';
					$destFolder=JPATH_ROOT.DS.$element->payment_params->upload_folder_relative;
					$destFolder=$this->_addLastSlash($destFolder);
					$destFolder.='l'.DS;
				}
				else{
					$destFolder=JPATH_ROOT.DS.$element->payment_params->upload_folder_relative;
					$destFolder=$this->_addLastSlash($destFolder);
					$destFolder.='l'.DS;
				}
			}

			jimport('joomla.filesystem.folder');
			if(!(JFolder::exists($destFolder))){
				JFolder::create($destFolder);
			}

			$this->_copyLogos($safe_mode, $element);

			$link_vars=null;
			$link_query = parse_url($_SERVER['REQUEST_URI']);
			parse_str( html_entity_decode($link_query['query']), $link_vars);
			$str = JArrayHelper::getValue($link_vars,'values','');

			$selectedCards=explode(',2,',$str);
			$selectedCards[$a=count($selectedCards)-1]=substr($selectedCards[$a=count($selectedCards)-1],0,-2);
			$finalCard = array();

			$files=JFolder::files($destFolder);
			if(count($files)<1)	$files=JFolder::files(HIKASHOP_MEDIA.'images'.DS.'payment');
			$chk=0;
			foreach($this->cards as $card){
				foreach($files as $pic){
					$name=explode('.',$pic);
					if($card==$name[0] && $name[1]=='gif'){
						$finalCard[$card] = new stdClass();
						$finalCard[$card]->name=$card;
						$finalCard[$card]->present=1;
						$chk=1;
						break;
					}
				}
				if($chk==0){
					$finalCard[$card] =  new stdClass();
					$finalCard[$card]->name=$card;
					$finalCard[$card]->present=0;
				}
				$chk=0;
			}

			foreach($finalCard as $card){
				foreach($selectedCards as $scard){
					if($card->name==$scard){
						$finalCard[$scard]->check=1;
						$chk=1;
						break;
					}
				}
				if($chk==0){
					$finalCard[$card->name]->check=0;
				}
				$chk=0;
			}

			$this->finalCard=$finalCard;

			$js="
function insertCards(){
	var cards = new Array();
	var names = '';
	var checkBox = document.forms['adminForm'].elements['cards[]'];
	for(var i=0,l=checkBox.length;i<l; i++){
		if(checkBox[i].checked){
				cards.push(checkBox[i]);
				names+=checkBox[i].value;
				names+=',2,';
		}
	}
	names=names.substr(0,names.length-1);
	window.top.document.getElementById('plugin_cards').value = names;
	window.top.document.getElementById('plugin_cards_link').href = 'index.php?option=com_hikashop&ctrl=plugins&task=edit&name=atos&plugin_type=payment&subtask=logos&tmpl=component&values='+names;
	window.top.hikashop.closeBox();
}
";
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}else{
			parent::onPaymentConfiguration($element);
			$this->address = hikashop_get('type.address');
		}
	}
	function getPaymentDefaultValues(&$element) {
		$element->payment_name='SIPS ATOS';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->notification=1;
		$element->payment_params->merchant_country='fr';
		$element->payment_params->request_exist=false;
		$element->payment_params->response_exist=false;
		$element->payment_params->certif_exist=false;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
		$element->payment_params->upload_folder=HIKASHOP_MEDIA.DS.'logo';
		$element->payment_params->payment_means='CB,2,VISA,2,MASTERCARD,2';

		$element->payment_params->return_url='';
		$element->payment_params->cancel_url='';
		$element->payment_params->upload_folder=str_replace(JPATH_ROOT.DS,'',HIKASHOP_MEDIA);
		$element->payment_params->logo_folder=str_replace(JPATH_ROOT.DS,'',HIKASHOP_MEDIA).'l'.DS;
		$safe_mode = ini_get('safe_mode') == 1 || !strcasecmp(ini_get('safe_mode'), 'On');
		$safe_mode_dir = ini_get('safe_mode_exec_dir');
		if($safe_mode ){
			$element->payment_params->binaries_folder=$safe_mode_dir;
			$element->payment_params->param_folder=str_replace(JPATH_ROOT.DS,'',HIKASHOP_MEDIA);
		}
	}

	function onPaymentConfigurationSave(&$element){
		$app = JFactory::getApplication();

		$message=$this->_getLanguage();

		if(!empty($element->payment_params->merchant_country))
			$element->payment_params->merchant_country=strtolower($element->payment_params->merchant_country);

		$element->payment_params->merchant_id = preg_replace('#[^a-z_0-9]#','',@$element->payment_params->merchant_id);

		if(!empty($element->payment_params->upload_folder))
			$element->payment_params->upload_folder_relative=$this->_getRelativePath($element->payment_params->upload_folder, $element);
		if(!empty($element->payment_params->logo_folder))
			$element->payment_params->logo_folder_relative=$this->_getRelativePath($element->payment_params->logo_folder, $element);
		if(!empty($element->payment_params->param_folder))
			$element->payment_params->param_folder_relative=$this->_getRelativePath($element->payment_params->param_folder, $element);
		if(!empty($element->payment_params->binaries_folder))
			$element->payment_params->binaries_folder_relative=$this->_getRelativePath($element->payment_params->binaries_folder, $element);

		$safe_mode = ini_get('safe_mode') == 1 || !strcasecmp(ini_get('safe_mode'), 'On');
		$safe_mode_dir = ini_get('safe_mode_exec_dir');
		if($safe_mode){
			if(!$this->_checkOnSafeMode($element))
				$app->enqueueMessage( $message['safe_mode_activated']);
			$safe_mode_dir = ini_get('safe_mode_exec_dir');
			$element->payment_params->safe=true;
			if($app->isAdmin() && empty($element->payment_params->binaries_folder)){
				$app->enqueueMessage($message['cannot_run_binaries'].$safe_mode_dir, 'error');
			}
		}

		if(empty($element->payment_params->upload_folder))
			$element->payment_params->upload_folder=str_replace(JPATH_ROOT.DS,'',HIKASHOP_MEDIA);
		if(empty($element->payment_params->param_folder))
			$element->payment_params->param_folder=str_replace(JPATH_ROOT.DS,'',HIKASHOP_MEDIA);
		if(empty($element->payment_params->logo_folder))
			$element->payment_params->logo_folder=str_replace(JPATH_ROOT.DS,'',HIKASHOP_MEDIA).'l'.DS;
		$safe_mode_dir = ini_get('safe_mode_exec_dir');
		if(empty($element->payment_params->binaries_folder))
			$element->payment_params->binaries_folder=$safe_mode_dir;

		if(!empty($element->payment_params->upload_folder)){
			if(strlen($element->payment_params->upload_folder)>78 )
				$app->enqueueMessage( sprintf($message['upload_path_too_long'], $element->payment_params->upload_folder));
		}
		if(!empty($element->payment_params->binaries_folder)){
			if(strlen($element->payment_params->binaries_folder)>78)
				$app->enqueueMessage(sprintf($message['upload_path_too_long'], $element->payment_params->binaries_folder));
		}

		if(!$safe_mode){
			$this->_uploadFiles('request', $element);
			$this->_uploadFiles('response',$element);
			$this->_uploadFiles('certificate', $element);
			$this->_generateFiles($element);
			$this->_checkFiles($element);
		}
		$this->_copyLogos($safe_mode, $element);
		return true;
	}

	function _uploadFiles($name, &$element){
		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');
		$file = JRequest::getVar( $name, array(), 'files', 'array' );
		$message=$this->_getLanguage();

		if(empty($file['name'])){
				return false;
		}

		$fileName=explode('.',$file['name']);
		$fileName[0]=strtolower($fileName[0]);
		if($fileName[0]!='request' && $fileName[0]!='response' && $fileName[0]!='certif'){
			$app->enqueueMessage( $message['wrong_file'], 'error');
			return true;

		}

		$element->payment_params->upload_folder_relative=$this->_addLastSlash($element->payment_params->upload_folder_relative);
		$folder_path=JPATH_ROOT.DS.$element->payment_params->upload_folder_relative;
		$file_path = strtolower(JFile::makeSafe($file['name']));

		if($name=='certificate'){
			if($fileName[0]!='certif')
				return false;
			$fileName[0]='ct';
			if(empty($element->payment_params->merchant_country) && empty($fileName[1])){
				$app->enqueueMessage( $message['missing_merchant_coutry'], 'error');
				return true;
			}
			if(empty($element->payment_params->merchant_id) && empty($fileName[2])){
				$app->enqueueMessage( $message['missing_merchant_id'], 'error');
				return true;
			}
			if(!empty($fileName[1]))
				$element->payment_params->merchant_country=$fileName[1];
			else
				$fileName[1]=$element->payment_params->merchant_country;
			if(!empty($fileName[2]))
				$element->payment_params->merchant_id=$fileName[2];
			else
				$fileName[2]=$element->payment_params->merchant_id;
			$fileName[1]=strtolower($fileName[1]);
			$name=implode('.',$fileName);
			if(!JFile::upload($file['tmp_name'], $folder_path.'b'.DS . $name)){
				if ( !move_uploaded_file($file['tmp_name'], $folder_path .'b'.DS. $name)) {
					$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD',$file['tmp_name'],$folder_path . $name), 'error');
					return true;
				}
			}
			JPath::setPermissions($folder_path.$name, '0755');
			return true;
		}

		if(!JFile::upload($file['tmp_name'], $folder_path .'b'.DS.$file['name'])){
			if ( !move_uploaded_file($file['tmp_name'], $folder_path .'b'.DS. $file['name'])) {
				$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD',$file['tmp_name'],$folder_path .'b'.DS.$file['name']), 'error');
				return false;
			}
		}
		JPath::setPermissions($folder_path.'b'.DS.$name, '0755');

		$htaccess='deny from all';
		if(!JFile::exists($folder_path.'b'.DS.'.htaccess')){
			JFile::write($folder_path.'b'.DS.'.htaccess', $htaccess);
		}
	}

	function _generateFiles(&$element){
		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');
		if($element->payment_params->debug==1)
			$debug='YES';
		else
			$debug='NO';

		if(empty($element->payment_params->merchant_id)){
			return true;
		}

		$message=$this->_getLanguage();

		$logoPath=JURI::base(true).$element->payment_params->logo_folder_relative;
		$logoPath=str_replace('administrator','',$logoPath);

		$safe_mode = ini_get('safe_mode') == 1 || !strcasecmp(ini_get('safe_mode'), 'On');
		if(str_replace(JPATH_ROOT,'',$element->payment_params->upload_folder)!=$element->payment_params->upload_folder)
			$path=$element->payment_params->upload_folder;
		else
			$path=JPATH_ROOT.DS.$element->payment_params->upload_folder;


		$lang = &JFactory::getLanguage();
		$locale=strtolower(substr($lang->get('tag'),0,2));

		$atos='<?php
$_GET[\'option\']=\'com_hikashop\';
$_GET[\'tmpl\']=\'component\';
$_GET[\'ctrl\']=\'checkout\';
$_GET[\'task\']=\'notify\';
$_GET[\'notif_payment\']=\'atos\';
$_GET[\'lang\']=\''.$locale.'\';
$_REQUEST[\'option\']=\'com_hikashop\';
$_REQUEST[\'tmpl\']=\'component\';
$_REQUEST[\'ctrl\']=\'checkout\';
$_REQUEST[\'task\']=\'notify\';
$_REQUEST[\'notif_payment\']=\'atos\';
$_REQUEST[\'lang\']=\''.$locale.'\';
include(\'index.php\');
';
		$success='<?php header("Location: '.hikashop_frontendLink('index.php?option=com_hikashop&ctrl=checkout&task=after_end').'");';

		$path=$this->_addLastSlash($path);
		$os=substr(PHP_OS, 0, 3);
		$os=strtolower($os);
		if($os=='win')
			$logoPath=str_replace('/',DS,$logoPath);

		$pathfile='DEBUG!'.$debug.'!'."\r\n".'D_LOGO!'.$logoPath.'!'."\r\n".
				'F_DEFAULT!'.$path.'pc.x!'."\r\n".
				'F_PARAM!'.$path.'pc!'."\r\n".
				'F_CERTIFICATE!'.$path.'b'.DS.'ct!'."\r\n";
		$parcom='TEMPLATE!'.$element->payment_params->template.'!'."\r\n";

		$pc='';
		JFile::write($path.'pc.x', $parcom);
		JFile::write($path.'pc.'.$element->payment_params->merchant_id, $pc);
		JFile::write($path.'pathfile', $pathfile);
		$rights=JPath::getPermissions(JPATH_ROOT);
		if($rights[1]!='w' && !(JFile::exists(JPATH_ROOT.DS.'atos.php'))){
			$app->enqueueMessage( $message['autoresponse_cannot_be_created'], 'error');
			return true;
		}
		JFile::write(JPATH_ROOT.DS.'atos.php',$atos);
		JFile::write(JPATH_ROOT.DS.'success.php',$success);

	}

	function getMethod(){
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type=\'atos\'';
		$db->setQuery($query);
		$paymentData = $db->loadObject();
		$paymentData->payment_params = unserialize($paymentData->payment_params);
		return $paymentData;
	}

	function _getRelativePath($path, &$element){
		$message=$this->_getLanguage();

		$app = JFactory::getApplication();
		if(!preg_match('#^([A-Z]:)?/.*#',$path)){
			if(!$path[0]=='/' || !is_dir($path)){
				$pathClean = JPath::clean(HIKASHOP_ROOT.DS.trim($path,DS.' ').DS);
			}
		}

		if(!empty($pathClean)){
			$relativePath=str_replace(JPATH_ROOT.DS,'',$pathClean);
		} else{
			$relativePath=str_replace(JPATH_ROOT.DS,'',$path);
		}

		if(!empty($pathClean)){
			if($relativePath==$pathClean){
				if ($path==$element->payment_params->logo_folder){
					$app->enqueueMessage( $message['logo_path_outside'], 'error');
					return true;
				}
				return $path;
			}
			return $relativePath;
		}
		return $relativePath;

	}

	function _addLastSlash($path){
	 	if($path[strlen($path)-1]!=DS){
			return $path.=DS;
		}
		return $path;
	}

	function _getLanguage(){
		$lang = JFactory::getLanguage();
		$locale=strtolower(substr($lang->get('tag'),0,2));

		if($locale=='fr')
			return ($this->messageFr);
		return ($this->messageEng);
	}

	function _copyLogos($safe_mode, &$element) {
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$app = JFactory::getApplication();

		$message=$this->_getLanguage();
		if(strlen($element->payment_params->logo_folder)>78){
			$app->enqueueMessage('the destination folder path is probably too long, try a shorter one');
			$destFolder=JPATH_ROOT.DS.$element->payment_params->logo_folder_relative;
		}
		else{
			$destFolder=JPATH_ROOT.DS.$element->payment_params->logo_folder_relative;
		}
		$destFolder=$this->_addLastSlash($destFolder);

		$pic=false;
		if(JFolder::exists($destFolder))
			$files=JFolder::files($destFolder);

		if(!empty($files)){
			foreach($files as $file){
				$name=explode('.',$file);
				if($name[1]=='gif')
					$pic=true;
			}
		}

		$safe_mode = ini_get('safe_mode') == 1 || !strcasecmp(ini_get('safe_mode'), 'On');
		if($safe_mode && $pic==false){
			if(empty($files))
				$app->enqueueMessage(sprintf($message['copy_logo'],HIKASHOP_MEDIA.'images'.DS.'payment'.DS,$destFolder));
			return true;
		}

		if(!(JFolder::exists($destFolder))){
			JFolder::create($destFolder);
		}

		$ok = true;
		foreach($this->cards as $card){
			if(JFile::exists(HIKASHOP_MEDIA.DS.'images'.DS.'payment'.DS.$card.'.gif')){
				if(!(JFile::exists($destFolder.$card.'.gif'))){
					if(!JFile::copy(HIKASHOP_MEDIA.'images'.DS.'payment'.DS.$card.'.gif',$destFolder.$card.'.gif')){
						$ok =false;
					}
				}
			}
		}

		JFile::copy(HIKASHOP_MEDIA.'images'.DS.'payment'.DS.'INTERVAL.gif',$destFolder.'INTERVAL.gif');
		JFile::copy(HIKASHOP_MEDIA.'images'.DS.'payment'.DS.'CLEF.gif',$destFolder.'CLEF.gif');

		if(!($ok)){
			$app->enqueueMessage( sprintf($message['error_copy_logos'], HIKASHOP_MEDIA.'images'.DS.'payment' , $destFolder), 'error');
		}

		if(empty($destFolder)){
			 echo 'The specified folder is empty';
				return true;
		}
	}

	function _checkOnSafeMode($element){
		$app = JFactory::getApplication();
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$fail=false;
		$message=$this->_getLanguage();

		if(!empty($element->payment_params->binaries_folder)){
			if($element->payment_params->binaries_folder_relative==$element->payment_params->binaries_folder)
				$binaries_path=$element->payment_params->binaries_folder;
			else
				$binaries_path=JPATH_ROOT.DS.$element->payment_params->binaries_folder_relative;
			if($element->payment_params->param_folder_relative==$element->payment_params->param_folder && $element->payment_params->param_folder[0]=='/')
				$param_path=$element->payment_params->param_folder;
			else
				$param_path=JPATH_ROOT.DS.$element->payment_params->param_folder_relative;
		}
		else{
			return false;
		}

		if(!empty($element->payment_params->logo_folder_relative))
			$logo_path=JPATH_ROOT.DS.$element->payment_params->logo_folder_relative;

		$binaries_path=$this->_addLastSlash($binaries_path);
		$param_path=$this->_addLastSlash($param_path);
		$logo_path=$this->_addLastSlash($logo_path);

		if(JFolder::exists($binaries_path)){
			$os=substr(PHP_OS, 0, 3);
			$os=strtolower($os);
			if($os=='win'){
				if(!JFile::exists($binaries_path.'request.exe')){
					$app->enqueueMessage( sprintf($message['request_not_exists'], $binaries_path).'.exe)', 'error');
					$fail=true;
				}
				if(!JFile::exists($binaries_path.'response.exe')){
					$app->enqueueMessage( sprintf($message['response_not_exists'], $binaries_path).'.exe)', 'error');
					$fail=true;
				}
			}
			else{
				if(!JFile::exists($binaries_path.'request')){
					$app->enqueueMessage(sprintf($message['request_not_exists'], $binaries_path).')', 'error');
					$fail=true;
				}
				if(!JFile::exists($binaries_path.'response')){
					$app->enqueueMessage( sprintf($message['response_not_exists'], $binaries_path).')', 'error');
					$fail=true;
				}
			}
		}
		else{
			$app->enqueueMessage( sprintf($message['binary_folder_not_exists'], $binaries_path).')', 'error');
			return false;
		}

		if(!JFolder::exists($logo_path)){
			$app->enqueueMessage( sprintf($message['logo_folder_error'], $logo_path), 'error');
			return false;
		}

		if(JFolder::exists($param_path)){
			if(!JFile::exists($param_path.'pathfile')){
				$app->enqueueMessage( sprintf($message['file_x_not_exists'], 'pathfile', $param_path.'pathfile'), 'error');
				$fail=true;
			}
			if(!JFile::exists($param_path.'parmcom.'.$element->payment_params->merchant_id)){
				$app->enqueueMessage( sprintf($message['file_x_not_exists'], 'parmcom' , $param_path.'parmcom.'.$element->payment_params->merchant_id ).$message['file_generated'], 'error');
				$fail=true;
			}

			$test=null;
			$parmcomVerif=false;
			$files=JFolder::files($param_path);
			foreach($files as $file){
				preg_match('#^(.*)\.([^.]*)$#',$file,$test);
				if(!empty($test[2]) && $test[1]=='parcom' && strlen($test[2])==15)
					$parmcomVerif=true;
			}
			if($parmcomVerif){
				$app->enqueueMessage( sprintf($message['file_x_not_exists'], 'parmcom.[service]' , $param_path.'parmcom.[service])'), 'error');
				$fail=true;
			}
			if(!JFile::exists($binaries_path.'certif.'.$element->payment_params->merchant_country.'.'.$element->payment_params->merchant_id)){
				if(!empty($element->payment_params->merchant_country) && !empty($element->payment_params->merchant_id))
					$app->enqueueMessage( sprintf($message['file_x_not_exists'], 'certificate' , $binaries_path.'certif.'.$element->payment_params->merchant_country.'.'.$element->payment_params->merchant_id). $message['file_generated'], 'error');
				else
					$app->enqueueMessage( sprintf($message['file_x_not_exists'], 'certificate' , $binaries_path.'certif.[merchant_country].[merchant_id]').$message['file_generated'], 'error');
				$fail=true;
			}
		}
		else{
			$app->enqueueMessage( sprintf($message['param_folder_not_exists'], $param_path) , 'error');
			return false;
		}

		if(!JFile::exists(JPATH_ROOT.DS.'atos.php')){
			$app->enqueueMessage( $message['autoresponse_cannot_be_created'], 'error');
			$fail=true;
		}

		if($fail)
			return false;

		$data=JFile::read($param_path.'pathfile');
		preg_match_all('#([a-z_]+)\!(.+)\!#iU',$data,$matches);
		$i=0;
		foreach($matches[1] as $match){
			if($match == 'D_LOGO'){
				$logoPath=JURI::base(true).$element->payment_params->logo_folder_relative;
				$logoPath=str_replace('administrator','',$logoPath);
				if($os=='win')
					$logoPath=str_replace('/',DS,$logoPath);
				if($matches[2][$i]!=$logoPath){
					$app->enqueueMessage( sprintf($message['wrong_path_to_x'], 'logo folder', $matches[2][$i], $logoPath), 'error');
					$fail=true;
				}
			}
			if($match == 'F_PARAM'){
				if($matches[2][$i]!=$param_path.'parmcom'){
					$app->enqueueMessage(sprintf($message['wrong_path_to_x'], 'parmcom', $matches[2][$i] , $param_path.'parmcom'), 'error');
					$fail=true;
				}
			}
			if($match == 'F_CERTIFICATE'){
				if($matches[2][$i]!=$binaries_path.'certif'){
					$app->enqueueMessage(sprintf($message['wrong_path_to_x'], 'parmcom', $matches[2][$i] , $binaries_path.'certif'), 'error');
					$fail=true;
				}

			}
			$i++;
		}

		if($fail)
			return false;
		return true;
	}

	function _checkFiles(&$element){
		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		$fail=false;
		$message=$this->_getLanguage();

		$os=substr(PHP_OS, 0, 3);
		$os=strtolower($os);

		if($os=='win'){
			if($element->payment_params->upload_folder_relative[1]==':')
				$path=$element->payment_params->upload_folder;
			else
				$path=JPATH_ROOT.DS.$element->payment_params->upload_folder_relative;
		}
		else{
			if($element->payment_params->upload_folder_relative[0]==DS)
				$path=$element->payment_params->upload_folder;
			else
				$path=JPATH_ROOT.DS.$element->payment_params->upload_folder_relative;
		}

		$path=$this->_addLastSlash($path);

		if($os=='win'){
			if(!JFile::exists($path.'b'.DS.'request.exe')){
				$app->enqueueMessage( sprintf($message['request_not_exists'], $path.'b'.DS).'.exe)', 'error');
				$fail=true;
			}
			else{
				$element->payment_params->request_exist=true;
			}
			if(!JFile::exists($path.'b'.DS.'response.exe')){
				$app->enqueueMessage( sprintf($message['response_not_exists'], $path.'b'.DS).'.exe)', 'error');
				$fail=true;
			}
			else{
				$element->payment_params->response_exist=true;
			}
		}
		else{
			if(!JFile::exists($path.'b'.DS.'request')){
				$app->enqueueMessage( sprintf($message['request_not_exists'],$path.'b'.DS).')', 'error');
				$fail=true;
				$element->payment_params->request_exist=false;
			}
			else{
				$element->payment_params->request_exist=true;
			}
			if(!JFile::exists($path.'b'.DS.'response')){
				$app->enqueueMessage( sprintf($message['request_not_exists'], $path.'b'.DS).')', 'error');
				$fail=true;
				$element->payment_params->response_exist=false;
			}
			else{
				$element->payment_params->response_exist=true;
			}
		}
		if(!empty($element->payment_params->merchant_country))
			$element->payment_params->merchant_country=strtolower($element->payment_params->merchant_country);
		if(!JFile::exists($path.'b'.DS.'ct.'.$element->payment_params->merchant_country.'.'.$element->payment_params->merchant_id)){
			if(!empty($element->payment_params->merchant_country) && !empty($element->payment_params->merchant_id)){
				$app->enqueueMessage( sprintf($message['certif_not_exists'], $path.'b'.DS.'ct.'.$element->payment_params->merchant_country.'.'.$element->payment_params->merchant_id).$message['file_generated'], 'error');
				$fail=true;
				$element->payment_params->certif_exist=false;
			}
			else{
				$app->enqueueMessage( sprintf($message['certif_not_exists'], $path.'b'.DS.'ct.[merchant_country].[merchant_id]').$message['file_generated'], 'error');
				$fail=true;
				$element->payment_params->certif_exist=false;
			}
		}
		else{
			$element->payment_params->certif_exist=true;
		}
		if($fail)
			return false;
		return true;
	}
}
