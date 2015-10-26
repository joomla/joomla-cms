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
class plgHikashoppaymentNets extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'DKK','EUR','NOK','SEK','USD'
	);

	var $multiple = true;
	var $name = 'nets';
	var $pluginConfig = array(
		'merchant_id' => array('MERCHANT_ID', 'input'),
		'token' => array('Token', 'input'),
		'service_type' => array('Service type', 'list', array(
			'B' => 'HIKA_REDIRECT',
		)),
		'force_3dsecure' => array('Force 3DSecure', 'boolean', '0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function checkPaymentDisplay(&$method, &$order) {
		return $this->soapSupported();
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$viewType = 'end';
		$server_url = HIKASHOP_LIVE.'index.php';
		$return_url_p = 'option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&notif_id='.$method_id.'&order_id='.$order->order_id.'&lang='.$this->locale.$this->url_itemid;

		if(!$this->soapSupported()) {
			$this->app->enqueueMessage('SOAP is not supported');
			return false;
		}

		if( $this->payment_params->sandbox ) {
			$wsdl = 'https://test.epayment.nets.eu/netaxept.svc?wsdl';
		} else {
			$wsdl = 'https://epayment.nets.eu/netaxept.svc?wsdl';
		}

		try {
			$soap = new SoapClient($wsdl, array('trace' => true, 'exceptions' => true));
		} catch(Exception $e) { return false; }

		$amount = round($order->cart->full_total->prices[0]->price_value_with_tax, (int)$this->currency->currency_locale['int_frac_digits']);
		$amount *= pow(10, (int)$this->currency->currency_locale['int_frac_digits']);

		$tax = round($order->cart->full_total->prices[0]->price_value_with_tax - $order->cart->full_total->prices[0]->price_value, (int)$this->currency->currency_locale['int_frac_digits']);
		$tax *= pow(10, (int)$this->currency->currency_locale['int_frac_digits']);

		$languages = array(
			'no' => 'no_NO', // (Norwegian)
			'sv' => 'sv_SE', // (Swedish)
			'da' => 'da_DK', // (Denmark)
			'de' => 'de_DE', // (German)
			'fi' => 'fi_FI', // (Finnish)
			'ru' => 'ru_RU', // (Russian)
			'en' => 'en_GB'  // (English
		);
		if(!isset($languages[$this->locale])) {
			$lng = $languages['en'];
		} else {
			$lng = $languages[$this->locale];
		}

		$address = array(
			'street1' => '',
			'street2' => '',
			'firstname' => '',
			'lastname' => '',
			'postcode' => '',
			'city' => '',
			'country' => ''
		);
		if(!empty($order->cart->billing_address->address_lastname))
			$address['lastname'] = substr( $order->cart->billing_address->address_lastname, 0, 64);
		if(!empty($order->cart->billing_address->address_street1)) {
			$address['street1'] = $order->cart->billing_address->address_street1;
			if(strlen($address['street1']) > 64) {
				$address['street2'] = substr($address['street1'], 64, 64);
				$address['street1'] = substr($address['street1'], 0, 64);
			}
		}
		if(!empty($order->cart->billing_address->address_street2))
			$address['street2'] = substr( $order->cart->billing_address->address_street2, 0, 64);
		if(!empty($order->cart->billing_address->address_post_code))
			$address['postcode'] = substr( $order->cart->billing_address->address_post_code, 0, 64);
		if(!empty($order->cart->billing_address->address_city))
			$address['city'] = substr( $order->cart->billing_address->address_city, 0, 64);
		if(!empty($order->cart->billing_address->address_country->zone_name_english))
			$address['country'] = substr( $order->cart->billing_address->address_country->zone_name_english, 0, 64);

		if($this->payment_params->force_3dsecure){
			$force_3dsecure = 'true';
		}else{
			$force_3dsecure = 'false';
		}

		$parameters = array(
			'merchantId' => $this->payment_params->merchant_id,
			'token' => $this->payment_params->token,
			'request' => array(
				'ServiceType' => $this->payment_params->service_type,
				'Order' => array(
					'OrderNumber' => $order->order_id,
					'CurrencyCode' => $this->currency->currency_code,
					'Amount' => $amount,
					'Force3DSecure' => $force_3dsecure
				),
				'Environment' => array(
					'WebServicePlatform' => 'PHP5'
				),
				'Terminal' => array(
					'Language' => $lng,
					'RedirectUrl' => $server_url.'?'.$return_url_p,
					'Vat' => $tax
				),
				'Customer' => array(
					'Email' => $this->user->user_email,
					'FirstName' => $address['firstname'],
					'LastName' => $address['lastname'],
					'Address1' => $address['street1'],
					'Address2' => $address['street2'],
					'Postcode' => $address['postcode'],
					'Town' => $address['city'],
					'Country' => $address['country']
				)
			)
		);


		$transactionId = 0;
		try {
			$ret = $soap->__call('Register', array(
				'parameters' => $parameters
			));
			if(!empty($ret->RegisterResult) && !empty($ret->RegisterResult->TransactionId)) {
				$transactionId = $ret->RegisterResult->TransactionId;
			}
			if( $this->payment_params->debug ) {
				var_dump($ret->RegisterResult);
			}
		} catch(Exception $e) {
		}

		if(empty($transactionId)) {
			$this->app->enqueueMessage('Error during NETS call');
			return false;
		}

		if( $this->payment_params->sandbox ) {
			$this->redirect_url = 'https://test.epayment.nets.eu/Terminal/default.aspx?merchantId='.$this->payment_params->merchant_id.'&transactionId='.$transactionId;
		} else {
			$this->redirect_url = 'https://epayment.nets.eu/Terminal/default.aspx?merchantId='.$this->payment_params->merchant_id.'&transactionId='.$transactionId;
		}

		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses) {
		$method_id = JRequest::getInt('notif_id', 0);
		$this->pluginParams($method_id);
		$this->payment_params =& $this->plugin_params;
		if(empty($this->payment_params))
			return false;

		global $Itemid;
		$this->url_itemid = empty($Itemid) ? '' : '&Itemid=' . $Itemid;

		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order'.$this->url_itemid;

		if(!$this->soapSupported()) {
			$this->app->enqueueMessage('SOAP is not supported');
			$this->app->redirect($cancel_url);
			return false;
		}

		$transactionId = null;
		if(!empty($_GET['transactionId']))
			$transactionId = $_GET['transactionId'];

		if(!empty($_POST['transactionId']))
			$transactionId = $_POST['transactionId'];

		$order_id = JRequest::getInt('order_id', 0);
		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder)){
			$this->app->enqueueMessage('Could not load any order for your notification '.$order_id);
			$this->app->redirect($cancel_url);
			return false;
		}
		if($method_id != $dbOrder->order_payment_id)
			return false;
		$this->loadOrderData($dbOrder);

		if( $this->payment_params->sandbox ) {
			$wsdl = 'https://test.epayment.nets.eu/netaxept.svc?wsdl';
		} else {
			$wsdl = 'https://epayment.nets.eu/netaxept.svc?wsdl';
		}

		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order_id.$this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id.$this->url_itemid;
		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number, HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK', $url));

		if($dbOrder->order_status == $this->payment_params->verified_status) {
			$this->app->redirect($return_url);
			return true;
		}

		$history = new stdClass();
		$email = new stdClass();

		try {
			$soap = new SoapClient($wsdl, array('trace' => true, 'exceptions' => true));
		} catch(Exception $e) { return false; }

		$parameters = array(
			'merchantId' => $this->payment_params->merchant_id,
			'token' => $this->payment_params->token,
			'request' => array(
				'TransactionId' => $transactionId
			)
		);

		$ret = null;
		try {
			$ret = $soap->__call('Query', array(
				'parameters' => $parameters
			));
		} catch(Exception $e) {}

		if(empty($ret)) {
			$this->app->enqueueMessage('Could not load any order for your notification '.$order_id);
			$this->app->redirect($cancel_url);
			return false;
		}
		$query = $ret->QueryResult;

		$amount = round($dbOrder->order_full_price, (int)$this->currency->currency_locale['int_frac_digits']);
		$amount *= pow(10, (int)$this->currency->currency_locale['int_frac_digits']);

		$completed = false;

		$parameters = array(
			'merchantId' => $this->payment_params->merchant_id,
			'token' => $this->payment_params->token,
			'request' => array(
				'Operation' => 'SALE',
				'TransactionId' => $transactionId,
				'TransactionAmount' => $amount
			)
		);

		$authorizationid = '';
		try {
			$ret = $soap->__call('Process', array(
				'parameters' => $parameters
			));
			if( $ret->ProcessResult->ResponseCode == 'OK' )
				$completed = true;

			if(!empty($ret->ProcessResult->AuthorizationId))
				$authorizationid = $ret->ProcessResult->AuthorizationId;
		} catch(Exception $e) {
			if(isset($e->detail) && isset($e->detail->BBSException) && isset($e->detail->BBSException->Result->ResponseText) && $e->detail->BBSException->Result->ResponseText == 'Transaction already processed') {
				$completed = true;
				$authorizationid = $query->Summary->AuthorizationId;
			}
		}

		if( !$completed ) {
			$order_status = $this->payment_params->invalid_status;
			$history->data .= "\n\n" . 'TransactionId: '.$transactionId;

			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Nets',$order_status)).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Nets',$order_status,$dbOrder->order_number);

			$this->modifyOrder($order_id, $order_status, $history,$email);

			$this->app->enqueueMessage('Transaction Failed');
			$this->app->redirect($cancel_url);
			return false;
		}

		$order_status = $this->payment_params->verified_status;
		$vars['payment_status'] = 'Accepted';
		$history->data .= "\n\n" . 'TransactionId: '.$transactionId."\r\n".'AuthorizationId: '.$authorizationid;
		$history->notified = 1;
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Nets', $vars['payment_status'], $dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'Nets', $vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order_status])."\r\n\r\n".$order_text;

		$this->modifyOrder($order_id,$order_status,$history,$email);

		$this->app->redirect($return_url);
		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'NETS';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA';

		$element->payment_params->merchant_id = '';
		$element->payment_params->token = '';
		$element->payment_params->service_type = 'B';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}

	function soapSupported() {
		static $soapSupported = null;
		if($soapSupported === null) {
			$soapSupported = false;
			if(extension_loaded('soap') || class_exists('SoapClient'))
				$soapSupported = true;
		}
		return $soapSupported;
	}
}
