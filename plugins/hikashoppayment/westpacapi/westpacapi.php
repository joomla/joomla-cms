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
class plgHikashoppaymentWestpacapi extends hikashopPaymentPlugin
{
	var $accepted_currencies = array( "AUD" );
	var $multiple = true;
	var $name = 'westpacapi';

	var $pluginConfig = array(

		'username'=> array('Customer Username','input'),
		'password'=> array('Customer Password','input'),
		'merchant_id' => array("Merchant Id",'input'),
		'ipaddress' => array('Your IP address','html',''),

		'certFile' => array('Certificat file path','input'),

		'testingmode'=> array("testing Mode",'boolean','0'),
		'debug' => array("DEBUG",'boolean','0'),
		'notification'=> array("allow notification from westpac",'boolean','0'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
	);

	function __construct(&$subject, $config)
	{
		$this->pluginConfig['ipaddress'][2] = $_SERVER['SERVER_ADDR'];

		return parent::__construct($subject, $config);
	}

	function needCC(&$method) {

		$method->ask_cc = true;
		$method->ask_owner = true;
		$method->ask_ccv = true;

		return true;
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id)
	{
		parent::onAfterOrderConfirm($order,$methods,$method_id);

		if(parent::onBeforeOrderCreate($order, $do) === true) {
		return true;
		}

		if(!function_exists('curl_init')){
		$this->app->enqueueMessage('The Authorize.net payment plugin in AIM mode needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
		$do = false;
		return false;
		}

		if (empty($this->payment_params->merchant_id)) {
			$this->app->enqueueMessage('You have to configure a customer merchant for the westpac plugin payment first : check your plugin\'s parameters, on your website backend', 'error');
			return false;
		}

		if (empty($this->payment_params->username)) {
			$this->app->enqueueMessage('You have to configure your username for the westpac plugin payment first : check your plugin\'s parameters,
			on your website backend','error');

			return false;
		}

		if (empty($this->payment_params->password)) {
			$this->app->enqueueMessage('You have to configure your password for the westpac plugin payment first : check your plugin\'s parameters,
			on your website backend','error');

			return false;
		}

		if ( (empty($this->payment_params->certFile) ) || ($this->payment_params->certFile == '.pem expected') ) {
				$this->app->enqueueMessage('You have to define the certificat file path for the westpac Api plugin payment first : check your plugin\'s parameters,
			on your website backend','error');
			return false;
		}

		include dirname(__FILE__) . DS . 'westpacapi_qvalent.php';

		$capath = JPath::clean(HIKASHOP_ROOT .'plugins' . DS . 'hikashoppayment' . DS . 'westpacApi' . DS . 'cacerts.crt');  

		$initParams =
		"certificateFile=" . $this->payment_params->certFile . "&" .
		"caFile=". $capath . "&" .
		'logDirectory=' . HIKASHOP_ROOT . 'media' . DS . 'com_hikashop' . DS . 'upload' . DS . 'safe' . DS . 'logs' . DS ;
		$paywayAPI = new Qvalent_PayWayAPI();
		$paywayAPI->initialise( $initParams );

		$amount = round($order->cart->full_total->prices[0]->price_value_with_tax,2)*100;

		$this->ccLoad();

		$vars = array(

			'order.type' => "capture",
			'customer.merchant' => trim($this->payment_params->merchant_id),
			'customer.username' => trim($this->payment_params->username),
			'customer.password' => trim($this->payment_params->password),
			'customer.orderNumber' => $order->order_id,
			'customer.originalOrderNumber' => $order->order_id,

			'card.PAN' => $this->cc_number,
			'card.CVN' => $this->cc_CCV,
			'card.expiryYear' => $this->cc_year,
			'card.expiryMonth' => $this->cc_month,
			'card.currency' => 'AUD',
			'order.amount' => $amount,
			'order.ECI' => 'SSL',
		);

		if($this->payment_params->testingmode)
		{
			$vars['customer.merchant'] = 'TEST';
		}

		if($this->payment_params->debug)
		{
			$this->writeToLog("\n Data (vars) send to westpac in Api Mode: \n\n\n");
			$this->writeToLog(print_r($vars,true));
		}

		$requestText = $paywayAPI->formatRequestParameters( $vars );
		$responseText = $paywayAPI->processCreditCard( $requestText );

		$post_response = $paywayAPI->parseResponseParameters( $responseText );

		if($this->payment_params->debug)
		{
			$this->writeToLog("\n Data (post response) receive from westpac in Api Mode: \n\n\n");
			$this->writeToLog(print_r($post_response,true));
		}

		$this->ccClear();
		if ($post_response['response.summaryCode'] == '0') {
			$order_status = $this->payment_params->verified_status;
			$this->modifyOrder($order->order_id, $order_status, true, true);

			return $this->showPage('thankyou');
		}

		else {
			$order_status = $this->payment_params->invalid_status;
			$this->modifyOrder($order->order_id, $order_status, true, true);

			$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id . $this->url_itemid;

			$error = "Report by Westpac </br>Error : " . $post_response ['response.text'];

			$this->app->redirect($cancel_url, $error);
			return true;
		}
	}

	function getPaymentDefaultValues(&$element)
	{
		$element->payment_params->merchant_id = "";
		$element->payment_name = 'westpacapi';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->notification = 1;
		$element->payment_params->testingmode = 1;
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->verified_status = 'confirmed';

		$certpath = JPath::clean(HIKASHOP_ROOT .'plugins' . DS . 'hikashoppayment' . DS . 'westpacApi' . DS . 'ccapi.pem'); 		

		$element->payment_params->certFile = (file_exists($certpath ) ) ? $certpath : '.pem expected';
	}
}

