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

class Env_WebService {

	public $server = "http://test.envoimoinscher.com/"; // test environment by default

	private $serverTest = "http://test.envoimoinscher.com/";

	private $serverProd = "https://www.envoimoinscher.com/";

	private $options = array();

	private $auth = array();

	public $quotPost = array();

	public $curlError = false;

	public $curlErrorText = "";

	public $respError = false;

	public $respErrorsList = array();

	public $xpath = null;

	protected $sslCheck = array("peer" => true, "host" => 2);

	protected $getParams = "";

	protected $param;

	protected $platform = "library";

	protected $platform_version = "";

	protected $module_version = "1.1.4";

	public function __construct($auth) {
		$this->auth = $auth;
	}

	public function doRequest() {
		$req = curl_init();
		curl_setopt_array($req, $this->options);
		$result = curl_exec($req);
		$curlInfo = curl_getinfo($req);
		$contentType = explode(";", $curlInfo["content_type"]);
		if(curl_errno($req) > 0) {
			$this->curlError = true;
			$this->curlErrorText = curl_error($req);
			curl_close($req);
			return false;
		}
		elseif(trim($contentType[0]) == "text/html" && $curlInfo["http_code"] == "404") {
			$result = false;
			$this->respError = true;
			$i = 0;
			if($this->constructList) {
				$i = count($this->respErrorsList);
			}
			$this->respErrorsList[$i] = array(
				"code" => "http_file_not_found",
				"url" => $curlInfo["url"],
				"message" => "Votre requête n'a pas été correctement envoyée. Veuillez vous rassurer qu'elle
				questionne le bon serveur (https et non pas http). Si le problème persiste, contactez notre équipe de développement"
			);
		}
		curl_close($req);

		return $result;
	}

	public function setOptions($options) {
		$this->setSSLProtection();
		$this->options = array(
			CURLOPT_SSL_VERIFYPEER => $this->sslCheck['peer'], CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYHOST => $this->sslCheck['host'], CURLOPT_URL => $this->server.$options['action'].$this->getParams,
			CURLOPT_HTTPHEADER => array(
				"Authorization: ".base64_encode($this->auth['user'].":".$this->auth['pass'])."",
				"access_key : ".$this->auth['key'].""
			),
			CURLOPT_CAINFO => dirname(__FILE__).'/ca/ca-bundle.crt'
		);
	}

	private function setSSLProtection() {
		if($this->server != "https://www.envoimoinscher.com/") {
			$this->sslCheck["peer"] = false;
			$this->sslCheck["host"] = 0;
		}
	}

	public function setPost() {
		$this->param['platform'] = $this->platform;
		$this->param['platform_version'] = $this->platform_version;
		$this->param['module_version'] = $this->module_version;
		$this->options[CURLOPT_POST] = true;
		$this->options[CURLOPT_POSTFIELDS] = http_build_query($this->param);
	}

	public function setGetParams() {
		$this->param['platform'] = $this->platform;
		$this->param['platform_version'] = $this->platform_version;
		$this->param['module_version'] = $this->module_version;
		$this->getParams = '?'.http_build_query($this->param);
	}

	public function parseResponse($document) {
		$domCl = new DOMDocument();
		$domCl->loadXML($document);
		$this->xpath = new DOMXPath($domCl);
		if($this->hasErrors()) {
			$this->setResponseErrors();
		}
	}

	private function hasErrors() {
		if((int)$this->xpath->evaluate("count(/error)") > 0) {
			$this->respError = true;
			return true;
		}
		return false;
	}

	private function setResponseErrors() {
		$errors = $this->xpath->evaluate("/error");
		foreach($errors as $e => $error) {
			$this->respErrorsList[$e] = array(
				"code" => $this->xpath->evaluate(".//code")->item($e)->nodeValue,
				"message" => $this->xpath->evaluate(".//message")->item($e)->nodeValue
			);
		}
	}

	public function setEnv($env)
	{
		$envs = array('test', 'prod');
		if(in_array($env, $envs))
		{
			$var = "server".ucfirst($env);
			$this->server = $this->$var;
		}
	}

	public function setParam($param)
	{
		$this->param = $param;
	}

	public function setPlatformParams($platform,$platform_version,$module_version)
	{
		$this->platform = strtolower($platform);
		$this->platform_version = strtolower($platform_version);
		$this->module_version = strtolower($module_version);
	}

}
