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

class HikashopGeopluginInc {

	var $host = 'http://www.geoplugin.net/php.gp?ip={IP}&base_currency={CURRENCY}';

	var $currency = 'USD';

	var $ip = null;
	var $city = null;
	var $regionName = null;
	var $areaCode = null;
	var $dmaCode = null;
	var $countryCode = null;
	var $countryName = null;
	var $continentCode = null;
	var $latitude = null;
	var $longitude = null;
	var $currencyCode = null;
	var $currencySymbol = null;
	var $currencyConverter = null;

	var $timeout= 10;

	function geoPlugin() {

	}

	function locate($ip = null) {

		global $_SERVER;

		if ( is_null( $ip ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$host = str_replace( '{IP}', $ip, $this->host );
		$host = str_replace( '{CURRENCY}', $this->currency, $host );

		$data = array();

		$response = $this->fetch($host);

		if(preg_match('#O:[0-9]+:#',$response)){
			return false;
		}

		$data = unserialize($response);

		$this->ip = $ip;
		$this->city = $data['geoplugin_city'];
		$this->regionName = $data['geoplugin_region'];
		$this->areaCode = $data['geoplugin_areaCode'];
		$this->dmaCode = $data['geoplugin_dmaCode'];
		$this->countryCode = $data['geoplugin_countryCode'];
		$this->countryName = $data['geoplugin_countryName'];
		$this->continentCode = $data['geoplugin_continentCode'];
		$this->latitude = $data['geoplugin_latitude'];
		$this->longitude = $data['geoplugin_longitude'];
		$this->currencyCode = $data['geoplugin_currencyCode'];
		$this->currencySymbol = $data['geoplugin_currencySymbol'];
		$this->currencyConverter = $data['geoplugin_currencyConverter'];

	}

	function fetch($host) {

		if ( function_exists('curl_init') ) {

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'geoPlugin PHP Class v1.0');
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			curl_setopt ($ch, CURLOPT_TIMEOUT, $this->timeout);
			$response = curl_exec($ch);
			curl_close ($ch);

		} else if ( ini_get('allow_url_fopen') ) {

			$response = file_get_contents($host, 'r');

		} else {

			trigger_error ('geoPlugin class Error: Cannot retrieve data. Either compile PHP with cURL support or enable allow_url_fopen in php.ini ', E_USER_ERROR);
			return;

		}

		return $response;
	}

	function convert($amount, $float=2, $symbol=true) {

		if ( !is_numeric($this->currencyConverter) || $this->currencyConverter == 0 ) {
			trigger_error('geoPlugin class Notice: currencyConverter has no value.', E_USER_NOTICE);
			return $amount;
		}
		if ( !is_numeric($amount) ) {
			trigger_error ('geoPlugin class Warning: The amount passed to geoPlugin::convert is not numeric.', E_USER_WARNING);
			return $amount;
		}
		if ( $symbol === true ) {
			return $this->currencySymbol . round( ($amount * $this->currencyConverter), $float );
		} else {
			return round( ($amount * $this->currencyConverter), $float );
		}
	}


}

