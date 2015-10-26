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
class plgHikashoppaymentAmazon extends hikashopPaymentPlugin {
	var $accepted_currencies = array('USD');

	var $multiple = true;
	var $name = 'amazon';
	var $doc_form = 'amazon';
	var $pluginConfig = array(
		'merchant_Key' => array('AWS_KEY_ID', 'input'),
		'secret_Key' => array('SECRET_KEY', 'input'),
		'debug' => array('DEBUG', 'boolean','0'),
		'environnement' => array('ENVIRONNEMENT', 'list',array(
			'production' => 'HIKA_PRODUCTION',
			'sandbox' => 'HIKA_SANDBOX'
		)),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
		'return_url' => array('RETURN_URL', 'input'),
	);

	function onBeforeOrderCreate(&$order,&$do){
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(empty($this->payment_params->secret_Key) || empty($this->payment_params->merchant_Key)) {
			$this->app->enqueueMessage('Please check your &quot;Amazon&quot; plugin configuration');
			$do = false;
		}
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$tax_total = '';
		$discount_total = '';

		$notify_url = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=amazon&tmpl=component&lang=' . $this->locale . $this->url_itemid;
		if (!isset($this->payment_params->no_shipping))
			$this->payment_params->no_shipping = 1;
		if (!empty($this->payment_params->rm))
			$this->payment_params->rm = 2;

		$host = 'authorize.payments-sandbox.amazon.com';
		$path = '/cobranded-ui/actions/start';
		if ($this->payment_params->environnement == 'production'){
			$PayUrl = 'https://authorize.payments.amazon.com/cobranded-ui/actions/start';
			$parsedUrl = parse_url($PayUrl);
		}
		if ($this->payment_params->environnement == 'sandbox'){
			$PayUrl = 'https://authorize.payments-sandbox.amazon.com/cobranded-ui/actions/start';
			$parsedUrl = parse_url($PayUrl);
		}
		$vars = array('signatureMethod' => 'HmacSHA256',
		 'signatureVersion' => '2',
		 'currencyCode' => $this->currency->currency_code,
		 'callerKey' => $this->payment_params->merchant_Key,
		 'callerReference' => $order->order_id,
		 'pipelineName' => 'SingleUse',
		 'returnUrl' => $notify_url,
		 'transactionAmount' => round($order->order_full_price,3),
		 'version' => '2009-01-09', );
		ksort($vars);
		$vars2 = array_map('rawurlencode', $vars);

		$paramStringArray = array();
		foreach ($vars2 as $key => $value) {
			$paramStringArray[] = $key . '=' . $value;
		}
		$paramString = implode('&', $paramStringArray);
		$string_to_sign = 'POST' . "\n" . $parsedUrl['host'] . "\n" . $parsedUrl['path'] . "\n" . $paramString;

		$signature = base64_encode(hash_hmac('sha256', $string_to_sign, $this->payment_params->secret_Key, true));
		$vars["signature"] = $signature;
		ksort($vars);
		$this->payment_params->url = $PayUrl;
		$this->vars = $vars;
		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses) {
		$vars = array();
		$data = array();
		$filter = JFilterInput::getInstance();
		$app =& JFactory::getApplication();
		$httpsHikashop = HIKASHOP_LIVE;
		foreach ($_REQUEST as $key => $value) {
			$key = $filter->clean($key);
			if (preg_match("#^[0-9a-z_-]{1,30}$#i", $key) && !preg_match("#^cmd$#i", $key)  && $key != 'task' && $key != 'tmpl' && $key != 'Itemid' && $key != 'notif_payment' && $key != 'ctrl' && $key != 'lang' && $key != 'option' && $key != 'hikashop_front_end_main' && $key != 'view') {
				$value = JRequest::getString($key);
				$vars[$key] = $value;
				$data[] = $key . '=' . urlencode($value);
			}
		}
		$dbOrder = $this->getOrder((int)@$vars['callerReference']);
		$this->loadPaymentParams($dbOrder);
		if ($this->payment_params->debug) {
			echo "<br/>---------------------- REQUEST -------------------------------------";
			foreach ($vars as $key => $value) {
				echo "$key = $value <br/>";
			}
			echo "<br/>------------------ EO REQUEST ----------------------------------------";
		}
		$data = implode('&', $data) . '&cmd=_notify-validate';
		$user = hikashop_loadUser(true);
		$lang = JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'), 0, 2));
		global $Itemid;
		$url_itemid = '';
		if (!empty($Itemid)) {
			$url_itemid = '&Itemid=' . $Itemid;
		}

		$paramStringArray = array();
		foreach ($vars as $key => $value) {
				$paramStringArray[] = str_replace('%7E', '~', rawurlencode($key)) . '=' . str_replace('%7E', '~', rawurlencode($value));
		}
		$http_param = '';
		$http_param = implode('&', $paramStringArray);
		if ($this->payment_params->environnement == 'production'){
			$curlUrl = 'https://fps.amazonaws.com';
			$parsedUrl = parse_url($curlUrl);
		}
		if ($this->payment_params->environnement == 'sandbox'){
			$curlUrl = 'https://fps.sandbox.amazonaws.com';
			$parsedUrl = parse_url($curlUrl);
		}
		$Timestamp = gmdate("Y-m-d\TH:i:s\Z");
		$urlEndPoint = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=amazon&tmpl=component&lang=' . $locale . $url_itemid;
		$vars_signVerif= array (
			"Action"=> "VerifySignature",
			"UrlEndPoint"=> $urlEndPoint,
			"HttpParameters"=>$http_param,
			"AWSAccessKeyId"=> $this->payment_params->merchant_Key,
			"Timestamp" => $Timestamp ,
			"Version" => "2010-08-28" ,
			"SignatureVersion" => 2 ,
			"SignatureMethod" => "HmacSHA256" ,
		);
		uksort($vars_signVerif, 'strcmp');

		$paramStringArray = array();
		foreach ($vars_signVerif as $key => $value) {
				$paramStringArray[] = $key . '=' . str_replace('%7E', '~', rawurlencode($value));
		}
		$paramString = '';
		$paramString = implode('&', $paramStringArray);
		$string_to_sign = 'GET' . "\n" . $parsedUrl['host'] . "\n" . '/' . "\n" . $paramString;

		$signature = base64_encode(hash_hmac('sha256', $string_to_sign, $this->payment_params->secret_Key, true));
		$paramString.="&Signature=". $signature;
		$vars_signVerif["Signature"] = $signature;
		if ($this->payment_params->debug) {
			echo "<br/>---------------------- VARS SIGN VERIF  -------------------------------------";
			foreach ($vars_signVerif as $key => $value) {
				echo "$key = $value <br/>";
			}
			$curlUrl .= '/?' . $paramString;
			echo "<br/> Curl URL : <br/> $curlUrl <br/>";
			echo "<br/>------------------ EO VAR SIGN VERIF -----------------------------------------";
		}
		$session = curl_init();
		curl_setopt($session, CURLOPT_URL, $curlUrl);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($session, CURLOPT_VERBOSE, 1);
		curl_setopt($session, CURLOPT_POST, 0);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($session);
		$error = curl_errno($session);
		$err_msg = curl_error($session);

		curl_close($session);
		$VerificationStatus = $this->getTagValue($result,'VerificationStatus');
		if ($this->payment_params->debug) {
			echo "<br/>---------------------- Curl Result SIGN -------------------------------------<br/>";
			echo"CURL RESULT :<br/>";
			var_dump($result);
			echo "Transaction Status : $VerificationStatus";
			echo "<br/>------------------ EO Curl Result sign -----------------------------------------";
		}

		if($VerificationStatus == 'Success' && ($vars['status'] == 'SA' || $vars['status'] == 'SB' || $vars['status'] == 'SC')){
			$currencyClass = hikashop_get('class.currency');
			$currencies = null;
			$currencies = $currencyClass->getCurrencies($dbOrder->order_currency_id,$currencies);
			$currency = $currencies[$dbOrder->order_currency_id];

			if (empty($dbOrder)) {
				if ($this->payment_params->debug) {
					echo "Could not load any order for your notification " . $vars['orderID'] . "NO ORDER ID <br/>";
				}
				return false;
			}
			if ($this->payment_params->environnement == 'production'){
				$curlUrl = 'https://fps.amazonaws.com';
				$parsedUrl = parse_url($curlUrl);
			}
			if ($this->payment_params->environnement == 'sandbox'){
				$curlUrl = 'https://fps.sandbox.amazonaws.com';
				$parsedUrl = parse_url($curlUrl);
			}
			$Timestamp = gmdate("Y-m-d\TH:i:s\Z");
			$vars_request= array (
				"Action" => "Pay" ,
				"AWSAccessKeyId"=> $this->payment_params->merchant_Key,
				"CallerDescription"=> "hikashop-amazon",
				"CallerReference"=> $vars['callerReference'],
				"SenderTokenId"=> $vars['tokenID'],
				"SignatureMethod" =>"HmacSHA256" ,
				"SignatureVersion" => 2 ,
				"Timestamp" => $Timestamp ,
				"TransactionAmount.CurrencyCode" => $currency->currency_code ,
				"TransactionAmount.Value" => round($dbOrder->order_full_price,3) ,
				"Version" => "2008-09-17" ,
			);
			ksort($vars_request);
			$vars_sign = array_map('rawurlencode', $vars_request);

			$paramStringArray = array();
			foreach ($vars_sign as $key => $value) {
				$paramStringArray[] = $key . '=' . $value;
			}

			$paramString = implode('&', $paramStringArray);

			$string_to_sign = 'POST' . "\n" . $parsedUrl['host'] . "\n" . '/' . "\n" . $paramString;

			$signature = base64_encode(hash_hmac('sha256', $string_to_sign, $this->payment_params->secret_Key, true));
			$vars_request["Signature"] = $signature;
			ksort($vars_request);
			if ($this->payment_params->debug) {
				echo "<br/>---------------------- VARS PAY  -------------------------------------";
				foreach ($vars_request as $key => $value) {
					echo "$key = $value <br/>";
				}
				echo "<br/> $paramString";
				echo "<br/>------------------ EO VARS PAY -----------------------------------------";
			}
			$session = curl_init($curlUrl);
			curl_setopt($session, CURLOPT_URL, $curlUrl);
			curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($session, CURLOPT_VERBOSE, 1);
			curl_setopt($session, CURLOPT_POST, 1);
			curl_setopt($session, CURLOPT_POSTFIELDS, str_replace('+', '%20', http_build_query($vars_request, '', '&')));
			curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);

			$result = curl_exec($session);
			$error = curl_errno($session);
			$err_msg = curl_error($session);

			curl_close($session);
			if ($this->payment_params->debug) {
				echo "<br/>---------------------- Curl Result  -------------------------------------<br/>";
				echo"CURL RESULT : <br/>";
				var_dump($result);
				echo "<br/>------------------ EO Curl Result -----------------------------------------";
			}
			$TransactionId = $this->getTagValue($result,'TransactionId');
			$TransactionStatus = $this->getTagValue($result,'TransactionStatus');

			$url = HIKASHOP_LIVE . 'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . $vars['callerReference'];
			$order_text = "\r\n" . JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE);
			$order_text .= "\r\n" . str_replace('<br/>', "\r\n", JText::sprintf('ACCESS_ORDER_WITH_LINK', $url));
			if ($this->payment_params->debug) {
				echo print_r($dbOrder, true) . "\n\n\n";
			}

