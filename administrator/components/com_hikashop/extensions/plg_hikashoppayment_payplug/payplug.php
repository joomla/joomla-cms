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
class plgHikashoppaymentPayplug extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'EUR'
	);

	var $multiple = true;
	var $name = 'payplug';
	var $pluginConfig = array(
		'email' => array('HIKA_EMAIL', 'input'),
		'password' => array('HIKA_PASSWORD', 'input'),
		'debug' => array('DEBUG', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;
		$notif_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&notif_id='.$method_id.'&order_id='.$order->order_id.'&lang='.$this->locale.$this->url_itemid;

		require_once(dirname(__FILE__).'/lib/payplug.php');

		try{
			Payplug::setConfigFromFile(HIKASHOP_MEDIA."payplug_parameters.json");

			$paymentUrl = PaymentUrl::generateUrl(array(
				'amount' => (int)(round($order->cart->full_total->prices[0]->price_value_with_tax,2)*100),
				'currency' => 'EUR',
				'ipnUrl' => $notif_url,
				'email' => $this->user->user_email,
				'firstName' => @$order->cart->billing_address->address_firstname,
				'lastName' => @$order->cart->billing_address->address_lastname,
				'order' => $order->order_id,
				'returnUrl' => $return_url
			));
		}catch(Exception $e){
			$this->app->enqueueMessage($e->getMessage());
			return;
		}
		header("Location: $paymentUrl");
		exit;
	}

	function onPaymentNotification(&$statuses) {
		$method_id = JRequest::getInt('notif_id', 0);
		$this->pluginParams($method_id);
		$this->payment_params =& $this->plugin_params;
		if(empty($this->payment_params))
			return false;

		$vars = array();
		$filter = JFilterInput::getInstance();
		foreach($_REQUEST as $key => $value) {
			$key = $filter->clean($key);
			$value = JRequest::getString($key);
			$vars[strtolower($key)] = $value;
		}

		if( @$this->payment_params->debug ) {
			$this->writeToLog( var_export($vars, true) );
		}

		require_once(dirname(__FILE__).'/lib/payplug.php');
		try{
			Payplug::setConfigFromFile(HIKASHOP_MEDIA."payplug_parameters.json");
			$ipn = new IPN();
			if( @$this->payment_params->debug ) {
				$this->writeToLog( var_export($ipn, true) );
			}
		}catch(Exception $e){
			$this->writeToLog($e->getMessage());
			return;
		}

		if(empty($ipn->order) || empty($ipn->state))
			return false;
		$order_id = (int)$ipn->order;

		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder)) {
			return false;
		}
		if($method_id != $dbOrder->order_payment_id)
			return false;
		$this->loadOrderData($dbOrder);

		$return_url = hikashop_completeLink('checkout&task=after_end&order_id=' . $order_id . $this->url_itemid);
		$cancel_url = hikashop_completeLink('order&task=cancel_order&order_id=' . $order_id . $this->url_itemid);


		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id.$this->url_itemid;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$history = new stdClass();
		$history->notified = 0;
		$history->data = '';
		$email = new stdClass();

		$completed = ($ipn->state == 'paid');
		$amount = (int)(round($dbOrder->order_full_price,2)*100);
		if( !$completed ||$ipn->amount != $amount ) {
			$order_status = $this->payment_params->invalid_status;
			$history->data .= "\n\n" . 'payment with code ' . $ipn->idTransaction;

			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','PayPlug',$order_status)).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','PayPlug',$order_status,$dbOrder->order_number);

			$this->modifyOrder($order_id, $order_status, $history,$email);
			return false;
		}

		if($dbOrder->order_status == $this->payment_params->verified_status) {
			if( @$this->payment_params->debug ) {
				$this->writeToLog( 'Already confirmed' );
			}
			return true;
		}

		$order_status = $this->payment_params->verified_status;
		$vars['payment_status'] = $ipn->state;
		$history->data .= "\n\n" . 'Transaction id: ' . $ipn->idTransaction;
		$history->notified = 1;
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','PayPlug', $vars['payment_status'], $dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'PayPlug', $vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order_status])."\r\n\r\n".$order_text;

		$this->modifyOrder($order_id,$order_status,$history,$email);
		return true;
	}

	function onPaymentConfigurationSave(&$element) {
		$app = JFactory::getApplication();
		if(empty($element->payment_params->email)){
			$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED', 'PayPlug', JText::_('HIKA_EMAIL'), 'PayPlug', 'http://www.payplug.fr'));
		}elseif(empty($element->payment_params->password)){
			$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED', 'PayPlug', JText::_('HIKA_PASSWORD'), 'PayPlug', 'http://www.payplug.fr'));
		}else{
			require_once(dirname(__FILE__).'/lib/payplug.php');
			try{
				$parameters = Payplug::loadParameters($element->payment_params->email, $element->payment_params->password);
				$parameters->saveInFile(HIKASHOP_MEDIA."payplug_parameters.json");
			}catch(Exception $e){
				$app->enqueueMessage($e->getMessage());
			}
		}
		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'PayPlug';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card';

		$element->payment_params->email = '';
		$element->payment_params->password = '';
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->verified_status = 'confirmed';
	}
}
