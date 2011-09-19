<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.environment.uri');
jimport('joomla.client.github.gists');
require_once JPATH_PLATFORM.'/joomla/client/github/gists.php';

/**
 * HTTP client class.
 *
 * @package     Joomla.Platform
 * @subpackage  Client
 * @since       11.1
 */
class JGithub
{
	/**
	 * Authentication Method
	 * 
	 * Possible values are 0 - no authentication, 1 - basic authentication, 2 - OAuth
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $authentication_method = 0;

	protected $gists = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Array of configuration options for the client.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		$this->http = new JHttp;
		
		if (isset($options['username']) && isset($options['password'])) {
			$this->credentials['username'] = $options['username'];
			$this->credentials['password'] = $options['password'];
			$this->authentication_method = 1;
		} elseif (isset($options['token'])) {
			$this->credentials['token'] = $options['token'];
			$this->authentication_method = 2;
		} else {
			$this->authentication_method = 0;
		}

		$this->http = curl_init();
	}

	public function __get($name)
	{
		if ($name == 'gists') {
			if ($this->gists == null) {
				$this->gists = new JGithubGists($this);
			}
			return $this->gists;
		}
	}

	public function sendRequest($url, $method = 'get', $data = array(), $options = array())
	{
		// $this->http = new JHttp;
		$curl_options = array(
			CURLOPT_URL => 'https://api.github.com'.$url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_USERAGENT => 'JGithub',
			CURLOPT_CONNECTTIMEOUT => 120,
			CURLOPT_TIMEOUT => 120,
			CURLINFO_HEADER_OUT => true
		);
		
		switch ($method) {
			case 'post':
				$curl_options[CURLOPT_POST] = true;
				$curl_options[CURLOPT_POSTFIELDS] = json_encode($data);
				break;

			case 'put':
			case 'patch':
			case 'delete':
				$curl_options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
				$curl_options[CURLOPT_POST] = false;
				$curl_options[CURLOPT_HTTPGET] = false;
				break;

			case 'get':
				$curl_options[CURLOPT_POST] = false;
				$curl_options[CURLOPT_HTTPGET] = true;
				break;
		}

		curl_setopt_array($this->http, $curl_options);

		$response = new JHttpResponse;
		$response->body = json_decode(curl_exec($this->http));
		$request_data = curl_getinfo($this->http);
		$response->headers = $request_data['request_header'];
		$response->code = $request_data['http_code'];
		
		return json_decode($response);
	}
}
