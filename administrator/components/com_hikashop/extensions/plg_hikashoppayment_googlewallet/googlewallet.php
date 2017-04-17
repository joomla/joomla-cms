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

class plgHikashoppaymentgooglewallet extends hikashopPaymentPlugin
{
	var $accepted_currencies = array( "EUR", "USD" );
	var $multiple = true;
	var $name = 'googlewallet';

	var $pluginConfig = array(
		'sellerIdentifier' => array("Seller Identifier",'input'),
		'sellerSecret'=>array("Seller Secret",'input'),
		'url'=>array("postback url in google wallet settings",'html',''),
		'debug' => array("DEBUG", 'boolean','0'),
		'testingMode'=> array("testing Mode",'boolean','0'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
	);

	function __construct(&$subject, $config)
	{
		$this->pluginConfig['url'][2] = HIKASHOP_LIVE.'index.php?option=com_hikashop&amp;ctrl=checkout&amp;task=notify&amp;notif_payment=googlewallet&amp;tmpl=component';

		return parent::__construct($subject, $config);
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id)
	{
		parent::onAfterOrderConfirm($order,$methods,$method_id);

		if ($this->payment_params->testingMode == true) 
		{
			$this->payment_params->url = "https://sandbox.google.com/checkout/inapp/lib/buy.js";
		}
		else 
		{
			$this->payment_params->url = "https://wallet.google.com/inapp/lib/buy.js";
		}

		if (empty($this->payment_params->sellerIdentifier))
		{
			$this->app->enqueueMessage('You have to configure an seller Identifier for the googlewallet plugin payment first : check your plugin\'s parameters,
			on your website backend','error');

			return false;
		}

		if (empty($this->payment_params->sellerSecret))
		{
			$this->app->enqueueMessage('You have to configure the seller Secret for the googlewallet plugin payment first : check your plugin\'s parameters,
			on your website backend','error');

			return false;
		}

		$amount = round($order->cart->full_total->prices[0]->price_value_with_tax,2);

		$succes_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id . $this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id . $this->url_itemid;
		$this->payment_params->succes_url = $succes_url;
		$this->payment_params->cancel_url = $cancel_url;

		$vars = array(

			'iss' => trim($this->payment_params->sellerIdentifier),
			'aud'=> "Google",
			'typ' => "google/payments/inapp/item/v1",
			'exp' => time() + 3600,
			'iat' => time(),
			'request' => array (
				'name' => $order->order_number,
				'description' => "", //optionnal
				'price' => $amount,
				'currencyCode' => $this->currency->currency_code,
				'sellerData' => $order->order_id, //Order_id for Hikashop
			)
		); 

		$sellerSecret = $this->payment_params->sellerSecret;

		$token = JWT::encode($vars, $sellerSecret);
		$this->token = $token;

		$this->showPage('end');

		if($this->payment_params->debug) $this->writeToLog("Data send to googlewallet: \n\n\n".print_r($vars,true));

	}

	function getPaymentDefaultValues(&$element)
	{
		$element->payment_params->sellerIdentifier = "";
		$element->payment_params->sellerSecret = "";
		$element->payment_name = 'googlewallet';
		$element->payment_params->url = HIKASHOP_LIVE."index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=googlewallet&tmpl=component";
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->notification = 1;
		$element->payment_params->verified_status = 'confirmed';
	}

	function onPaymentNotification(&$statuses)
	{
		$this->pluginParams();
		$this->payment_params = $this->plugin_params;

		if($this->payment_params->debug) $this->writeToLog("JWT from googlewallet: \n\n\n".print_r($_POST,true));

		$gwdata = JWT::decode($_POST["jwt"], null, false);
		if(empty($gwdata)) return false;

		if($this->payment_params->debug) $this->writeToLog("Decoded data from googlewallet: \n\n\n".print_r($gwdata,true));

		$dbOrder = $this->getOrder($gwdata->request->sellerData);
		$this->loadPaymentParams($dbOrder);

		$gwdata = JWT::decode($_POST["jwt"], $this->payment_params->sellerSecret, true);
		if(empty($gwdata)) return false;
		$orderId = $gwdata->response->orderId;

		if ($orderId)//success
		{   
			echo $orderId; 
			ob_start();

			$order_status = $this->payment_params->verified_status;
			$this->modifyOrder($order_id, $order_status, true, true);

			return true;
		}

		$email = new stdClass();
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Google Wallet','Unknown',$dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Google Wallet','Unknown')).' '.JText::_('STATUS_NOT_CHANGED');
		$action = false;
		$this->modifyOrder($action, null, null, $email);
	}
}

class JWT
{   
	public static function decode($jwt, $key = null, $verify = true)
	{
		$tks = explode('.', $jwt);
		if (count($tks) != 3) 
		{

			throw new UnexpectedValueException('Wrong number of segments');
		}
		list($headb64, $payloadb64, $cryptob64) = $tks;
		if (null === ($header = JWT::jsonDecode(JWT::urlsafeB64Decode($headb64)))) 
		{

			throw new UnexpectedValueException('Invalid segment encoding');
		}
		if (null === $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($payloadb64))) 
		{

			throw new UnexpectedValueException('Invalid segment encoding');
		}
		$sig = JWT::urlsafeB64Decode($cryptob64);
		if ($verify) 
		{
			if (empty($header->alg)) 
			{

				throw new DomainException('Empty algorithm');
			}
			if ($sig != JWT::sign("$headb64.$payloadb64", $key, $header->alg)) 
			{

				throw new UnexpectedValueException('Signature verification failed');
			}
		}
		return $payload;
	}

