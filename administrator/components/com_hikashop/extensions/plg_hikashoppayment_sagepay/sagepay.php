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
class plgHikashoppaymentSagepay extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'GBP','USD','EUR','AED','AFN','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZN','BAM','BBD','BDT',
		'BGN','BHD','BIF','BMD','BND','BOB','BRL','BSD','BTN','BWP','BYR','BZD','CAD','CDF','CHF','CLP',
		'CNY','COP','CRC','CUP','CVE','CZK','DJF','DKK','DOP','DZD','EGP','ERN','ETB','FJD','FKP','GEL',
		'GHS','GIP','GMD','GNF','GTQ','GYD','HKD','HNL','HRK','HTG','HUF','IDR','ILS','INR','IQD','IRR',
		'ISK','JMD','JOD','JPY','KES','KGS','KHR','KMF','KPW','KRW','KWD','KYD','KZT','LAK','LBP','LKR',
		'LTL','LVL','LYD','MAD','MDL','MGA','MKD','MMK','MNT','MOP','MRO','MUR','MWK','MXN','MYR','MZN',
		'NAD','NGN','NIO','NOK','NPR','NZD','OMR','PAB','PEN','PGK','PHP','PKR','PLN','PYG','QAR','RON',
		'RSD','RUB','RWF','SAR','SBD','SCR','SEK','SGD','SHP','SLL','SOS','SRD','STD','SVC','SYP','SZL',
		'THB','TJS','TMT','TND','TRY','TTD','TWD','TZS','UAH','UGX','UYU','UZS','VEF','VND','WST','XAF',
		'XCD','XOF','XPF','YER','ZAR','ZMK','ZWL'
	);

	var $multiple = true;
	var $name = 'sagepay';
	var $pluginConfig = array(
		'vendor_name' => array('VENDOR_NAME', 'input'),
		'password' => array('HIKA_PASSWORD', 'input'),
		'mode' => array('MODE', 'list', array(
			'LIVE' => 'Live',
			'TEST' => 'Test',
			'SIMU' => 'Simulation'
		)),
		'billing_agreement' => array('Billing Agreement', 'boolean', '0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'sendbasket' => array('Send Basket data', 'boolean', '0'),
		'txtype' => array('Transaction type', 'list', array(
			'0' => 'Payment',
			'1' => 'Authenticate',
			'2' => 'Deferred'
		)),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus')
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order,$methods,$method_id);

		if(!function_exists('mcrypt_encrypt')){
			$this->app->enqueueMessage('The SagePay payment plugin requires the PHP extension Mcrypt to be installed and activated on your server. Please contact your hosting company to set it up');
			return false;
		}

		$viewType='end';

		$server_url = HIKASHOP_LIVE.'index.php';

		$return_url_p = 'option=com_hikashop&ctrl=checkout&task=notify&notif_payment=sagepay&notif_id='.$method_id.'&tmpl=component&lang='.$this->locale.$this->url_itemid;

		$address1 = ''; $address2 = '';
		$address1 = @$order->cart->billing_address->address_street;
		if( strlen($address1) > 100 ) {
			$address2 = substr($address1, 100, 100);
			$address1 = substr($address1, 0, 100);
		}

		$ship_address1 = ''; $ship_address2 = '';
		$ship_address1 = @$order->cart->shipping_address->address_street;
		if( empty($ship_address1) ) { $ship_address1 = $address1; }
		if( strlen($ship_address1) > 100 ) {
			$ship_address2 = substr($ship_address1, 100, 100);
			$ship_address1 = substr($ship_address1, 0, 100);
		}

		$sendEmail = 0;
		if(empty($order->cart->billing_address->address_post_code)){
			$billing_code = '0000';
		}else{
			$billing_code = $order->cart->billing_address->address_post_code;
		}
		if(empty($order->cart->shipping_address->address_post_code)){
			$shipping_code = $billing_code;
		}else{
			$shipping_code = $order->cart->shipping_address->address_post_code;
		}
		$postData = array(
			'VendorTxCode' => $order->order_id,
			'Amount' => round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$this->currency->currency_locale['int_frac_digits']),
			'Currency' => $this->currency->currency_code,
			'Description' => $order->order_number,
			'SuccessURL' => $server_url . '?' . $return_url_p,
			'FailureURL' => $server_url . '?' . $return_url_p,
			'CustomerName' => @$order->cart->billing_address->address_firstname . ' ' . @$order->cart->billing_address->address_lastname,
			'SendEMail' => $sendEmail,

			'BillingFirstnames' => @$order->cart->billing_address->address_firstname,
			'BillingSurname' => @$order->cart->billing_address->address_lastname,
			'BillingAddress1' => $address1,
			'BillingAddress2' => $address2,
			'BillingCity' => @$order->cart->billing_address->address_city,
			'BillingPostCode' => $billing_code,
			'BillingCountry' => @$order->cart->billing_address->address_country->zone_code_2,

			'DeliveryFirstnames' => @$order->cart->shipping_address->address_firstname,
			'DeliverySurname' => @$order->cart->shipping_address->address_lastname,
			'DeliveryAddress1' => $ship_address1,
			'DeliveryAddress2' => $ship_address2,
			'DeliveryCity' => @$order->cart->shipping_address->address_city,
			'DeliveryPostCode' => $shipping_code,
			'DeliveryCountry' => @$order->cart->shipping_address->address_country->zone_code_2,

			'ReferrerID' => 'B5C3FBCA-9E9C-4C49-B3ED-3AFEEF7592A4',
			'BillingAgreement' => (empty($this->payment_params->billing_agreement) ? 0 : 1),
			'AllowGiftAid' => 0,
			'ApplyAVSCV2' => 0,
			'Apply3DSecure' => 0,
		);

		$bxml = '<basket>';

		foreach($order->cart->products as $product){
			$qty = (int)$product->order_product_quantity;
			$unitprice = (float)$product->order_product_price;
			$tax = (float)$product->order_product_tax;

			$bxml .= '<item>'.
				'<description>'.strip_tags($product->order_product_name).'</description>'.
				'<quantity>'.$qty.'</quantity>'.
				'<unitNetAmount>'.round($unitprice,2).'</unitNetAmount>'.
				'<unitTaxAmount>'.round($tax, 2).'</unitTaxAmount>'.
				'<unitGrossAmount>'.round($unitprice + $tax, 2).'</unitGrossAmount>'.
				'<totalGrossAmount>'.round($qty * $unitprice, 2).'</totalGrossAmount>'.
			'</item>';
		}

		if(!empty($order->order_shipping_price)) {
			$shippingprice = (float)$order->order_shipping_price;
			$shippingtax = (float)$order->order_shipping_tax;

			$bxml .= '<deliveryNetAmount>'.round($shippingprice,2).'</deliveryNetAmount>'.
				'<deliveryTaxAmount>'.round($shippingtax,2).'</deliveryTaxAmount>'.
				'<deliveryGrossAmount>'.round($shippingprice + $shippingtax,2).'</deliveryGrossAmount>';
		}

		$bxml .= '</basket>';

		if(!empty($this->payment_params->sendbasket) && strlen($bxml) < 20000) {
			$postData['BasketXML'] = $bxml;
		}

		if(@$order->cart->billing_address->address_country->zone_code_3=='USA'){
			$postData['BillingState'] = @$order->cart->billing_address->address_state->zone_code_3;
		}
		if(@$order->cart->shipping_address->address_country->zone_code_3=='USA'){
			$postData['DeliveryState'] = @$order->cart->shipping_address->address_state->zone_code_3;
		}

		$t = array();
		foreach($postData as $k => $v) {
			$t[] = $k . '=' . $v;
		}
		$postData = implode('&',$t);
		unset($t);

		$txTypes = array(
			0 => 'PAYMENT',
			1 => 'AUTHENTICATE',
			2 => 'DEFERRED'
		);
		$txType = $txTypes[0];
		if(isset( $txTypes[ (int)@$this->payment_params->txtype ] ))
			$txType = $txTypes[(int)@$this->payment_params->txtype];

		$this->vars = array(
			'navigate' => '',
			'VPSProtocol' => '3.00',
			'TxType' => $txType,
			'Vendor' => $this->payment_params->vendor_name,
			'Crypt' => $this->encryptAndEncode($postData, $this->payment_params->password, '' ),
		);

		switch( $this->payment_params->mode ) {
			case 'LIVE':
				$this->url = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
				break;
			case 'TEST':
				$this->url = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
				break;
			case 'SIMU':
			default:
				$this->url = 'https://test.sagepay.com/Simulator/VSPFormGateway.asp';
				break;
		}

		if($this->payment_params->debug) {
			$this->writeToLog(htmlentities($postData) )."\n\n\n";
		}

		return $this->showPage($viewType);
	}

	function onPaymentNotification(&$statuses){
		$method_id = JRequest::getInt('notif_id', 0);
		$this->pluginParams($method_id);
		$this->payment_params =& $this->plugin_params;
		if(empty($this->payment_params))
			return false;

		$crypt = null;
		if(!empty($_REQUEST['crypt']))
			$crypt = $_REQUEST['crypt'];
		if(empty($crypt))
			$crypt = JRequest::getString('crypt', null, 'default', JREQUEST_ALLOWRAW);

		$data = $this->decodeAndDecrypt($crypt, $this->payment_params->password);

		if(empty($this->app))
			$this->app = JFactory::getApplication();

		$httpsHikashop = HIKASHOP_LIVE;
		if( $this->payment_params->debug ) {
			$httpsHikashop = str_replace('https://','http://', HIKASHOP_LIVE);
		}

		$cancel_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=order&task=cancel_order';

		if( strpos($data, 'Status') === false ) {
			$this->app->enqueueMessage('Error while processing encrypted data');
			$this->app->redirect($cancel_url);
			return false;
		}
		$vars = array();
		parse_str($data, $vars);

		$vars['OrderID'] = (int)$vars['VendorTxCode'];
		$order_id = (int)$vars['OrderID'];
		$order_status = '';

		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder)){
			$this->app->enqueueMessage('Could not load any order for your notification '.$vars['OrderID']);
			$this->app->redirect($cancel_url);
			return false;
		}
		if($method_id != $dbOrder->order_payment_id)
			return false;
		$this->loadOrderData($dbOrder);
		if($this->payment_params->debug) {
			echo print_r($vars,true)."\n\n\n";
			echo print_r($dbOrder,true)."\n\n\n";
		}

		$cancel_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order_id.$this->url_itemid;
		if($this->payment_params->debug)
			echo print_r($dbOrder,true)."\n\n\n";

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$return_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id.$this->url_itemid;

		$history = new stdClass();
		$email = new stdClass();

		$history->notified = 0;
		$history->amount = $vars['Amount'] . $this->currency->currency_code;
		$history->data = $vars['Status'] . ': ' . $vars['StatusDetail'] . "\n--\n" . 'Sage Pay ID: ' . $vars['VPSTxId'] . "\n" . 'Authorisation Code:' . $vars['TxAuthNo'] . "\n" . ob_get_clean();

		$completed = ($vars['Status'] == 'OK');

		if((int)@$this->payment_params->txType == 1) {
			$completed = $completed || ($vars['Status'] == 'AUTHENTICATED') || ($vars['Status'] == 'REGISTERED');
		}

		if( !$completed ) {
			$order_status = $this->payment_params->invalid_status;
			$history->data .= "\n\n" . 'payment with code '.$vars['Status'].' - '.$vars['StatusDetail'];

			$order_text = $vars['Status'] . ' - ' . $vars['StatusDetail']."\r\n\r\n".$order_text;
			$email->body = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'SagePay', $vars['Status'])) . ' ' . JText::_('STATUS_NOT_CHANGED') . "\r\n\r\n" . $order_text;
		 	$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'SagePay', $vars['Status'], $dbOrder->order_number);

			$this->modifyOrder($order_id, $order_status, $history,$email);

			$this->app->enqueueMessage('Transaction Failed: '.$vars['StatusDetail']);
			$this->app->redirect($cancel_url);
			return false;
		}

		$order_status = $this->payment_params->verified_status;
		if((int)@$this->payment_params->txType == 1 && (($vars['Status'] == 'AUTHENTICATED') || ($vars['Status'] == 'REGISTERED')))
			$order_status = $this->payment_params->pending_status;

		$vars['payment_status'] = 'Accepted';
		$history->notified = 1;
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'SagePay', $vars['payment_status'], $dbOrder->order_number);
		$email->body = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'SagePay', $vars['payment_status'])) . ' ' . JText::sprintf('ORDER_STATUS_CHANGED', $statuses[$order_status]) . "\r\n\r\n" . $order_text;

		$this->modifyOrder($order_id, $order_status, $history, $email);

		$this->app->redirect($return_url);
		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'SagePay';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'VISA,Maestro,MasterCard';

		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}

	function simpleXor($in, $k) {
		$lst = array();
		$output = '';
		for($i = 0; $i < strlen($k); $i++) {
			$lst[$i] = ord(substr($k, $i, 1));
		}
		for($i = 0; $i < strlen($in); $i++) {
			$output .= chr(ord(substr($in, $i, 1)) ^ ($lst[$i % strlen($k)]));
		}
		return $output;
	}
	function encryptAndEncode($in, $password, $type) {
		$password = trim($password);
		if($type == 'XOR') {
			return base64_encode($this->simpleXor($in, $password));
		}
		$this->addPKCS5Padding($in);
		$iv = $password;
		$strCrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $password, $in, MCRYPT_MODE_CBC, $iv);
		return "@" . bin2hex($strCrypt);
	}

	function decodeAndDecrypt($in, $password) {
		if( substr($in,0,1) == '@') {
			$iv = $password;
			$in = substr($in,1);
			$in = pack('H*', $in);
			return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $password, $in, MCRYPT_MODE_CBC, $iv);
		}
		return $this->simpleXor(base64_decode(str_replace(' ','+',$in)), $password);
	}

	function addPKCS5Padding(&$input) {
		$blocksize = 16;
		$padding = '';
		$padlength = $blocksize - (strlen($input) % $blocksize);
		for($i = 1; $i <= $padlength; $i++) {
			$padding .= chr($padlength);
		}
		$input .= $padding;
	}
}
