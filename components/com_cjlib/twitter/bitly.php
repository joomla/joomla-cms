<?php
/**
 * Bitly class
 *
 * This source file can be used to communicate with Bit.ly (http://bit.ly)
 *
 * The class is documented in the file itself. If you find any bugs help me out and report them. Reporting can be done by sending an email to php-bitly-bugs[at]verkoyen[dot]eu.
 * If you report a bug, make sure you give me enough information (include your code).
 *
 * Changelog since 1.0.0
 * - corrected some documentation
 * - wrote some explanation for the method-parameters
 *
 * License
 * Copyright (c) 2009, Tijs Verkoyen. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products derived from this software without specific prior written permission.
 *
 * This software is provided by the author "as is" and any express or implied warranties, including, but not limited to, the implied warranties of merchantability and fitness for a particular purpose are disclaimed. In no event shall the author be liable for any direct, indirect, incidental, special, exemplary, or consequential damages (including, but not limited to, procurement of substitute goods or services; loss of use, data, or profits; or business interruption) however caused and on any theory of liability, whether in contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of this software, even if advised of the possibility of such damage.
 *
 * @author			Tijs Verkoyen <php-bitly@verkoyen.eu>
 * @version			1.0.1
 *
 * @copyright		Copyright (c) 2008, Tijs Verkoyen. All rights reserved.
 * @license			BSD License
 */
defined('_JEXEC') or die('Restricted access');

class Bitly
{
	// internal constant to enable/disable debugging
	const DEBUG = false;

	// url for the bitly-api
	const API_URL = 'http://api.bit.ly';

	// port for the bitly-API
	const API_PORT = 80;

	// bitly-API version
	const API_VERSION = '2.0.1';

	// current version
	const VERSION = '1.0.1';


	/**
	 * The API-key that will be used for authenticating
	 *
	 * @var	string
	 */
	private $apiKey;


	/**
	 * The login that will be used for authenticating
	 *
	 * @var	string
	 */
	private $login;


	/**
	 * The timeout
	 *
	 * @var	int
	 */
	private $timeOut = 60;


	/**
	 * The user agent
	 *
	 * @var	string
	 */
	private $userAgent;


// class methods
	/**
	 * Default constructor
	 *
	 * @return	void
	 * @param	string $login	The login (username) that has to be used for authenticating
	 * @param	string $apiKey	The API-key that has to be used for authentication (see http://bit.ly/account)
	 */
	public function __construct($login, $apiKey)
	{
		$this->setLogin($login);
		$this->setApiKey($apiKey);
	}


	/**
	 * Make the call
	 *
	 * @return	string
	 * @param	string $url
	 * @param	array[optional] $aParameters
	 */
	private function doCall($url, $aParameters = array())
	{
		// redefine
		$url = (string) $url;
		$aParameters = (array) $aParameters;

		// add required parameters
		$aParameters['login'] = $this->getLogin();
		$aParameters['apiKey'] = $this->getApiKey();
		$aParameters['version'] = self::API_VERSION;

		// init var
		$queryString = '';

		// loop parameters and add them to the queryString
		foreach($aParameters as $key => $value) $queryString .= '&'. $key .'='. urlencode(utf8_encode($value));

		// cleanup querystring
		$queryString = trim($queryString, '&');

		// append to url
		$url .= '?'. $queryString;

		// prepend
		$url = self::API_URL .'/'. $url;

		// set options
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_PORT] = self::API_PORT;
		$options[CURLOPT_USERAGENT] = $this->getUserAgent();
		$options[CURLOPT_HTTPGET] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_TIMEOUT] = (int) $this->getTimeOut();

		// init
		$curl = curl_init();

		// set options
		curl_setopt_array($curl, $options);

		// execute
		$response = curl_exec($curl);
		$headers = curl_getinfo($curl);

		// fetch errors
		$errorNumber = curl_errno($curl);
		$errorMessage = curl_error($curl);

		// close
		curl_close($curl);

		// invalid headers
		if(!in_array($headers['http_code'], array(0, 200)))
		{
			// should we provide debug information
			if(self::DEBUG)
			{
				// make it output proper
				echo '<pre>';

				// end proper format
				echo '</pre>';

				// stop the script
				exit;
			}

			// throw error
			throw new BitlyException('Invalid headers ('. $headers['http_code'] .')', (int) $headers['http_code']);
		}

		// error?
		if($errorNumber != '') throw new BitlyException($errorMessage, $errorNumber);

		// we expect JSON so decode it
		$json = @json_decode($response, true);

		// validate json
		if($json === false) throw new BitlyException('Invalid JSON-response');

		// is error?
		if(!isset($json['statusCode']) || (string) $json['statusCode'] != 'OK')
		{
			// bitly-error?
			if(isset($json['errorCode']) && isset($json['errorMessage'])) throw new BitlyException((string) $json['errorMessage'], (int) $json['errorCode']);

			// invalid json?
			else throw new BitlyException('Invalid JSON-response');
		}

		// return
		return $json;
	}


	/**
	 * Get the APIkey
	 *
	 * @return	string
	 */
	private function getApiKey()
	{
		return (string) $this->apiKey;
	}


	/**
	 * Get the login
	 *
	 * @return	string
	 */
	private function getLogin()
	{
		return (string) $this->login;
	}


	/**
	 * Get the timeout that will be used
	 *
	 * @return	int
	 */
	public function getTimeOut()
	{
		return (int) $this->timeOut;
	}


	/**
	 * Get the useragent that will be used. Our version will be prepended to yours.
	 * It will look like: "PHP Akismet/<version> <your-user-agent>"
	 *
	 * @return	string
	 */
	public function getUserAgent()
	{
		return (string) 'PHP Bitly/'. self::VERSION .' '. $this->userAgent;
	}


	/**
	 * Set the API-key that has to be used
	 *
	 * @return	void
	 * @param	string $apiKey
	 */
	private function setApiKey($apiKey)
	{
		$this->apiKey = (string) $apiKey;
	}


	/**
	 * Set the login that has to be used
	 *
	 * @return	void
	 * @param	string $login
	 */
	private function setLogin($login)
	{
		$this->login = (string) $login;
	}


	/**
	 * Set the timeout
	 * After this time the request will stop. You should handle any errors triggered by this.
	 *
	 * @return	void
	 * @param	int $seconds	The timeout in seconds
	 */
	public function setTimeOut($seconds)
	{
		$this->timeOut = (int) $seconds;
	}


	/**
	 * Set the user-agent for you application
	 * It will be appended to ours, the result will look like: "PHP Akismet/<version> <your-user-agent>"
	 *
	 * @return	void
	 * @param	string $userAgent	Your user-agent, it should look like <app-name>/<app-version>
	 */
	public function setUserAgent($userAgent)
	{
		$this->userAgent = (string) $userAgent;
	}


