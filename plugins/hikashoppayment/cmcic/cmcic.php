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
class plgHikashoppaymentCmcic extends hikashopPaymentPlugin {
	var $accepted_currencies = array('EUR','USD','GBP','CHF');
	var $multiple = true;
	var $name = 'cmcic';
	var $doc_form = 'cmcic';

	var $pluginConfig = array(
		'tpe' => array('TPE', 'input'),
		'key' => array('Key', 'input'),
		'societe' => array('Societe', 'input'),
		'bank' => array('Bank', 'list', array(
			'cm' => 'Credit Mutuel',
			'cic' => 'Groupe CIC',
			'obc' => 'OBC')
		),
		'debug' => array('DEBUG', 'boolean','0'),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus', 'cancelled'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus', 'confirmed')
	);

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'CMCIC';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}

	function onBeforeOrderCreate(&$order,&$do){
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(empty($this->payment_params->tpe) || empty($this->payment_params->societe) || empty($this->payment_params->key)) {
			$this->app->enqueueMessage('Please check your &quot;CM-CIC&quot; plugin configuration');
			$do = false;
		}
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$httpsHikashop = HIKASHOP_LIVE; //str_replace('http://','https://', HIKASHOP_LIVE);
		$notify_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=cmcic&tmpl=component&orderId='.$order->order_id.'&lang='.$this->locale;
		$return_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=cmcic&tmpl=component&cmcic_return=1&orderId='.$order->order_id.'&lang='.$this->locale;

		$localeCM = 'FR';
		if( in_array($this->locale, array('fr','en','de','it','es','nl','pt')) ) {
			$localCM = strtoupper($this->locale);
		}

		if(@$this->payment_params->sandbox) {
			$urls = array(
				'cm' => 'https://paiement.creditmutuel.fr/test/paiement.cgi',
				'cic' => 'https://ssl.paiement.cic-banques.fr/test/paiement.cgi',
				'obc' => 'https://ssl.paiement.banque-obc.fr/test/paiement.cgi'
			);
		} else {
			$urls = array(
				'cm' => 'https://paiement.creditmutuel.fr/paiement.cgi',
				'cic' => 'https://ssl.paiement.cic-banques.fr/paiement.cgi',
				'obc' => 'https://ssl.paiement.banque-obc.fr/paiement.cgi'
			);
		}
		if(!isset($this->payment_params->bank) || !isset($urls[$this->payment_params->bank]) ) {
			$this->payment_params->bank = 'cm';
		}
		$this->url = $urls[$this->payment_params->bank];

		$this->vars = array(
			'TPE' => trim($this->payment_params->tpe),
			'date' => date('d/m/Y:H:i:s'),
			'montant' => number_format($order->cart->full_total->prices[0]->price_value_with_tax, 2, '.', '') . $this->currency->currency_code,
			'reference' => $order->order_number,
			'texte-libre' => '',
			'version' => '3.0',
			'lgue' => $localeCM,
			'societe' => trim($this->payment_params->societe),
			'mail' => $this->user->user_email,
		);

		$this->vars['MAC'] = $this->generateHash($this->vars, $this->payment_params->key, 19);

		if( @$this->payment_params->debug ) {
			echo 'Data sent<pre>' . var_export($this->vars, true) . '</pre>';
		}

		$this->vars['url_retour'] = HIKASHOP_LIVE . 'index.php?option=com_hikashop';
		$this->vars['url_retour_ok'] = $return_url;
		$this->vars['url_retour_err'] = $return_url;

		$this->showPage('end');
		return true;
	}

	function onPaymentNotification(&$statuses) {
		$finalReturn = isset($_GET['cmcic_return']);
		if($finalReturn) {
			$order_id = (int)@$_GET['orderId'];
		} else {
			$reference = @$_POST['reference'];
			$db = JFactory::getDBO();
			$db->setQuery('SELECT order_id FROM '.hikashop_table('order').' WHERE order_number='.$db->Quote($reference).';');
			$order_id = (int)$db->loadResult();
		}
		if(empty($order_id)){
			ob_clean();
			echo "version=2\ncdr=1\n";
			exit;
		}

		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		if($finalReturn) {
			return $this->onPaymentUserReturn($dbOrder);
		}

		$vars = array(
			'TPE' => $this->payment_params->tpe,
			'date' => @$_POST['date'],
			'montant' => @$_POST['montant'],
			'reference' => @$_POST['reference'],
			'texte-libre' => @$_POST['texte-libre'],
			'version' => '3.0',
			'code-retour' => @$_POST['code-retour'],
			'cvx' => @$_POST['cvx'],
			'vld' => @$_POST['vld'],
			'brand' => @$_POST['brand'],
			'status3ds' => @$_POST['status3ds'],
			'numauto' => @$_POST['numauto'],
			'motifrefus' => @$_POST['motifrefus'],
			'originecb' => @$_POST['originecb'],
			'bincb' => @$_POST['bincb'],
			'hpancb' => @$_POST['hpancb'],
			'ipclient' => @$_POST['ipclient'],
			'originetr' => @$_POST['originetr']
		);

		if($this->payment_params->debug){
			echo print_r($vars,true)."\r\n\r\n";
		}

		if(empty($dbOrder)){
			$msg = ob_get_clean();
			echo "version=2\ncdr=1\n";
			$msg .= "\r\n".'POST[reference] invalid ("'.$vars['reference'].'")';
			if($this->payment_params->debug) $this->writeToLog($msg);
			exit;
		}

		if(empty($_POST['MAC'])) {
			$msg = ob_get_clean();
			echo "version=2\ncdr=1\n";
			$msg .= "\r\n".'POST[MAC] not present';
			if($this->payment_params->debug) $this->writeToLog($msg);
			exit;
		}

		if($_POST['TPE'] != $this->payment_params->tpe) {
			$msg = ob_get_clean();
			echo "version=2\ncdr=1\n";
			$msg .= "\r\n".'POST[TPE] invalid ("'.$_POST['TPE'].'" != "'.$this->payment_params->tpe.'")';
			if($this->payment_params->debug) $this->writeToLog($msg);
			exit;
		}

		if(strtolower($_POST['MAC']) != $this->generateHash($vars, $this->payment_params->key, 21)) {
			$msg = ob_get_clean();
			echo "version=2\ncdr=1\n";
			$msg .= "\r\n".'POST[MAC] invalid ("'.$_POST['MAC'].'" != "'.$this->generateHash($vars, $this->payment_params->key, 21).'")';
			if( $this->payment_params->debug ) $this->writeToLog($msg);
			exit;
		}

		$history = new stdClass();
		$history->notified = 0;
		$history->amount = $vars['montant'];
		$history->data = $vars['numauto'] . "\r\n" . ob_get_clean();

		if( $this->payment_params->sandbox ) {
			$completed = ($vars['code-retour'] == 'payetest');
		} else {
			$completed = ($vars['code-retour'] == 'paiement');
		}

		if($completed) {
			$order_status = $this->payment_params->verified_status;
			$history->notified = 1;
		} else {
			$order_status = $this->payment_params->invalid_status;
			$order_text = $vars['motifrefus'];
		}

		$email = true;
		if(!empty($order_text))
			$email = $order_text;
		$this->modifyOrder($order_id, $order_status, $history, $email);

		$msg = ob_get_clean();
		echo "version=2\ncdr=0\n";
		if( $this->payment_params->debug )
			$this->writeToLog($msg);
		exit;
	}

	function onPaymentUserReturn($dbOrder) {
		$vars = array(
			'reference' => @$_GET['orderId']
		);

		if(empty($dbOrder)){
			$msg = ob_get_clean();
			echo 'Could not load any order for your notification '.$vars['reference'];
			return false;
		}

		$httpsHikashop = HIKASHOP_LIVE; //str_replace('http://','https://', HIKASHOP_LIVE);
		$return_url = $httpsHikashop . 'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=' . $dbOrder->order_id . $this->url_itemid;
		$cancel_url = $httpsHikashop . 'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=' . $dbOrder->order_id . $this->url_itemid;

		if($dbOrder->order_status != $this->payment_params->verified_status) {
			$this->app->enqueueMessage(JText::_('TRANSACTION_DECLINED'));
			$this->app->redirect($cancel_url);
		}

		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM '. hikashop_table('history') .' WHERE history_order_id='. $dbOrder->order_id.' AND history_new_status='.$db->Quote($this->payment_params->verified_status).' ORDER BY history_created DESC;');
		$histories = $db->loadObjectList();
		foreach($histories as $history) {
			$data = $history->history_data;
			if(strpos($data, "\n--\n") !== false) {
				$data = trim(substr($data, 0, strpos($data, "\n--\n")));
				$this->app->enqueueMessage($data);
				break;
			}
		}
		$this->app->redirect($return_url);
	}

	function generateHash($vars, $key, $nb) {
		$str = implode('*',$vars);
		$l = $nb - count($vars);
		$str .= str_pad('', $l, '*');

		$hexStrKey = substr($key, 0, 38);
		$hexFinal = '' . substr($key, 38, 2) . '00';
		$cca0 = ord($hexFinal);
		if($cca0 > 70 && $cca0 < 97) {
			$hexStrKey .= chr($cca0-23) . substr($hexFinal, 1, 1);
		} elseif(substr($hexFinal, 1, 1) == 'M')  {
			$hexStrKey .= substr($hexFinal, 0, 1) . '0';
		} else {
			$hexStrKey .= substr($hexFinal, 0, 2);
		}
		$hKey = pack('H*', $hexStrKey);

		return strtolower($this->hmacsha1($str, $hKey));
	}

	function hmacsha1($data,$key) {
		if(function_exists('hash_hmac'))
			return hash_hmac('sha1', $data, $key);

		if(!function_exists('sha1'))
			die('SHA1 function is not present');

		if(strlen($key) > 64)
			$key = pack('H*',sha1($key));

		$key  = str_pad($key, 64, chr(0x00));
		$ipad = str_pad('', 64, chr(0x36));
		$opad = str_pad('', 64, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;

		return sha1($k_opad.pack('H*',sha1($k_ipad.$data)));
	}
}
