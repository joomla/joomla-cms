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
class plgHikashoppaymentOgone extends hikashopPaymentPlugin
{
	var $debugData = array();
	var $multiple = true;
	var $name = 'ogone';
	var $features = array(
		'authorize_capture' => true,
		'recurring' => false,
		'refund' => true
	);
	var $pluginConfig = array(
		'pspid' => array('PSPID', 'input'),
		'shain_passphrase' => array('SHA-IN Passphrase', 'input'),
		'shaout_passphrase' => array('SHA-OUT Passphrase', 'input'),
		'hash_method' => array('HASH_METHOD', 'list', array(
			'sha1' => 'SHA1',
			'sha256' => 'SHA256',
			'sha512' => 'SHA512'
		)),
		'environnement' => array('ENVIRONNEMENT', 'list', array(
			'production' => 'HIKA_PRODUCTION',
			'test' => 'HIKA_TEST'
		)),
		'status_url' => array('After payment URL', 'html', ''),
		'debug' => array('DEBUG', 'boolean'),

		'authorize_capture' => array('Authorize/Capture', 'radio', array(
			'capture' => 'Capture',
			'authorize' => 'Authorize (manual capture)',
			'dynamic' => 'Authorize (dynamic capture)',
		)),
		'info_user_pwd' => array('User &amp; Password', 'html', 'Only if you use the dynamic capture mode'),
		'userid' => array('User ID', 'input'),
		'pwd' => array('HIKA_PASSWORD', 'input'),
		'authorization_days' => array('Days of authorization', 'input', '12'),

		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),

		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'authorized_status' => array('AUTHORIZED_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onPaymentConfiguration(&$element) {
		parent::onPaymentConfiguration($element);
		$lang = JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'),0,2));

		if(empty($element->payment_params->pspid)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED','Ogone','PSPID','Ogone','http://www.ogone.com/en/sitecore/Content/COM/Web/Solutions/Payment%20Processing/eCommerce.aspx'));
		}

		$this->pluginConfig['status_url'][2] = htmlentities(HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=ogone&tmpl=component&lang='.strtolower($locale));

		if( !function_exists('hash') && !function_exists('sha1') )
			$this->pluginConfig['hash_method'][2]['sha1'] = JText::_('SHA1').' '.JText::_('not present');

		if( !function_exists('hash') ) {
			$this->pluginConfig['hash_method'][2]['sha256'] = JText::_('SHA256').' '.JText::_('not present');
			$this->pluginConfig['hash_method'][2]['sha512'] = JText::_('SHA512').' '.JText::_('not present');
		}
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'Ogone';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,American_Express';

		$element->payment_params->notification = 1;
		$element->payment_params->details = 0;
		$element->payment_params->invalid_status = 'created';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->authorized_status = 'confirmed';
		$element->payment_params->verified_status = 'confirmed';

		$element->payment_params->authorize_capture = 'capture';
	}

	function checkPaymentDisplay(&$method, &$order) {
		$method->features['authorize_capture'] = (!empty($method->payment_params->authorize_capture) && $method->payment_params->authorize_capture == 'dynamic') && (!empty($method->payment_params->userid) && !empty($method->payment_params->pwd));
		return true;
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);
		$lang = JFactory::getLanguage();

		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=ogone&tmpl=component&lang=' . $this->locale . $this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=' . $order->order_id . $this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=' . $order->order_id . $this->url_itemid;

		$language = str_replace('-', '_', $lang->get('tag'));
		$language_codes = array(
			'ar_AR', 'cs_CZ', 'zh_CN', 'da_DK', 'nl_BE', 'nl_NL', 'en_GB', 'en_US', 'fr_FR', 'de_DE', 'el_GR',
			'hu_HU', 'it_IT', 'ja_JP', 'no_NO', 'pl_PL', 'pt_PT', 'ru_RU', 'sk_SK', 'es_ES', 'se_SE', 'tr_TR',
		);

		if(!in_array($language, $language_codes))
			$language = 'en_US';

		$vars = array(
			'PSPID' => $this->payment_params->pspid,
			'orderID' => @$order->order_id,
			'amount' => 100 * round(@$order->cart->full_total->prices[0]->price_value_with_tax, 2),
			'currency' => $this->currency->currency_code,
			'language' => $language,
			'EMAIL' => $this->user->user_email,
			'accepturl' => $return_url,
			'declineurl' => $cancel_url,
			'exceptionurl' => $cancel_url,
			'cancelurl' => $cancel_url,
		);

		$address = $this->app->getUserState(HIKASHOP_COMPONENT . '.billing_address');
		if(!empty($address)) {
			$vars['owneraddress'] = @$order->cart->billing_address->address_street;
			$vars['ownerZIP'] = substr(@$order->cart->billing_address->address_post_code, 0, 10);
			$vars['ownertown'] = @$order->cart->billing_address->address_city;
			$vars['ownercty'] = @$order->cart->billing_address->address_country->zone_code_2;
			$vars['CN'] = trim(@$order->cart->billing_address->address_firstname . ' ' . @$order->cart->billing_address->address_lastname);
			$vars['ownertelno'] = @$order->cart->billing_address->address_telephone;
		}

		if(!empty($order->order_payment_params) && is_string($order->order_payment_params))
			$order->order_payment_params = unserialize($order->order_payment_params);
		if(isset($order->order_payment_params->need_authorization)) {
			$vars['OPERATION'] = 'RES';
		}

		$vars['SHASign'] = $this->generateHash($vars, $this->payment_params->shain_passphrase, $this->payment_params->hash_method);

		if($this->payment_params->environnement == 'test')
			$this->payment_params->url = 'https://secure.ogone.com/ncol/test/orderstandard_utf8.asp';
		else
			$this->payment_params->url = 'https://secure.ogone.com/ncol/prod/orderstandard_utf8.asp';

		$this->vars = $vars;
		return $this->showPage('end');
	}

