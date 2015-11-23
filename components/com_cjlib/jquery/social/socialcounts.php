<?php
/**
 * @version		$Id: socialcounts.php 01 2011-01-11 11:37:09Z maverick $
 * @package		CoreJoomla.cjlib
 * @subpackage	Components.component
 * @copyright	Copyright (C) 2009 - 2012 corejoomla.com, Inc. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die();

interface SocialNetwork
{
	public function getKey();
	public function getShareCount($url);
}

class Twitter implements SocialNetwork
{
	public function getKey(){
		
		return 'twitter';
	}

	public function getShareCount($url){
		
		$contents = file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url=' . $url);
		
		if($contents) {
			
			return json_decode($contents)->count;
		} else {
			
			return NULL;
		}
	}
}

class Facebook implements SocialNetwork {
	
	public function getKey(){
		
		return 'facebook';
	}

	public function getShareCount($url){
		
		$contents = file_get_contents("http://graph.facebook.com/fql?q=SELECT%20url,%20total_count%20FROM%20link_stat%20WHERE%20url='".$url."'");
		
		if($contents) {
			
			$json = json_decode($contents);
			return isset($json->data[0]->total_count) ? $json->data[0]->total_count : 0;
		} else {
			
			return NULL;
		}
	}
}

class GooglePlus implements SocialNetwork
{
	public function getKey(){
		
		return 'googleplus';
	}

	public function getShareCount($url){
		
		// Warning! Reverse Engineered, not an Actual API
		// http://johndyer.name/getting-counts-for-twitter-links-facebook-likesshares-and-google-1-plusones-in-c-or-php/
		// Open use license per https://twitter.com/johndyer/status/223239624498229248
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS,
			'[{' .
			'"method":"pos.plusones.get",' .
			'"id":"p",' .
			'"params":{"nolog":true,"id":"' . $url . '","source":"widget","userId":"@viewer","groupId":"@self"},' . 
			'"jsonrpc":"2.0",' .
			'"key":"p",' .
			'"apiVersion":"v1"' .
			'}]');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		$curl_results = curl_exec ($curl);
		curl_close ($curl);

		if($curl_results) {
			
			$json = json_decode($curl_results, true);
			
			if(!isset($json[0]['error'])) {
				
				return $json[0]['result']['metadata']['globalCounts']['count'];
			} else {
				
				return NULL;
			}
		} else {
			
			return NULL;
		}
	}
}

class ShareThis implements SocialNetwork {
	
	const PUB_KEY = 'a3cce920-3a6b-47a8-a890-d27d55cbc9e8';
	const ACCESS_KEY = '512db7bf2cce2acb63fad31b31067e27';

	public function getKey(){
		
		return 'sharethis';
	}

	public function getShareCount($url){
		
		$contents = file_get_contents('http://rest.sharethis.com/reach/getUrlInfo.php?url=' . $url . '&pub_key=' . self::PUB_KEY . '&access_key=' . self::ACCESS_KEY);
		
		if($contents) {
			
			$json = json_decode($contents);
			return $json->total->inbound;
		} else {
			
			return NULL;
		}
	}
}

/*
 * SocialCount
 * Returns share, like, and comment counts for various popular social networks in a single ajax request.
 *
 * Usage:
 * 	service.php?url=http://www.google.com/
 */
class SocialCount{
	
	private $url, $services = array();
	const EMPTY_RESULT = '""', REQUIRE_LOCAL_URL = FALSE;

	function __construct($url){
		
		if(empty($url)) {
			
			throw new Exception('"url" required.');
		}

		$this->url = htmlspecialchars($url);
	}

	static public function isLocalUrl( $url ){
		
		return preg_match('/^http(s?):\/\/' . $_SERVER['HTTP_HOST'] . '(:\d+)?\//', $url );
	}

	public function addNetwork(SocialNetwork $network){
		
		$this->services[] = $network;
	}

	public function toJSON() {
		
		$services = array();

		foreach($this->services as $service) {
			
			$count = $service->getShareCount($this->url);
			$services[] = '"' . $service->getKey() . '": ' . (is_null($count) ? self::EMPTY_RESULT : $count);
		}

		return '{' . implode(',', $services) . '}';
	}
}