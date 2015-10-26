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
class plgHikashoppaymentPostfinance extends hikashopPaymentPlugin {
	var $accepted_currencies = array(
		'CHF', 'EUR', 'GBP', 'USD', 'DZD', 'AUD', 'CAD', 'HRK', 'CZK', 'DKK', 'EGP', 'HKD', 'HUF', 'INR', 'IDR', 'ILS', 'JPY', 'KES', 'LVL',
		'LTL', 'MYR', 'MUR', 'MAD', 'NAD', 'NZD', 'NOK', 'PHP', 'PLN', 'RON', 'SGD', 'ZAR', 'LKR', 'SEK', 'TWD', 'THB', 'TND', 'TRY', 'VND'
	);
	var $multiple = true;
	var $name = 'postfinance';
	var $pluginConfig = array(
		'returnurl' => array('RETURN_URL', 'html', ''),
		'shop_ID' => array('ATOS_MERCHANT_ID', 'input'),
		'sha_in_phrase' => array('SHA-IN_Pass_phrase', 'input'),
		'sha_out_phrase' => array('SHA-OUT_Pass_phrase', 'input'),
		'debug' => array('DEBUG', 'boolean','0'),
		'address_type' => array('PAYPAL_ADDRESS_TYPE', 'address'),
		'url' => array('URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$home_url = HIKASHOP_LIVE.'index.php';
		$notify_url = $home_url.'?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=postfinance&tmpl=component&lang='.$this->locale.$this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;

		$languages = array(
			'en' => 'en_US',
			'fr' => 'fr_FR',
			'it' => 'it_IT',
			'de' => 'de_DE',
			'nl' => 'nl_NL',
		);

		if(isset($languages[$this->locale])) {
			$lng = $languages[$this->locale];
		} else {
			$lng = $languages['en'];
		}

		$vars = array(
			"PSPID" => $this->payment_params->shop_ID,
			"LANGUAGE" => $lng,
			"ORDERID" => $order->order_id,
			"AMOUNT" => round($order->order_full_price, 2)*100,
			"CURRENCY" => $this->currency->currency_code,
			"ACCEPTURL" => $notify_url,
			"CANCELURL" => $notify_url,
			"DECLINEURL" => $notify_url,
			"EXCEPTIONURL" => $notify_url,
			"HOMEURL" => $home_url,
			"CATALOGURL" => $home_url,
		);

		$order_address = null;
		if(!empty($order->cart->billing_address) && $this->payment_params->address_type == 'billing') {
			$order_address = $order->cart->billing_address;
		}
		if(!empty($order->cart->shipping_address) && $this->payment_params->address_type == 'shipping') {
			$order_address = $order->cart->shipping_address;
		}
		if(!empty($order_address)) {
			$order_address_1 = '';
			$order_address_2 = '';

			if(!empty($order_address->address_street2)) {
				$billing_address2 = substr($order_address->address_street2, 0, 99);
			}
			if(!empty($order_address->address_street)) {
				if(strlen($order_address->address_street) > 100) {
					$billing_address1 = substr($order_address->address_street, 0, 99);
					if(empty($billing_address2))
						$billing_address2 = substr($order_address->address_street, 99, 199);
				} else {
					$billing_address1 = $order_address->address_street;
				}
			}
			if(!empty($billing_address1))
				$vars["OWNERADDRESS"] = $billing_address1;
			if(!empty($billing_address2))
				$vars["OWNERADDRESS"] .= $billing_address2;
			if(!empty($order_address->address_post_code))
				$vars["OWNERZIP"] = $order_address->address_post_code;
			if(!empty($order_address->address_city))
				$vars["OWNERCTY"] = $order_address->address_city;
			if(!empty($this->user->user_email))
				$vars["EMAIL"] = $this->user->user_email;
			if(!empty($order_address->address_telephone))
				$vars["OWNERTELNO"] = $order_address->address_telephone;
		}

		ksort($vars);
		$txtSha_tosecure = '';
		foreach($vars as $key => $var) {
			$txtSha_tosecure .= strtoupper($key) . '=' . $var . $this->payment_params->sha_in_phrase;
		}
		$vars["SHASIGN"] = strtoupper(sha1($txtSha_tosecure));

		$this->vars = $vars;
		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses) {
		$vars = array();
		$filter = JFilterInput::getInstance();
		foreach($_REQUEST as $key => $value) {
			$key = $filter->clean($key);
			if(preg_match("#^[0-9a-z_-]{1,30}$#i", $key) && !preg_match("#^cmd$#i", $key)) {
				$vars[$key] = JRequest::getString($key);
			}
		}

		$order_id = (int)@$vars['orderID'];
		$dbOrder = $this->getOrder($order_id);

		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;

		$this->loadOrderData($dbOrder);
		if($this->payment_params->debug) {
			$this->writeToLog(
				print_r($dbOrder, true)."\r\n\r\n".
				print_r($vars, true)
			);
		}

		$result = array();
		$acceptedKeys = array(
			'AAVADDRESS','AAVCHECK','AAVZIP','ACCEPTANCE','ALIAS','AMOUNT','BIN','BRAND','CARDNO','CCCTY','CN','COMPLUS','CREATION_STATUS','CURRENCY','CVCCHECK','DCC_COMMPERCENTAGE','DCC_CONVAMOUNT',
			'DCC_CONVCCY','DCC_EXCHRATE','DCC_EXCHRATESOURCE','DCC_EXCHRATETS','DCC_INDICATOR','DCC_MARGINPERCENTAGE','DCC_VALIDHOURS','DIGESTCARDNO','ECI','ED','ENCCARDNO','IP','IPCTY',
			'NBREMAILUSAGE','NBRIPUSAGE','NBRIPUSAGE_ALLTX','NBRUSAGE','NCERROR','ORDERID','PAYID','PM','SCO_CATEGORY','SCORING','STATUS','SUBBRAND','SUBSCRIPTION_ID','TRXDATE','VC'
		);
		foreach($_REQUEST as $key => $value) {
			if($value != '' && in_array(strtoupper($key), $acceptedKeys)) {
				$result[strtoupper($key)] = $value;
			} else if($key == 'SHASIGN') {
				$shasign = $value;
			}
		}

		if($this->payment_params->debug) {
			$this->writeToLog('PostFinance $_REQUEST :'."\r\n".print_r($_REQUEST, true));
		}

		ksort($result);
		$txtSha_tosecure ='';
		foreach($result as $key => $var) {
			$txtSha_tosecure .= $key . '=' . $var . $this->payment_params->sha_out_phrase;
		}
		$txtSha = strtoupper(sha1($txtSha_tosecure));

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . $order_id;
		$order_text = "\r\n" . JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE);
		$order_text .= "\r\n" . str_replace('<br/>', "\r\n", JText::sprintf('ACCESS_ORDER_WITH_LINK', $url));

		if($this->payment_params->debug) {
			$this->writeToLog(
				'result : ' . "\r\n" . print_r($result, true) . "\r\n" .
				'MYSHA : ' . $txtSha . "\r\n" .
				'THEIRCHA : '.$shasign . "\r\n" .
				'sha_out : '.$this->payment_params->sha_out_phrase . "\r\n"
			);
		}

		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=' . $order_id . '&lang=' . $this->locale . $this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=' . $order_id . '&lang=' . $this->locale . $this->url_itemid;
		if(($txtSha == $shasign) && in_array((int)$result['STATUS'], array(9, 91))) {
			$history = new stdClass();
			$email = new stdClass();
			$history->notified = 1;
			$history->amount = $result['AMOUNT'];
			$history->data = ob_get_clean();

			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Postfinance',$result['STATUS'],$dbOrder->order_number);
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Postfinance',$result['STATUS'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$this->payment_params->verified_status)."\r\n\r\n".$order_text;
			$email->body = $body;

			if($dbOrder->order_status != $this->payment_params->verified_status)
				$this->modifyOrder($order_id, $this->payment_params->verified_status, $history, $email);

			$this->app->redirect($return_url);
			return true;
		}

		if($txtSha == $shasign && (int)$result['STATUS'] == 5) {
			$this->app->enqueueMessage(JText::_('POSTFINANCE_AUTHORIZED_PAYMENT'));
			$this->app->redirect($return_url);
			return true;
		}

		$email = new stdClass();
		$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', $this->name) . ' invalid response';

		if($txtSha !== $shasign) {
			$email->body = JText::_("Hello,\r\n A Postfinance notification was refused because the signature was invalid")."\r\n\r\n".$order_text;
			if($element->payment_params->debug) {
				$this->writeToLog('invalid signature (status: ' . (int)$result['STATUS'] . ')');
			}
		} else {
			$email->body = JText::_("Hello,\r\n A Postfinance notification was refused because the response from the Post finance server was invalid")."\r\n\r\n".$order_text;
			if($element->payment_params->debug) {
				$this->writeToLog('invalid response: ' . (int)$result['STATUS']);
			}
		}

		if($dbOrder->order_status != $this->payment_params->invalid_status)
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, false, $email);

		$this->app->enqueueMessage('Transaction Failed with the status number : '.$result['STATUS']);
		$this->app->redirect($cancel_url);
		return false;
	}

	function onPaymentConfiguration(&$element) {
		$this->pluginConfig['returnurl'][2] = HIKASHOP_LIVE.'index.php?option=com_hikashop&amp;ctrl=checkout&amp;task=notify&amp;notif_payment=postfinance&amp;tmpl=component';
		parent::onPaymentConfiguration($element);
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'Postfinance';
		$element->payment_description = 'You can pay by credit card or Postfinance using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,Postfinance';

		$element->payment_params->url = 'https://e-payment.postfinance.ch/ncol/test/orderstandard.asp';
		$element->payment_params->notification = 1;
		$element->payment_params->shop_ID = '';
		$element->payment_params->language = 'en_US';
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}
}
