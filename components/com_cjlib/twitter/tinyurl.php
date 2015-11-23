<?php
/**
 * TinyUrl class
 *
 * This source file can be used to communicate with TinyURL.com (http://tinyurl.com)
 *
 * The class is documented in the file itself. If you find any bugs help me out and report them. Reporting can be done by sending an email to php-tinyurl-bugs[at]verkoyen[dot]eu.
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
 * @author		Tijs Verkoyen <php-tinyurl@verkoyen.eu>
 * @version		1.0.1
 *
 * @copyright	Copyright (c) 2008, Tijs Verkoyen. All rights reserved.
 * @license		BSD License
 */

defined('_JEXEC') or die('Restricted access');

class TinyUrl
{
	// internal constant to enable/disable debugging
	const DEBUG = false;

	// url for the twitter-api
	const API_URL = 'http://tinyurl.com';

	// port for the tinyUrl-API
	const API_PORT = 80;

	// current version
	const VERSION = '1.0.1';


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
	 */
	public function __construct()
	{
		// nothing to do
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

		// rebuild url if we don't use post
		if(!empty($aParameters))
		{
			// init var
			$queryString = '';

			// loop parameters and add them to the queryString
			foreach($aParameters as $key => $value) $queryString .= '&'. $key .'='. urlencode(utf8_encode($value));

			// cleanup querystring
			$queryString = trim($queryString, '&');

			// append to url
			$url .= '?'. $queryString;
		}

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
			throw new TinyUrlException(null, (int) $headers['http_code']);
		}

		// error?
		if($errorNumber != '') throw new TinyUrlException($errorMessage, $errorNumber);

		// return
		return $response;
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
	 * It will look like: "PHP Tinyurl/<version> <your-user-agent>"
	 *
	 * @return	string
	 */
	public function getUserAgent()
	{
		return (string) 'PHP TinyUrl/'. self::VERSION .' '. $this->userAgent;
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
	 * It will be appended to ours, the result will look like: "PHP TinyUrl/<version> <your-user-agent>"
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
	 * Create a TinyUrl
	 *
	 * @return	string
	 * @param	string $url	The orginal url that should be shortened
	 */
	public function create($url)
	{
		// redefine
		$url = (string) $url;

		// build parameters
		$aParameters['url'] = $url;

		// make the call
		return (string) $this->doCall(self::API_URL .'/api-create.php', $aParameters);
	}


	/**
	 * Reverse a TinyUrl into a real url
	 *
	 * @return	mixed	If something fails it will return false, otherwise the orginal url will be returned as a string
	 * @param	string $url	The short tinyUrl that should be reversed
	 */
	public function reverse($url)
	{
		// redefine
		$url = (string) $url;

		// explode on .com
		$aChunks = explode('tinyurl.com/', $url);

		if(isset($aChunks[1]))
		{
			// rebuild url
			$requestUrl = 'http://preview.tinyurl.com/'.$aChunks[1];

			// make the call
			$response = $this->doCall($requestUrl);

			// init var
			$aMatches = array();

			// match
			preg_match('/redirecturl" href="(.*)">/', $response, $aMatches);

			// return if something was found
			if(isset($aMatches[1])) return (string) $aMatches[1];
		}

		// fallback
		return false;
	}
}


/**
 * TinyUrl Exception class
 *
 * @author		Tijs Verkoyen <php-tinyurl@verkoyen.eu>
 */
class TinyUrlException extends Exception
{
	/**
	 * Http header-codes
	 *
	 * @var	array
	 */
	private $aStatusCodes = array(100 => 'Continue',
									101 => 'Switching Protocols',
									200 => 'OK',
									201 => 'Created',
									202 => 'Accepted',
									203 => 'Non-Authoritative Information',
									204 => 'No Content',
									205 => 'Reset Content',
									206 => 'Partial Content',
									300 => 'Multiple Choices',
									301 => 'Moved Permanently',
									301 => 'Status code is received in response to a request other than GET or HEAD, the user agent MUST NOT automatically redirect the request unless it can be confirmed by the user, since this might change the conditions under which the request was issued.',
									302 => 'Found',
									302 => 'Status code is received in response to a request other than GET or HEAD, the user agent MUST NOT automatically redirect the request unless it can be confirmed by the user, since this might change the conditions under which the request was issued.',
									303 => 'See Other',
									304 => 'Not Modified',
									305 => 'Use Proxy',
									306 => '(Unused)',
									307 => 'Temporary Redirect',
									400 => 'Bad Request',
									401 => 'Unauthorized',
									402 => 'Payment Required',
									403 => 'Forbidden',
									404 => 'Not Found',
									405 => 'Method Not Allowed',
									406 => 'Not Acceptable',
									407 => 'Proxy Authentication Required',
									408 => 'Request Timeout',
									409 => 'Conflict',
									411 => 'Length Required',
									412 => 'Precondition Failed',
									413 => 'Request Entity Too Large',
									414 => 'Request-URI Too Long',
									415 => 'Unsupported Media Type',
									416 => 'Requested Range Not Satisfiable',
									417 => 'Expectation Failed',
									500 => 'Internal Server Error',
									501 => 'Not Implemented',
									502 => 'Bad Gateway',
									503 => 'Service Unavailable',
									504 => 'Gateway Timeout',
									505 => 'HTTP Version Not Supported');


	/**
	 * Default constructor
	 *
	 * @return	void
	 * @param	string[optional] $message
	 * @param	int[optional] $code
	 */
	public function __construct($message = null, $code = null)
	{
		// set message
		if($message === null && isset($this->aStatusCodes[(int) $code])) $message = $this->aStatusCodes[(int) $code];

		// call parent
		parent::__construct((string) $message, $code);
	}
}

?>