// url methods
	/**
	 * Get a list of bit.ly API error codes.
	 *
	 * @return	array
	 */
	public function errors()
	{
		// make the call
		$response = $this->doCall('errors', null);

		// validate
		if(isset($response['results'])) return (array) $response['results'];

		// fallbak
		return false;
	}


	/**
	 * Given a bit.ly url or hash return long source url
	 *
	 * @return	string
	 * @param	string $shortUrlOrHash	A bit.ly-url (eg: http://bit.ly/1RmnUT) or hash (eg: 1RmnUT)
	 */
	public function expand($shortUrlOrHash)
	{
		// calculate hash
		$hash = str_replace('http://bit.ly/', '', (string) $shortUrlOrHash);

		// make the call
		$parameters['hash'] = $hash;

		// make the call
		$response = $this->doCall('expand', $parameters);

		// validate
		if(isset($response['results'][$hash]['longUrl'])) return (string) $response['results'][$hash]['longUrl'];

		// fallbak
		return false;
	}


	/**
	 * Given a bit.ly url or hash, return information about that page, such as the long source url, ...
	 *
	 * @return	array
	 * @param	string $shortUrlOrHash	A bit.ly-url (eg: http://bit.ly/1RmnUT) or hash (eg: 1RmnUT)
	 */
	public function info($shortUrlOrHash)
	{
		// calculate hash
		$hash = str_replace('http://bit.ly/', '', (string) $shortUrlOrHash);

		// make the call
		$parameters['hash'] = $hash;

		// make the call
		$response = $this->doCall('info', $parameters);

		// validate
		if(isset($response['results'][$hash])) return (array) $response['results'][$hash];

		// fallbak
		return false;
	}


	/**
	 * Given a long url, returns a shorter one.
	 *
	 * @return	string
	 * @param	string $url	    A long URL to shorten, eg: http://betaworks.com
	 * @param	bool[optional] $publishToHistory	Should this url be published into your history? Default is true
	 */
	public function shorten($url, $publishToHistory = true)
	{
		// redefine
		$parameters['longUrl'] = (string) $url;
		if((bool) $publishToHistory) $parameters['history'] = 1;

		// make the call
		$response = $this->doCall('shorten', $parameters);

		// validate
		if(isset($response['results'][$url]['shortUrl'])) return (string) $response['results'][$url]['shortUrl'];

		// fallback
		return false;
	}


	/**
	 * Given a bit.ly url or hash, return traffic and referrer data.
	 *
	 * @return	array
	 * @param	string $shortUrlOrHash	A bit.ly-url (eg: http://bit.ly/1RmnUT) or hash (eg: 1RmnUT)
	 */
	public function stats($shortUrlOrHash)
	{
		// calculate hash
		$hash = str_replace('http://bit.ly/', '', (string) $shortUrlOrHash);

		// make the call
		$parameters['hash'] = $hash;

		// make the call
		$response = $this->doCall('stats', $parameters);

		// validate
		if(isset($response['results'])) return (array) $response['results'];

		// fallbak
		return false;
	}
}


/**
 * Bitly Exception class
 *
 * @author	Tijs Verkoyen <php-bitly@verkoyen.eu>
 */
class BitlyException extends Exception
{
}

?>