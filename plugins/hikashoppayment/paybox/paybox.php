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
class plgHikashoppaymentPaybox extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		978 => 'EUR'
	);

	var $multiple = true;
	var $name = 'paybox';
	var $pluginConfig = array(
		'pbx_site' => array('Site', 'input'),
		'pbx_rang' => array('Rang', 'input'),
		'pbx_indentifiant' => array('Indentifiant', 'input'),
		'hash' => array('HMAC', 'input'),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'ips' => array('IPS', 'input'),
		'signature' => array('SIGNATURE', 'boolean', '1'),
		'force_card' => array('Force card', 'boolean', '1'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$srv = 'tpeweb.paybox.com';

		if($this->payment_params->sandbox) {
			$srv = 'preprod-tpeweb.paybox.com';
		}

		$this->url = 'https://'.$srv.'/cgi/MYchoix_pagepaiement.cgi';

		$amount = (int)($order->cart->full_total->prices[0]->price_value_with_tax * 100);

		$this->vars = array(
			'PBX_SITE' => trim($this->payment_params->pbx_site),
			'PBX_RANG' => trim($this->payment_params->pbx_rang),
			'PBX_IDENTIFIANT' => trim($this->payment_params->pbx_indentifiant),
			'PBX_TOTAL' => $amount,
			'PBX_DEVISE' => 978,
			'PBX_CMD' => (int)$order->order_id,
			'PBX_PORTEUR' => $this->user->user_email,
			'PBX_RETOUR' => 'mt:M;ref:R;auth:A;err:E;sign:K',
			'PBX_HASH' => 'SHA512',
			'PBX_TIME' => date('c'),
			'PBX_EFFECTUE' => (HIKASHOP_LIVE.'paybox_'.$method_id.'.php?pbx=user&t=confirm'),
			'PBX_ATTENTE' => (HIKASHOP_LIVE.'paybox_'.$method_id.'.php?pbx=user&t=wait'),
			'PBX_REFUSE' => (HIKASHOP_LIVE.'paybox_'.$method_id.'.php?pbx=user&t=refuse'),
			'PBX_ANNULE' => (HIKASHOP_LIVE.'paybox_'.$method_id.'.php?pbx=user&t=cancel'),
			'PBX_REPONDRE_A' => (HIKASHOP_LIVE.'paybox_'.$method_id.'.php')
		);

		if(!empty($this->payment_params->force_card)) {
			$this->vars['PBX_TYPEPAIEMENT'] = 'CARTE';
		}

		$msg = array();
		foreach($this->vars as $k => $v) {
			$msg[] = $k . '=' . $v;
		}
		$msg = implode('&', $msg);

		$binKey = pack('H*', $this->payment_params->hash);
		$this->vars['PBX_HMAC'] = strtoupper(hash_hmac('sha512', $msg, $binKey));
		unset($msg);

		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses) {
		global $Itemid;
		$this->url_itemid = empty($Itemid) ? '' : '&Itemid=' . $Itemid;

		if(JRequest::getVar('pbx', '') == 'user') {
			$app = JFactory::getApplication();
			$t = JRequest::getVar('t', '');
			switch($t) {
				case 'refuse':
					$url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order'.$this->url_itemid;
					break;
				case 'cancel':
					$url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order'.$this->url_itemid;
					break;
				case 'confirm':
				default:
					$url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end'.$this->url_itemid;
					break;
			}
			$app->redirect($url);
			return;
		}

		$method_id = JRequest::getInt('notif_id', 0);
		$this->pluginParams($method_id);
		$this->payment_params =& $this->plugin_params;
		if(empty($this->payment_params))
			exit;

		if(!empty($this->payment_params->ips)){
			$ip = hikashop_getIP();
			$valid = false;
			$ips = explode(';', $this->payment_params->ips);
			foreach($ips as $i) {
				$i = trim($i);
				if($i == $ip) {
					$valid = true;
					break;
				}
			}
			if(!$valid) {
				$email = new stdClass();
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paybox') . ' ' . JText::sprintf('IP_NOT_VALID', '');
				$email->body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_FROM_IP','Paybox',$ip,implode("\r\n",$ips)));
				$action = false;
				$this->modifyOrder($action, null, null, $email);

				JError::raiseError(403, JText::_('Access Forbidden'));
				exit;
			}
		}

		if(function_exists('openssl_pkey_get_public') && (!isset($this->payment_params->signature) || !empty($this->payment_params->signature))) {
			$signature = JRequest::getVar('sign', '');
			if(!empty($signature))
				$signature = base64_decode(urldecode($signature));

			$p_mt = JRequest::getVar('mt', '');
			$p_ref = JRequest::getVar('ref', '');
			$p_auth = JRequest::getVar('auth', '');
			$p_err = JRequest::getVar('err', '');
			$sign_data = 'mt=' . rawurlencode($p_mt) . '&ref=' . rawurlencode($p_ref) . '&auth=' . rawurlencode($p_auth) . '&err' . rawurlencode($p_err);

			$pubkeyid = openssl_pkey_get_public( dirname(__FILE__) . DS . 'paybox_pubkey.pem' );
			if($pubkeyid !== false) {
				$sign = openssl_verify($sign_data, $signature, $pubkeyid);
				openssl_free_key($pubkeyid);

				if($sign !== 1) {
					$ip = hikashop_getIP();
					$email = new stdClass();
					$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Paybox') . ' ' . JText::_('SIGN_NOT_VALID');
					$email->body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_FROM_IP','Paybox',$ip,JText::_('SIGN_NOT_VALID')));
					$action = false;
					$this->modifyOrder($action, null, null, $email);

					JError::raiseError(403, JText::_('Access Forbidden'));
					exit;
				}
			}
		}

		$order_id = (int)JRequest::getInt('ref', 0);
		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder)){
			exit;
		}

		if($method_id != $dbOrder->order_payment_id)
			exit;
		$this->loadOrderData($dbOrder);

		$pbx_auth = JRequest::getVar('auth', '');
		$pbx_err = JRequest::getVar('err', '99999');
		$pbx_mt = JRequest::getInt('mt', 0);

		$history = new stdClass();
		$email = new stdClass();

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . $order_id . $this->url_itemid;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK', $url));

		$history->notified = 0;
		$history->amount = ($pbx_mt/100);
		$history->data =  ob_get_clean();

		$price_check = (int)($dbOrder->order_full_price * 100);
		if($pbx_mt != $price_check) {
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', 'Paybox') . JText::_('INVALID_AMOUNT');
			$email->body = str_replace('<br/>', "\r\n", JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER', 'Paybox', $history->amount, ($price_check/100) . $this->currency->currency_code)) . "\r\n\r\n" . $order_text;
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, $history, $email);
			exit;
		}

		$completed = ((int)$pbx_err == 0 && $pbx_err == '00000');

		if( !$completed ) {
			$order_status = $this->payment_params->invalid_status;
			$history->data .= "\n\n" . 'payment with code '.$pbx_auth;
			$payment_status = 'cancel';

			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'Paybox', $payment_status)).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'Paybox', $payment_status, $dbOrder->order_number);

			$this->modifyOrder($order_id, $order_status, $history, $email);
			exit;
		}

		$history->notified = 1;
		$order_status = $this->payment_params->verified_status;
		$payment_status = 'Accepted';

		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Paybox', $payment_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED', $statuses[$order_status])."\r\n\r\n".$order_text;
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'Paybox', $payment_status, $dbOrder->order_number);

		$this->modifyOrder($order_id, $order_status, $history, $email);
		exit;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'PAYBOX';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->ips = '195.101.99.76;194.2.122.158;195.25.7.166';
		$element->payment_params->force_card = 1;

		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}

	function onPaymentConfigurationSave(&$element) {
		parent::onPaymentConfigurationSave($element);

		if(empty($element->payment_id)) {
			$pluginClass = hikashop_get('class.payment');
			$status = $pluginClass->save($element);
			if(!$status)
				return true;
			$element->payment_id = $status;
		}

		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');
		$lang = JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'),0,2));

		$content = '<?php
	$_GET[\'option\']=\'com_hikashop\';
	$_GET[\'tmpl\']=\'component\';
	$_GET[\'ctrl\']=\'checkout\';
	$_GET[\'task\']=\'notify\';
	$_GET[\'notif_payment\']=\'paybox\';
	$_GET[\'format\']=\'html\';
	$_GET[\'lang\']=\''.$locale.'\';
	$_GET[\'notif_id\']=\''.$element->payment_id.'\';
	$_REQUEST[\'option\']=\'com_hikashop\';
	$_REQUEST[\'tmpl\']=\'component\';
	$_REQUEST[\'ctrl\']=\'checkout\';
	$_REQUEST[\'task\']=\'notify\';
	$_REQUEST[\'notif_payment\']=\'paybox\';
	$_REQUEST[\'format\']=\'html\';
	$_REQUEST[\'lang\']=\''.$locale.'\';
	$_REQUEST[\'notif_id\']=\''.$element->payment_id.'\';
	include(\'index.php\');
';
		JFile::write(JPATH_ROOT.DS.'paybox_'.$element->payment_id.'.php', $content);

		return true;
	}
}
