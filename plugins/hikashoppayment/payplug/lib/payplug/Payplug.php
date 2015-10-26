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

class Payplug {

	const VERSION = "0.9";

	private static $parameters;

	public static function getConfig() {
			return self::$parameters;
	}

	public static function loadParameters($email, $password) {
		$answer;
		$configUrl = 'https://www.payplug.fr/portal/ecommerce/autoconfig';
		$curlErrNo;
		$curlErrMsg;
		$httpCode;
		$httpMsg;
		$parameters;
		$process = curl_init($configUrl);

		curl_setopt($process, CURLOPT_HEADER, true);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($process, CURLOPT_SSLVERSION, defined('CURL_SSLVERSION_TLSv1') ? CURL_SSLVERSION_TLSv1 : 1);
		curl_setopt($process, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($process, CURLOPT_SSL_VERIFYHOST, true);
		curl_setopt($process, CURLOPT_USERPWD, $email . ':' . $password);

		$answer = curl_exec($process);
		$headerSize = curl_getinfo($process, CURLINFO_HEADER_SIZE);
		$httpCode = curl_getinfo($process, CURLINFO_HTTP_CODE);

		$body = substr($answer, $headerSize);
		$headers = substr($answer, 0, $headerSize);
		$headers = explode("\r\n", $headers);

		$httpMsg = explode(" ", $headers[0], 3);

		$httpMsg = @$httpMsg[2];
		$curlErrNo = curl_errno($process);
		$curlErrMsg = curl_error($process);
		curl_close($process);

		if ($curlErrNo == 0) {
			$body = json_decode($body);

			if ($httpCode == 200) {
				$parameters = new Parameters(
					$body->currencies,
					$body->amount_max,
					$body->amount_min,
					$body->url,
					$body->payplugPublicKey,
					$body->yourPrivateKey
				);
			}
			elseif ($httpCode == 401) {
				throw new InvalidCredentialsException();
			}
			else {
				throw new NetworkException("HTTP error ($httpCode) : $httpMsg", $httpCode);
			}
		}
		else {
			throw new NetworkException("CURL error ($curlErrNo) : $curlErrMsg", $curlErrNo);
		}

		return $parameters;
	}

	public static function setConfig($parameters) {
		self::$parameters = $parameters;
	}

	public static function setConfigFromFile($path) {
		self::$parameters = Parameters::loadFromFile($path);
	}
}
