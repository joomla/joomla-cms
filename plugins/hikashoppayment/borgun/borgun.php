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
class plgHikashoppaymentBorgun extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'GBP', 'USD', 'EUR', 'DKK', 'NOK', 'SEK', 'CHF', 'CAD', 'JPY', 'ISK'
	);
	var $languages = array(
		'IS', 'EN', 'DE', 'FR', 'RU', 'ES', 'IT', 'PT', 'SE'
	);
	var $multiple = true;
	var $name = 'borgun';
	var $pluginConfig = array(
		'merchantid' => array('MERCHANT_ID', 'input'),
		'paymentgatewayid' => array('Payment Gateway Id', 'input'),
		'securecode' => array('Secure Code', 'input'),
		'debug' => array('DEBUG', 'boolean','0'),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$this->url_itemid;
		$notif_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&notif_id='.$method_id.'&order_id='.$order->order_id.'&lang='.$this->locale.$this->url_itemid;

		$locale = strtoupper($this->locale);
		if(!in_array($locale, $this->languages))
			$locale = 'EN';

		if( isset($this->payment_params->sandbox) && $this->payment_params->sandbox) {
			$this->url = 'https://test.borgun.is/SecurePay/default.aspx';
		} else {
			$this->url = 'https://securepay.borgun.is/securepay/default.aspx';
		}

		$this->vars = array(
			'MerchantId' => @$this->payment_params->merchantid,
			'paymentgatewayid' => @$this->payment_params->paymentgatewayid,
			'Orderid' => $order->order_id,
			'reference' => $order->order_number,
			'checkhash' => md5(@$this->payment_params->merchantid . $notif_url . @$this->payment_params->securecode),
			'amount' => number_format($order->cart->full_total->prices[0]->price_value_with_tax, 2, ',', ''),
			'currency' => $this->currency->currency_code,
			'language' => $locale,

			'Itemdescription_1' => JText::_('CART_PRODUCT_TOTAL_PRICE'),
			'Itemcount_1' => '1',
			'Itemunitamount_1' => number_format($order->cart->full_total->prices[0]->price_value_with_tax, 2, ',', ''),
			'Itemamount_1' => number_format($order->cart->full_total->prices[0]->price_value_with_tax, 2, ',', ''),

			'buyername' => '', // can be empty
			'buyeremail' => $this->user->user_email, // optional

			'returnurlsuccess' => $notif_url, // user end ok
			'returnurlsuccessserver' => $notif_url, // notif url
			'returnurlcancel' => $cancel_url, // user end cancel
			'returnurlerror' => $cancel_url // user end cancel/error
		);

		return $this->showPage('end');
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

		if(empty($vars['orderid']) || empty($vars['step']))
			return false;
		$order_id = (int)$vars['orderid'];
		$vars['step'] = strtolower($vars['step']);
		$payment_status = strtolower( @$vars['status'] );

		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder)) {
			return false;
		}
		if($method_id != $dbOrder->order_payment_id)
			return false;
		$this->loadOrderData($dbOrder);

		$return_url = hikashop_completeLink('checkout&task=after_end&order_id=' . $order_id . $this->url_itemid);
		$cancel_url = hikashop_completeLink('order&task=cancel_order&order_id=' . $order_id . $this->url_itemid);

		if($vars['step'] == 'confirmation' && $dbOrder->order_status == $this->payment_params->verified_status) {
			$this->app->redirect($return_url);
			return true;
		}

		$checkHash = md5($vars['orderid'] . number_format($dbOrder->order_full_price, 2, ',', '') . @$this->payment_params->securecode);
		if($checkHash != $vars['orderhash']) {
			if($vars['step'] != 'payment') {
				$this->app->redirect($cancel_url);
			}
			return false;
		}

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id.$this->url_itemid;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$history = new stdClass();
		$history->notified = 0;
		$history->data = '';
		$email = new stdClass();

		$completed = ($payment_status == 'ok');

		if( !$completed ) {
			$order_status = $this->payment_params->invalid_status;
			$history->data .= "\n\n" . 'payment with code ' . $vars['authorizationcode'];

			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Borgun',$order_status)).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Borgun',$order_status,$dbOrder->order_number);

			$this->modifyOrder($order_id, $order_status, $history,$email);

			$app->enqueueMessage('Transaction Failed: '.$vars['authorizationcode']);
			$app->redirect($cancel_url);
			return false;
		}

		if($dbOrder->order_status == $this->payment_params->verified_status) {
			$app->redirect($return_url);
			return true;
		}

		$order_status = $this->payment_params->verified_status;
		$vars['payment_status'] = 'Accepted';
		$history->data .= "\n\n" . 'AuthorizationCode: ' . $vars['authorizationcode'];
		$history->notified = 1;
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Borgun', $vars['payment_status'], $dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'Borgun', $vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order_status])."\r\n\r\n".$order_text;

		$this->modifyOrder($order_id,$order_status,$history,$email);

		if($vars['step'] == 'payment') {
			echo '<PaymentNotification>Accepted</PaymentNotification>';
			exit;
		}

		$app->redirect($return_url);
		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'BORGUN';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->merchant_id = '';
		$element->payment_params->paymentgatewayid = '';
		$element->payment_params->securecode = '';
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->verified_status = 'confirmed';
	}
}