			$return_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$vars['callerReference'].$url_itemid;
			$cancel_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$vars['callerReference'].$url_itemid;
			if ($TransactionStatus == 'Success' || $TransactionStatus == 'Pending') {
				if ($this->payment_params->debug) {
					echo "---------------------------------------NOTIFY OK----------------------------------------<br/>";
				}
				$history = new stdClass();
				$email = new stdClass();

				$history->notified = 1;
				$history->amount = $result['AMOUNT'];
				$history->data = ob_get_clean().'/r/n'.$vars['tokenID'];

				$order_status = $this->payment_params->verified_status;
				if ($dbOrder->order_status == $order_status)
					return true;

				$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'Amazon', $TransactionStatus, $dbOrder->order_number);
				$body = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', 'Amazon', $TransactionStatus)) . ' ' . JText::sprintf('ORDER_STATUS_CHANGED', $order_status) . "\r\n\r\n" . $order_text;
				$email->body = $body;
				$this->modifyOrder($vars['callerReference'], $this->payment_params->verified_status, $history, $email);
				$app->redirect($return_url);
				return true;
			} else {
				$email = new stdClass();
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', $this->name).'invalid response';
				$email->body = JText::sprintf("Hello,\r\n A Postfinance notification was refused because the response from the Post finance server was invalid")."\r\n\r\n".$order_text;
				$history = new stdClass();

				$this->modifyOrder($vars['callerReference'], $this->payment_params->invalid_status, $history, $email);

				if($element->payment_params->debug){
					echo 'invalid response'."\n\n\n";
				}
				$app->enqueueMessage('Transaction Failed');
				$app->redirect($cancel_url);
				return false;
			}
		}
	}
	function onPaymentConfiguration(&$element){
		parent::onPaymentConfiguration($element);
	}
	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'Amazon';
		$element->payment_description = 'You can pay by credit card or Amazon using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,Amazon';

		$element->payment_params->merchant_Key = '';
		$element->payment_params->merchant_Token = '';
		$element->payment_params->debug=false;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->verified_status='confirmed';
		$element->payment_params->pending_status='created';
	}

	function onPaymentConfigurationSave(&$element) {
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
		return true;
	}
	function getTagValue($string, $tagname) {
			$pattern = "#<$tagname>(.*)</$tagname>#";
			preg_match($pattern, $string, $matches);
			if(isset($matches[1])){
				return $matches[1];
			}else{
				return 'Failed';
			}
	}
}
