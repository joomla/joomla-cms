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
class plgHikashoppaymentPayfast extends hikashopPaymentPlugin
{
	var $accepted_currencies = array( 'ZAR' );
	var $multiple = true;
	var $name = 'payfast';

	var $pluginConfig = array(
		'merchant_id' => array('Merchant Id', 'input'),
		'merchant_key' => array('Merchant Key', 'input'),
		'debug' => array('DEBUG', 'boolean', '0'),
		'notification' => array('Allow notification from PayFast', 'boolean', '0'),
		'testingMode' => array('Testing Mode','boolean','0'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
	);

	function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		if ($this->payment_params->testingMode == true) {
			$this->payment_params->url = "https://sandbox.payfast.co.za/eng/process";
		} else {
			$this->payment_params->url = "https://www.payfast.co.za/eng/process";
		}

		if (empty($this->payment_params->merchant_id)) {
			$this->app->enqueueMessage('You have to configure an merchant id for the payfast plugin payment first : check your plugin\'s parameters, on your website backend', 'error');
			return false;
		}

		if (empty($this->payment_params->merchant_key)) {
			$this->app->enqueueMessage('You have to configure the merchant key for the payfast plugin payment first : check your plugin\'s parameters, on your website backend', 'error');
			return false;
		}

		$amount = round($order->cart->full_total->prices[0]->price_value_with_tax, 2);

		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&lang='.$this->locale . $this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id . $this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id . $this->url_itemid;

		$vars = array(
			'merchant_id' => trim($this->payment_params->merchant_id),
			'merchant_key' => trim($this->payment_params->merchant_key),
			'return_url' => $return_url,
			'cancel_url' => $cancel_url,
			'notify_url' => $notify_url,

			'name_first' => substr(@$order->cart->billing_address->address_firstname, 0, 99),
			'name_last' => substr(@$order->cart->billing_address->address_lastname, 0, 99),
			'email_address' => substr($this->user->user_email, 0, 99),

			'm_payment_id' => (int)$order->order_id,
			'amount' => $amount,
			'item_name' => $order->order_number,
		);

		$this->vars = $vars;

		$pfOutput = array();
		foreach($vars as $key => $val ) {
			if(!empty($val)) {
				$pfOutput[] = $key .'='. urlencode( trim($val) );
			}
		}

		$getString = implode('&', $pfOutput);

		$vars['signature'] = md5( $getString );

		if($this->payment_params->debug) {
			$this->writeToLog("Data sent to PayFast: \n\n" . print_r($vars, true));
		}

		return $this->showPage('end');
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_params->merchant_id = "10000100";
		$element->payment_params->merchant_key = "46f0cd694581a";
		$element->payment_name = 'payfast';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->notification = 1;
		$element->payment_params->testingMode = 1;
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->verified_status = 'confirmed';
		$element->payment_params->pending_status = 'created';
	}

	function onPaymentNotification(&$statuses) {
		header('HTTP/1.0 200 OK');
		flush();

		$filter = JFilterInput::getInstance();
		foreach($_POST as $key => $val)
		{
			$key = $filter->clean($key);
			$val = JRequest::getString($key);
			$pfdata[$key] = $val;
		}

		$order_id = (int)@$pfdata['m_payment_id'];
		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);

		$this->writeToLog('payfast : '.print_r($this->payment_params, true));
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		$pfParamString = array();
		foreach( $pfdata as $key => $val )
		{
			if(in_array($key, array('m_payment_id','pf_payment_id','payment_status','item_name','item_description','amount_gross','amount_fee','amount_net','custom_str1','custom_str2','custom_str3','custom_str4','custom_str5','custom_int1','custom_int2','custom_int3','custom_int4','custom_int5','name_first','name_last','email_address','merchant_id') ))
			{
				$pfParamString[] = $key .'='. urlencode( $val );
			}
		}

		$pfTempParamString = implode('&', $pfParamString);
		$pfParamString = implode('&', $pfParamString);

		$signature = md5($pfTempParamString);

