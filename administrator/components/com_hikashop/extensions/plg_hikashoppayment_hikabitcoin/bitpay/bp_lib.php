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

require_once 'bp_options.php';

function bpLog($contents)
{
	$file = dirname(__FILE__).'/bplog.txt';
	file_put_contents($file, date('m-d H:i:s').": ", FILE_APPEND);

	if (is_array($contents))
		$contents = var_export($contents, true);
	else if (is_object($contents))
		$contents = json_encode($contents);

	file_put_contents($file, $contents."\r\n", FILE_APPEND);
}

function bpCurl($url, $apiKey, $post = false) {
	global $bpOptions;

	$curl = curl_init($url);
	$length = 0;
	if ($post)
	{
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		$length = strlen($post);
	}

	$uname = base64_encode($apiKey);
	$header = array(
		'Content-Type: application/json',
		"Content-Length: $length",
		"Authorization: Basic $uname",
		);

	curl_setopt($curl, CURLOPT_PORT, 443);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1); // verify certificate
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // check existence of CN and verify that it matches hostname
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
	curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);

	$whitelist = array("127.0.0.1","::1");
	if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	}

	$responseString = curl_exec($curl);
	if($responseString == false) {
		$response = array('error' => curl_error($curl));
	} else {
		$response = json_decode($responseString, true);
		if (!$response)
			$response = array('error' => 'invalid json: '.$responseString);
	}
	curl_close($curl);
	return $response;
}
function bpCreateInvoice($orderId, $price, $posData, $options = array()) {
	global $bpOptions;

	$options = array_merge($bpOptions, $options);	// $options override any options found in bp_options.php

	$pos = array('posData' => $posData);
	if ($bpOptions['verifyPos'])
		$pos['hash'] = bpHash(serialize($posData), $options['apiKey']);
	$options['posData'] = json_encode($pos);

	$options['orderID'] = $orderId;
	$options['price'] = $price;

	$postOptions = array('orderID', 'itemDesc', 'itemCode', 'notificationEmail', 'notificationURL', 'redirectURL',
		'posData', 'price', 'currency', 'physical', 'fullNotifications', 'transactionSpeed', 'buyerName',
		'buyerAddress1', 'buyerAddress2', 'buyerCity', 'buyerState', 'buyerZip', 'buyerEmail', 'buyerPhone');
	foreach($postOptions as $o)
		if (array_key_exists($o, $options))
			$post[$o] = $options[$o];
	$post = json_encode($post);

	$response = bpCurl('https://bitpay.com/api/invoice/', $options['apiKey'], $post);

	return $response;
}

function bpVerifyNotification($apiKey = false) {
	global $bpOptions;
	if (!$apiKey)
		$apiKey = $bpOptions['apiKey'];

	$post = file_get_contents("php://input");
	if (!$post)
		return 'No post data';

	$json = json_decode($post, true);

	if (is_string($json))
		return $json; // error

	if (!array_key_exists('posData', $json))
		return 'no posData';

	$posData = json_decode($json['posData'], true);
	if($bpOptions['verifyPos'] and $posData['hash'] != bpHash(serialize($posData['posData']), $apiKey))
		return 'authentication failed (bad hash)';
	$json['posData'] = $posData['posData'];

	return $json;
}

function bpGetInvoice($invoiceId, $apiKey=false) {
	global $bpOptions;
	if (!$apiKey)
		$apiKey = $bpOptions['apiKey'];

	$response = bpCurl('https://bitpay.com/api/invoice/'.$invoiceId, $apiKey);
	if (is_string($response))
		return $response; // error
	$response['posData'] = json_decode($response['posData'], true);
	$response['posData'] = $response['posData']['posData'];

	return $response;
}

function bpHash($data, $key) {
	$hmac = base64_encode(hash_hmac('sha256', $data, $key, TRUE));
	return strtr($hmac, array('+' => '-', '/' => '_', '=' => ''));
}
