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

class plgHikashoppaymentStripe extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		"AED","AFN","ALL","AMD","ANG","AOA","ARS","AUD","AWG","AZN","BAM","BBD",
		"BDT","BGN","BIF","BMD","BND","BOB","BRL","BSD","BWP","BZD","CAD","CDF",
		"CHF","CLP","CNY","COP","CRC","CVE","CZK","DJF","DKK","DOP","DZD","EEK",
		"EGP","ETB","EUR","FJD","FKP","GBP","GEL","GIP","GMD","GNF","GTQ","GYD",
		"HKD","HNL","HRK","HTG","HUF","IDR","ILS","INR","ISK","JMD","JNY","KES",
		"KGS","KHR","KMF","KRW","KYD","KZT","LAK","LBP","LKR","LRD","LSL","LTL",
		"LVL","MAD","MDL","MGA","MKD","MNT","MOP","MRO","MUR","MVR","MWK","MXN",
		"MYR","MZN","NAD","NGN","NIO","NOK","NPR","NZD","PAB","PEN","PGK","PHP",
		"PKR","PLN","PYG","QAR","RON","RSD","RUB","RWF","SAR","SBD","SCR","SEK",
		"SGD","SHP","SLL","SOS","SRD","STD","SVC","SZL","THB","TJS","TOP","TRY",
		"TTD","TWD","TZS","UAH","UGX","USD","UYI","UZS","VEF","VND","VUV","SWT",
		"XAF","XCD","XOF","XPF","YER","ZAR","ZMW");
	var $multiple = true;
	var $name = 'stripe';
	var $doc_form = 'stripe';

	var $pluginConfig = array(
		'publishable_key' => array("STRIPE_PUBLISHABLE_KEY",'input'),
		'secret_key' => array("STRIPE_SECRET_KEY",'input'),
		'debug' => array('DEBUG', 'boolean','0'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);


	function __construct(&$subject, $config)
	{
		return parent::__construct($subject, $config);
	}

	function init()
	{
		static $init = null;
		if($init !== null)
			return $init;

		try{
			include_once(HIKASHOP_ROOT.'plugins/hikashoppayment/stripe/lib/Stripe.php');
			$init = true;
		}catch(Exception $e){
			$app = JFactory::getApplication();
			if($app->isAdmin())
				hikashop_display($e->getMessage());
			$init = false;
		}
		return $init;
	}

	function onPaymentDisplay(&$order, &$methods, &$usable_methods)
	{
		if(!$this->init())
			return false;
		return parent::onPaymentDisplay($order, $methods, $usable_methods);
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id) //On the checkout
	{
		if(!$this->init())
			return false;

		parent::onAfterOrderConfirm($order,$methods,$method_id);
		$this->notifyurl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&orderid='.$order->order_id;
		$this->order =& $order;
		return $this->showPage('end');
	}


	function getPaymentDefaultValues(&$element) //To set the back end default values
	{
		if(!$this->init())
			return false;

		$element->payment_name='Stripe';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,American_Express';
		$element->payment_params->address_type="billing";
		$element->payment_params->notification=1;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->verified_status='confirmed';
	}


	function onPaymentNotification(&$statuses)
	{
		if(!$this->init())
			return false;

		$order_id = (int)$_REQUEST['orderid'];
		$dbOrder = $this->getOrder($order_id);

		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
		{
			echo 'The system can\'t load the payment params';
			return false;
		}
		$this->loadOrderData($dbOrder);

		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id.$this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order_id.$this->url_itemid;

		$currency = $this->currency->currency_code;
		$amout = round($dbOrder->order_full_price,2)*100;
		$desc = JText::sprintf('ORDER_NUMBER').' : '.$order_id;

		Stripe::setApiKey(trim($this->payment_params->secret_key));
		Stripe::setApiVersion('2013-12-03');
		$token = $_POST['stripeToken'];

		try {
			$charge = Stripe_Charge::create(array(
				"amount" => $amout, // amount in cents, again
				"currency" => $currency,
				"card" => $token,
				"description" => $desc)
			);
		}
		catch(Exception $e)
		{
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
			$this->app->redirect($cancel_url,'Error charge : '.$e->getMessage());
			return false;
		}

		$this->modifyOrder($order_id, $this->payment_params->verified_status, true, true);
		$this->app->redirect($return_url);
		return true;
	}
}