	function generateHash($vars, $passphrase, $hash_method, $type = 'in') {
		uksort($vars, 'strnatcasecmp');
		$key = '';

		$outKeys = array(
			'AAVADDRESS','AAVCHECK','AAVZIP','ACCEPTANCE','ALIAS','AMOUNT','BIN','BRAND','CARDNO','CCCTY','CN','COMPLUS','CREATION_STATUS',
			'CURRENCY','CVCCHECK','DCC_COMMPERCENTAGE','DCC_CONVAMOUNT','DCC_CONVCCY','DCC_EXCHRATE','DCC_EXCHRATESOURCE','DCC_EXCHRATETS',
			'DCC_INDICATOR','DCC_MARGINPERCENTAGE','DCC_VALIDHOURS','DIGESTCARDNO','ECI','ED','ENCCARDNO','IP','IPCTY','NBREMAILUSAGE','NBRIPUSAGE',
			'NBRIPUSAGE_ALLTX','NBRUSAGE','NCERROR','ORDERID','PAYID','PM','SCO_CATEGORY','SCORING','STATUS','SUBBRAND','SUBSCRIPTION_ID','TRXDATE','VC'
		);

		foreach($vars as $k => $v) {
			if(strlen($v) == 0)
				continue;
			if($type == 'in' || ($type == 'out' && in_array(strtoupper($k), $outKeys))) {
				$key .= strtoupper($k) . '=' . $v . $passphrase;
			}
		}
		return strtoupper(hash($hash_method, $key));
	}

