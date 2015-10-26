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

defined('_JEXEC') or die('Restricted access');

?><?php
class plgHikashoppaymentSofort extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'EUR','PLN','GBP','CHF'
	);

	var $multiple = true;
	var $name = 'sofort';
	var $pluginConfig = array(
		'user_id' => array('Sofort User Id', 'input'),
		'project_id' => array('Sofort Project Id', 'input'),
		'token' => array('Sofort API key', 'input'),
		'debug' => array('DEBUG', 'boolean','0'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
	);

	function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		require_once dirname(__FILE__).'/library/sofortLib.php';

		$viewType = 'end';
		if (empty ($this->payment_params->return_url)){
			$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id . $this->url_itemid;
		} else {
			$return_url = $this->payment_params->return_url;
		}
		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&lang=nl';
		if (empty ($this->payment_params->cancel_url)){
			$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order';
		} else {
			$cancel_url = $this->payment_params->cancel_url;
		}

		$configkey = $this->payment_params->user_id.':'.$this->payment_params->project_id.':'.$this->payment_params->token;

		$amount = round($order->cart->full_total->prices[0]->price_value_with_tax, (int)$this->currency->currency_locale['int_frac_digits']);
		$order_text = "\r\n".JText::sprintf('betaling Feeen en ridders: order ',$order->order_id);

		$transactionId = 0;
		$Sofort = new SofortLib_Multipay($configkey);
		$Sofort->setSofortueberweisung();
		$Sofort->setAmount($amount,$this->currency->currency_code);
		$Sofort->setReason($order_text);
		$Sofort->addUserVariable($order->order_id);
		$Sofort->setSofortrechnungOrderId($order->order_id);
		$Sofort->addUserVariable($method_id);
		$Sofort->setSuccessUrl($return_url);
		$Sofort->setAbortUrl($cancel_url);
		$Sofort->setTimeoutUrl($cancel_url);
		$Sofort->setNotificationUrl($notify_url);
		$Sofort->sendRequest();

		if($Sofort->isError()) {
			echo $Sofort->getError();
			return false;
		} else {
			$this->redirect_url = $Sofort->getPaymentUrl();
		}

		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses) {
		$this->pluginParams();
		$configKey = $this->plugin_params->user_id.':'.$this->plugin_params->project_id.':'.$this->plugin_params->token;

		require_once dirname(__FILE__).'/library/sofortLib.php';

		$notification = new SofortLib_Notification();
		$notification->getNotification();

		echo $notification->getTime();
		$transactionId = $notification->getTransactionId();

		$transactionData = new SofortLib_TransactionData($configKey);
		$transactionData->setTransaction($transactionId);
		$transactionData->sendRequest();

		$method_id = $transactionData->getUserVariable(1);
		$this->pluginParams($method_id);
		$this->payment_params =& $this->plugin_params;

		global $Itemid;
		$this->url_itemid = empty($Itemid) ? '' : '&Itemid=' . $Itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order'.$this->url_itemid;

		if(empty($this->payment_params)){
			$this->redirect($cancel_url);
			return false;
		}

		$order_id = $transactionData->getUserVariable(0);
		echo $order_id;
		$dbOrder = $this->getOrder($order_id);

		if(empty($dbOrder)){
			$this->redirect_url = $cancel_url;
			return false;
		}

		$history = new stdClass();
		$history->history_data = 'TransactionId: '.$transactionId;

		if($transactionData->getStatus() == 'pending') {
			$email = new stdClass();
			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Sofort',$transactionData->getStatus(),$dbOrder->order_number);
			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Sofort',$transactionData->getStatus()))."\r\n\r\n".$transactionData->getStatusReason();
			$action = false;
			$order_status =  $this->payment_params->pending_status;
			$this->modifyOrder($order_id, $order_status, $history, $email);

			return false;
		}

		if ($transactionData->getStatus() != 'received') {

			$order_status = 'created';
			$email = new stdClass();
			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Sofort',$order_status)).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$transactionData->getStatusReason();
		 	$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Sofort',$order_status,$dbOrder->order_number);

			$this->modifyOrder($order_id, $order_status, $history,$email);

			return false;
		}

		$order_status = $this->payment_params->verified_status;
		$history->history_data = 'TransactionId: '.$transactionId;
		$history->notified = 1;
		$email = new stdClass();
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Sofort',$transactionData->getStatus(),$dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Sofort',$order_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order_status)."\r\n\r\n".$transactionData->getStatusReason();

		$this->modifyOrder($order_id, $order_status, $history,$email);

		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'SOFORT';
		$element->payment_description = 'Betalen met Credit card';
		$element->payment_images = 'MasterCard';

		$element->payment_params->merchant_id = '';
		$element->payment_params->token = '';
		$element->payment_params->service_type = 'B';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}
}