		if($signature!=$pfdata['signature']) {
			if($this->payment_params->debug) {
				echo 'Hash error '.$pfdata['signature'].' - '.$signature."\n\n\n";
				echo"\n\n\n Invalid Signature \n\n\n";
				echo "Data receive from PayFast: \n\n\n";
				print_r($pfdata);
				$this->writeToLog(null);
			}

			die('Invalid Signature');
		}

		$validHosts = array(
			'www.payfast.co.za',
			'sandbox.payfast.co.za',
			'w1w.payfast.co.za',
			'w2w.payfast.co.za',
		);

		$validIps = array();
		foreach($validHosts as $pfHostname) {
			$ips = gethostbynamel($pfHostname);

			if($ips !== false) {
				$validIps = array_merge($validIps, $ips);
			}
		}

		$validIps = array_unique( $validIps );
		if(!in_array($_SERVER['REMOTE_ADDR'], $validIps)) {
			if($this->payment_params->debug) {
				print_r ($_SERVER['REMOTE_ADDR'])."\n\n\n Source IP not Valid\n\n\n";
				$this->writeToLog(null);
			}

			die('Source IP not Valid');
		}

		$amount = round((float)hikashop_toFloat($dbOrder->order_full_price), 2);
		if( abs(floatval($amount) - floatval($pfdata['amount_gross'])) > 0.01) {
			if($this->payment_params->debug) {
				$amountmismatch = ( floatval( $amount ) - floatval( $pfdata['amount_gross'] ) );
				echo "amount - amount form PayFast = ".$amountmismatch."\n\n\n Amounts Mismatch\n\n\n";
				$this->writeToLog(null);
			}

			die('Amounts Mismatch');
		}

		$pfHost = ($this->payment_params->testingMode) ?  'sandbox.payfast.co.za' : 'www.payfast.co.za';

		if(in_array('curl', get_loaded_extensions())) {
			$url = 'https://'. $pfHost .'/eng/query/validate';

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $pfParamString);

			if( !empty( $pfProxy ) )
			{
				curl_setopt( $ch, CURLOPT_PROXY, $proxy );
			}
			$response = curl_exec( $ch );
			curl_close( $ch );
		} else {
			$header = '';
			$res = '';
			$headerDone = false;

			$header = "POST /eng/query/validate HTTP/1.0\r\n" .
					"Host: ". $pfHost ."\r\n" .
					"User-Agent: ". PF_USER_AGENT ."\r\n" .
					"Content-Type: application/x-www-form-urlencoded\r\n" .
					"Content-Length: " . strlen( $pfParamString ) . "\r\n\r\n";

			$socket = fsockopen('ssl://'. $pfHost, 443, $errno, $errstr, 10);

			fputs($socket, $header . $pfParamString);

			$response = '';
			while(!feof($socket)) {
				$line = fgets($socket, 1024);

				if(strcmp( $line, "\r\n") == 0) {
					$headerDone = true;
				}
				else if( $headerDone ) {
					if(empty($line))
						$line = '';

					$response .= $line;
				}
			}
		}

		$lines = explode("\r\n", $response);
		$verifyResult = trim($lines[0]);

		if(strcasecmp( $verifyResult, 'VALID' ) != 0) {
			if($this->payment_params->debug) {
				print_r($verifyResult, true)."\n\n\n Data not valid\n\n\n";
				$this->writeToLog(null);
			}

			die('Data not valid');
		}

		$pfPaymentId = $pfdata['pf_payment_id'];

		switch($pfdata['payment_status']) {
			case 'COMPLETE':
				$order_status = $this->payment_params->verified_status;
				$this->modifyOrder($order_id, $order_status, true, true);
				break;

			case 'PENDING':
				$order_status = $this->payment_params->pending_status;
				$this->modifyOrder($order_id, $order_status, true, true);
				break;

			default:
			case 'FAILED':
				$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
				if($this->payment_params->debug) {
					echo "Statut from PayFast: ".$pfdata['payment_status'];
				}
				break;
		}
	}
}