	public static function encode($payload, $key, $algo = 'HS256')
	{
		$header = array('typ' => 'JWT', 'alg' => $algo);

		$segments = array();
		$segments[] = JWT::urlsafeB64Encode(JWT::jsonEncode($header));
		$segments[] = JWT::urlsafeB64Encode(JWT::jsonEncode($payload));
		$signing_input = implode('.', $segments);

		$signature = JWT::sign($signing_input, $key, $algo);
		$segments[] = JWT::urlsafeB64Encode($signature);

		return implode('.', $segments);
	}

	public static function sign($msg, $key, $method = 'HS256')
	{
		$methods = array(
			'HS256' => 'sha256',
			'HS384' => 'sha384',
			'HS512' => 'sha512',
		);
		if (empty($methods[$method])) 
		{

			throw new DomainException('Algorithm not supported');
		}
		return hash_hmac($methods[$method], $msg, $key, true);
	}

	public static function jsonDecode($input)
	{
		$obj = json_decode($input);
		if (function_exists('json_last_error') && $errno = json_last_error()) 
		{
			JWT::handleJsonError($errno);
		}
		else if ($obj === null && $input !== 'null') 
		{
			throw new DomainException('Null result with non-null input');
		}
		return $obj;
	}

	public static function jsonEncode($input)
	{
		$json = json_encode($input);
		if (function_exists('json_last_error') && $errno = json_last_error()) 
		{
			JWT::handleJsonError($errno);
		}
		else if ($json === 'null' && $input !== null) 
		{
			throw new DomainException('Null result with non-null input');
		}
		return $json;
	}

	public static function urlsafeB64Decode($input)
	{
		$remainder = strlen($input) % 4;
		if ($remainder) 
		{
			$padlen = 4 - $remainder;
			$input .= str_repeat('=', $padlen);
		}
		return base64_decode(strtr($input, '-_', '+/'));
	}

	public static function urlsafeB64Encode($input)
	{
		return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
	}

	private static function handleJsonError($errno)
	{
		$messages = array(
			JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
			JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
			JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
		);
		throw new DomainException(isset($messages[$errno]) ? $messages[$errno]: 'Unknown JSON error: ' . $errno);
	}

}