	function onPaymentNotification(&$statuses) {
		$vars = array();
		foreach($_REQUEST as $k => $v) {
			$vars[strtoupper($k)] = $v;
		}

		$order_id = (int)@$vars['ORDERID'];
		$order_status = '';

		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;

		$this->loadOrderData($dbOrder);

		if($this->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
			echo print_r($dbOrder,true)."\n\n\n";

			$this->writeToLog(print_r($vars,true));
		}

		if(empty($dbOrder)) {
			echo 'Could not load any order for your notification ' . @$vars['ORDERID'];
			return false;
		}

		$vars['GENERATEDHASH'] = $this->generateHash($_REQUEST, $this->payment_params->shaout_passphrase, $this->payment_params->hash_method, 'out');

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$history = new stdClass();
		$email = new stdClass();

		$payment_status = (int)$vars['STATUS'];

		$invalid = false;
		$waiting = false;
		switch(substr($vars['STATUS'],0,1)){
			case '0':
			case '1':
			case '2':
			case '4':
			case '6':
			case '7':
			case '8':
				$invalid = true;
				break;
			case '5':
			case '9':
				$invalid = in_array($vars['STATUS'], array('52','92','93'));

				$waiting = in_array($vars['STATUS'], array('51','55','59','99','91'));

				break;
		}

		if($invalid || $vars['GENERATEDHASH'] != $vars['SHASIGN'] || empty($vars['SHASIGN'])) {
			if($vars['GENERATEDHASH'] != $vars['SHASIGN']) {
				$order_text = ' The Hashs didn\'t match. Received: '.$vars['SHASIGN']. ' and generated: '.$vars['GENERATEDHASH']."\n\n\n"."\n\n\n".ob_get_clean()."\n\n\n"."\n\n\n".$order_text;
				ob_start();
			}
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Ogone') . 'invalid transaction';
			$email->body = JText::sprintf("Hello,\r\n An Ogone payment notification was not validated. The status code was :" . $vars['STATUS']) . $order_text;

			$this->modifyOrder($order_id, $this->payment_params->invalid_status, false, $email);

			if($this->payment_params->debug) {
				echo 'invalid transaction' . "\n\n\n";
				$this->writeToLog('invalid transaction: '.$vars['STATUS']."\r\n".$order_text);
			}

			$dbg = ob_get_clean();
			ob_start();
			$this->showPage('thankyou');
			$msg = ob_get_clean();
			ob_start();
			echo $dbg;
			return $msg;
		}

		$need_authorization = (!empty($this->payment_params->authorize_capture) && $this->payment_params->authorize_capture == 'dynamic') && (!empty($this->payment_params->user_id) && !empty($this->payment_params->pwd));

		$payment_params = @$dbOrder->order_payment_params;
		if(!empty($payment_params) && is_string($payment_params))
			$payment_params = unserialize($payment_params);

		$payment_params->payment_value = $dbOrder->order_full_price;

		if((!empty($this->payment_params->authorize_capture) && $this->payment_params->authorize_capture != 'capture') || isset($payment_params->need_authorization)) {
			$this->payment_params->authorization_days = (int)@$this->payment_params->authorization_days;
			if(empty($this->payment_params->authorization_days))
				$this->payment_params->authorization_days = 12;

			$payment_params->payment_authorized = (int)$vars['PAYID'];
			$payment_params->payment_date = $vars['TRXDATE'];
			$payment_params->payment_auth_renew = hikashop_getDate(time() + $this->payment_params->authorization_days*86400, '%Y/%m/%d');
		}

		$history->notified = 0;
		$history->data = ob_get_clean();

	 	if(!$waiting) {
	 		$order_status = $this->payment_params->verified_status;
			if((!empty($this->payment_params->authorize_capture) && $this->payment_params->authorize_capture != 'capture'))
				$order_status = $this->payment_params->authorized_status;

	 		if($dbOrder->order_status == $order_status) {
				$dbg = ob_get_clean();
				ob_start();
				$this->showPage('thankyou');
				$msg = ob_get_clean();
				ob_start();
				echo $dbg;
				return $msg;
			}
	 	} else {
	 		$order_status = $this->payment_params->pending_status;
	 	}

	 	$config =& hikashop_config();
		if($config->get('order_confirmed_status', 'confirmed') == $order_status)
			$history->notified = 1;

	 	$mail_status = $statuses[$order->order_status];
	 	$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'Ogone', $vars['STATUS'], $dbOrder->order_number);
		$email->body = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'Ogone', $vars['STATUS'])) . ' ' . JText::sprintf('ORDER_STATUS_CHANGED', $mail_status) . "\r\n\r\n" . $order_text;

		$this->modifyOrder($order_id, $order_status, $history, $email, $payment_params);


		$dbg = ob_get_clean();
		ob_start();
		$this->showPage('thankyou');
		$msg = ob_get_clean();
		ob_start();
		echo $dbg;
		return $msg;
	}

	public function onHikashopCronTrigger(&$messages) {
		if(!$this->cronCheck())
			return;
		$this->renewalOrdersAuthorizations($messages);
	}

	function onOrderPaymentCapture(&$order, $total) {
		$payid = $this->getPaymentAuthorization($order);
		if(empty($payid))
			return false;

		$vars = array(
			'PAYID' => $payid,
			'AMOUNT' => 100 * round($total, 2),
			'OPERATION' => 'SAS', // or 'SAL',
		);

		$ret = $this->callOgoneDirect($vars);

		if($ret !== false) {
			$s = 0;
			if(isset($ret['status']))
				$s = (int)$ret['status'];
			if($s == 9 || $s == 91 || $s == 99)
				return true;
			if($s == 92 || $s == 95)
				return -1;
		}

		return false;
	}

	function onOrderAuthorizationCancel(&$order) {
		$payid = $this->getPaymentAuthorization($order);
		if(empty($payid))
			return false;

		$vars = array(
			'PAYID' => $payid,
			'OPERATION' => 'DES',
		);

		$ret = $this->callOgoneDirect($vars);

		if($ret !== false) {
			$s = 0;
			if(isset($ret['status']))
				$s = (int)$ret['status'];
			if($s == 6 || $s == 61)
				return true;
			if($s == 62)
				return -1;
		}
		return false;
	}

	function onOrderAuthorizationRenew(&$order) {
		$payid = $this->getPaymentAuthorization($order);
		if(empty($payid))
			return false;

		$vars = array(
			'PAYID' => $payid,
			'OPERATION' => 'REN',
		);

		$ret = $this->callOgoneDirect($vars);

		if($ret !== false) {
			$s = 0;
			if(isset($ret['status']))
				$s = (int)$ret['status'];
			if($s == 5 || $s == 51)
				return true;
			if($s == 52)
				return -1;

			unset($order->order_payment_params->payment_auth_renew);
		}
		return false;
	}

	function onOrderPaymentRefund(&$order, $total) {
		$payid = $this->getPaymentAuthorization($order);
		if(empty($payid))
			return false;

		$vars = array(
			'PAYID' => $payid,
			'OPERATION' => 'RFD',
			'AMOUNT' => 100 * round($total, 2),
		);

		$ret = $this->callOgoneDirect($vars);
		if($ret !== false) {
			$s = 0;
			if(isset($ret['status']))
				$s = (int)$ret['status'];
			if($s == 8 || $s == 81)
				return true;
			if($s == 82 || $s == 85)
				return -1;
		}
		return false;
	}

	function getPaymentAuthorization($order) {
		$payment_id = (int)@$order->old->order_payment_id;
		if(!empty($order->order_payment_id))
			$payment_id = (int)$order->order_payment_id;

		if(empty($this->plugin_data->payment_id) || (int)$this->plugin_data->payment_id != $payment_id) {
			$this->pluginParams($payment_id);
			$this->payment_params =& $this->plugin_params;
		}

		$payid = (int)@$order->order_payment_params->payment_authorized;
		return $payid;
	}

	function callOgoneDirect($vars) {
		if(empty($this->payment_params->environnement))
			return false;

		$url = 'https://secure.ogone.com/ncol/prod/maintenancedirect.asp';
		if($this->payment_params->environnement == 'test')
			$url = 'https://secure.ogone.com/ncol/test/maintenancedirect.asp';

		if(empty($vars['PSPID']))
			$vars['PSPID'] = $this->payment_params->pspid;
		if(empty($vars['USERID']))
			$vars['USERID'] = $this->payment_params->userid;
		if(empty($vars['PSWD']))
			$vars['PSWD'] = $this->payment_params->pwd;

		$postdata = array();
		foreach($vars as $k => $v) {
			$postdata[] = urlencode($k).'='.urlencode($v);
		}
		$postdata = implode('&', $postdata);

		$session = curl_init($url);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($session, CURLOPT_VERBOSE, 1);
		curl_setopt($session, CURLOPT_POST, 1);
		curl_setopt($session, CURLOPT_HEADER, 0);
		curl_setopt($session, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($session, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_TIMEOUT, 120);

		$ret = curl_exec($session);
		$error = curl_errno($session);
		$err_msg = curl_error($session);
		curl_close($session);

		if(!empty($ret)) {
			$ret = str_replace('<'.'?xml version="1.0"?'.'>', '', $ret);
			$matches = array();
			if(preg_match_all('#([A-Z]+)="(.*)"#iU', $ret, $matches)) {
				$ret = array();
				foreach($matches[1] as $k => $m) {
					$ret[ strtolower($m) ] = $matches[2][$k];
				}
			}
		}

		return $ret;
	}
}
