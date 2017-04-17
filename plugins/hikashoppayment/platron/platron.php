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

include dirname(__FILE__).DS.'PG_Signature.php';

class plgHikashoppaymentPlatron extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'RUB', 'EUR', 'USD'
	);

	var $url = 'https://www.platron.ru/payment.php';
	var $arrPaymentParams = array();
	var $multiple = true;
	var $name = 'platron';
	var $pluginConfig = array(
		'merchant_id' => array('MERCHANT_ID', 'input'),
		'secret_key' => array('SECRET_KEY', 'input'),
		'lifetime' => array('LIFETIME', 'input','0'),
		'testmode' => array('DEBUG', 'boolean','1'),
		'return_url' => array('RETURN_URL', 'input'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'canceled_status' => array('INVALID_STATUS', 'orderstatus'),
		'paid_status' => array('VERIFIED_STATUS', 'orderstatus'),
		'payment_system' => array('PAYMENT_SYSTEM', 'input'),
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order,$methods,$method_id);

		if (empty($this->payment_params->merchant_id) || empty($this->payment_params->secret_key)){
			$this->app->enqueueMessage('You have to configure a merchant account and its secret key for the Platron plugin payment first. Please check your payment method parameters on your website backend','error');
			return false;
		}

		$viewType='end';
		$strDescription = '';
		foreach($order->cart->products as $objItem){
			$strDescription .= $objItem->order_product_name;
			if($objItem->order_product_quantity > 1)
				$strDescription .= "*".$objItem->order_product_quantity;
			$strDescription .= "; ";
		}

		$server_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=platron&notif_id='.$method_id.'&tmpl=component'.$this->url_itemid;
		$failure_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$this->url_itemid;
		$success_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;

		$strCurrency = $this->currency->currency_code;
		if($strCurrency == 'RUB')
			$strCurrency = 'RUR';

		$arrFields = array(
			'pg_merchant_id'		=> $this->payment_params->merchant_id,
			'pg_order_id'			=> $order->order_id,
			'pg_currency'			=> $strCurrency,
			'pg_amount'				=> sprintf('%0.2f',$order->cart->full_total->prices[0]->price_value_with_tax),
			'pg_lifetime'			=> isset($this->payment_params->lifetime)?$this->payment_params->lifetime*60:0,
			'pg_testing_mode'		=> $this->payment_params->testmode,
			'pg_description'		=> $strDescription,
			'pg_user_ip'			=> $_SERVER['REMOTE_ADDR'],
			'pg_language'			=> (JFactory::getLanguage()->getTag() == 'ru-RU')?'ru':'en',
			'pg_check_url'			=> $server_url,
			'pg_result_url'			=> $server_url,
			'pg_success_url'		=> $success_url,
			'pg_failure_url'		=> $failure_url,
			'pg_request_method'		=> 'GET',
			'pg_salt'				=> rand(21,43433), // Параметры безопасности сообщения. Необходима генерация pg_salt и подписи сообщения.
		);

		if(!empty($order->cart->shipping_address->address_telephone)){
			preg_match_all("/\d/", $order->cart->shipping_address->address_telephone, $array);
			$strPhone = implode('',@$array[0]);
			$arrFields['pg_user_phone'] = $strPhone;
		}elseif(!empty($order->cart->billing_address->address_telephone)){
			preg_match_all("/\d/", $order->cart->billing_address->address_telephone, $array);
			$strPhone = implode('',@$array[0]);
			$arrFields['pg_user_phone'] = $strPhone;
		}

		if(!empty($order->cart->customer->email)){
			$arrFields['pg_user_email'] = $order->cart->customer->email;
			$arrFields['pg_user_contact_email'] = $order->cart->customer->email;
		}

		if(!empty($this->payment_params->payment_system))
			$arrFields['pg_payment_system'] = $this->payment_params->payment_system;

		$arrFields['pg_sig'] = PG_Signature::make('payment.php', $arrFields, $this->payment_params->secret_key);
		$this->arrPaymentParams = $arrFields;

		return $this->showPage($viewType);
	}

	function onPaymentNotification(&$statuses){
		$method_id = JRequest::getInt('notif_id', 0);
		$this->pluginParams($method_id);
		$this->payment_params =& $this->plugin_params;
		if(empty($this->payment_params))
			return false;

		if(!empty($_POST))
			$arrRequest = $_POST;
		else
			$arrRequest = $_GET;

		if(isset($arrRequest['hikashop_front_end_main'])) unset($arrRequest['hikashop_front_end_main']);
		if(isset($arrRequest['view'])) unset($arrRequest['view']);

		$thisScriptName = PG_Signature::getOurScriptName();

		if (empty($arrRequest['pg_sig']) || !PG_Signature::check($arrRequest['pg_sig'], $thisScriptName, $arrRequest, $this->payment_params->secret_key))
			die("Wrong signature");

		$dbOrder = $this->getOrder($arrRequest['pg_order_id']);

		if(!isset($arrRequest['pg_result'])){
			$bCheckResult = 0;
			if(empty($dbOrder) || $dbOrder->order_status != $this->payment_params->pending_status)
				$error_desc = "Товар не доступен. Либо заказа нет, либо его статус " . $statuses[$dbOrder->order_status];	
			elseif(sprintf('%0.2f',$arrRequest['pg_amount']) != sprintf('%0.2f',$dbOrder->order_full_price))
				$error_desc = "Неверная сумма";
			else
				$bCheckResult = 1;

			$arrResponse['pg_salt']              = $arrRequest['pg_salt']; // в ответе необходимо указывать тот же pg_salt, что и в запросе
			$arrResponse['pg_status']            = $bCheckResult ? 'ok' : 'error';
			$arrResponse['pg_error_description'] = $bCheckResult ?  ""  : $error_desc;
			$arrResponse['pg_sig']				 = PG_Signature::make($thisScriptName, $arrResponse, $this->payment_params->secret_key);

			$objResponse = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response/>');
			$objResponse->addChild('pg_salt', $arrResponse['pg_salt']);
			$objResponse->addChild('pg_status', $arrResponse['pg_status']);
			$objResponse->addChild('pg_error_description', $arrResponse['pg_error_description']);
			$objResponse->addChild('pg_sig', $arrResponse['pg_sig']);

		}
		else{
			$bResult = 0;
			if(empty($dbOrder) || 
					(($dbOrder->order_status != $this->payment_params->pending_status) &&
					!($dbOrder->order_status == $this->payment_params->paid_status && $arrRequest['pg_result'] == 1) && 
					!($dbOrder->order_status == $this->payment_params->canceled_status && $arrRequest['pg_result'] == 0)))

				$strResponseDescription = "Товар не доступен. Либо заказа нет, либо его статус " . $statuses[$dbOrder->order_status];		
			elseif(sprintf('%0.2f',$arrRequest['pg_amount']) != sprintf('%0.2f',$dbOrder->order_full_price))
				$strResponseDescription = "Неверная сумма";
			else {
				$history = new stdClass();
				$history->amount = $arrRequest['pg_amount'];
				$history->data = 'Platron transaction id '.$arrRequest['pg_payment_id'];

				$bResult = 1;
				$strResponseStatus = 'ok';
				$strResponseDescription = "Оплата принята";
				if ($arrRequest['pg_result'] == 1) {
					$history->notified = 1;
					$this->modifyOrder($arrRequest['pg_order_id'],$this->payment_params->paid_status,$history);
				}
				else{
					$history->notified = 0;
					$this->modifyOrder($arrRequest['pg_order_id'],$this->payment_params->canceled_status,$history);
				}
			}
			if(!$bResult)
				if($arrRequest['pg_can_reject'] == 1)
					$strResponseStatus = 'rejected';
				else
					$strResponseStatus = 'error';

			$objResponse = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response/>');
			$objResponse->addChild('pg_salt', $arrRequest['pg_salt']); // в ответе необходимо указывать тот же pg_salt, что и в запросе
			$objResponse->addChild('pg_status', $strResponseStatus);
			$objResponse->addChild('pg_description', $strResponseDescription);
			$objResponse->addChild('pg_sig', PG_Signature::makeXML($thisScriptName, $objResponse, $this->payment_params->secret_key));
		}

		header("Content-type: text/xml");
		echo $objResponse->asXML();
		die();

	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Platron';
		$element->payment_description='Более 20 доступных методов оплаты';
		$element->payment_images='VISA,Maestro,MasterCard';
		$element->payment_params->pending_status='created';
		$element->payment_params->canceled_status='cancelled';
		$element->payment_params->paid_status='confirmed';
	}
}
