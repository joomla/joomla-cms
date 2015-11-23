<?php
defined('_JEXEC') or die('Restricted access');

/**
 * Twitter class
 *
 * This source file can be used to communicate with Twitter (http://twitter.com)
 *
 * The class is documented in the file itself. If you find any bugs help me out and report them. Reporting can be done by sending an email to php-twitter-bugs[at]verkoyen[dot]eu.
 * If you report a bug, make sure you give me enough information (include your code).
 *
 * Changelog since 2.1.1
 * - code styling
 * - no more converting to integer for the cursor (thx to Jamaica)
 *
 * Changelog since 2.1.0
 * - fixed issue with generation of basestring
 * - added a new method: http://dev.twitter.com/doc/post/:user/:list_id/create_all
 *
 * Changelog since 2.0.3
 * - made a lot of changes to reflect the current API, some of the methods aren't backwards compatible, so be carefull before upgrading
 *
 * Changelog since 2.0.2
 * - tested geo*
 * - implemented accountUpdateProfileImage
 * - implemented accountUpdateProfileBackgroundImage
 * - fixed issue with GET and POST (thx to Luiz Felipe)
 * - added a way to detect open_basedir (thx to Lee Kindness)
 *
 * Changelog since 2.0.1
 * - Fixed some documentation
 * - Added a new method: usersProfileImage
 * - Fixed trendsLocation
 * - Added new GEO-methods: geoSearch, geoSimilarPlaces, geoPlaceCreate (not tested because geo-services were disabled.)
 * - Added legalToS
 * - Added legalPrivacy
 * - Fixed helpTest
 *
 * Changelog since 2.0.0
 * - no more fatal if twitter is over capacity
 * - fix for calculating the header-string (thx to Dextro)
 * - fix for userListsIdStatuses (thx to Josh)
 *
 * License
 * Copyright (c) 2010, Tijs Verkoyen. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products derived from this software without specific prior written permission.
 *
 * This software is provided by the author "as is" and any express or implied warranties, including, but not limited to, the implied warranties of merchantability and fitness for a particular purpose are disclaimed. In no event shall the author be liable for any direct, indirect, incidental, special, exemplary, or consequential damages (including, but not limited to, procurement of substitute goods or services; loss of use, data, or profits; or business interruption) however caused and on any theory of liability, whether in contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of this software, even if advised of the possibility of such damage.
 *
 * @author		Tijs Verkoyen <php-twitter@verkoyen.eu>
 * @version		2.1.2
 *
 * @copyright	Copyright (c) 2010, Tijs Verkoyen. All rights reserved.
 * @license		BSD License
 */
class Twitter
{
	// internal constant to enable/disable debugging
	const DEBUG = false;

	// url for the twitter-api
	const API_URL = 'https://api.twitter.com/1';
	const SEARCH_API_URL = 'https://search.twitter.com';
	const SECURE_API_URL = 'https://api.twitter.com';

	// port for the twitter-api
	const API_PORT = 443;
	const SEARCH_API_PORT = 443;
	const SECURE_API_PORT = 443;

	// current version
	const VERSION = '2.1.2';


	/**
	 * A cURL instance
	 *
	 * @var	resource
	 */
	private $curl;


	/**
	 * The consumer key
	 *
	 * @var	string
	 */
	private $consumerKey;


	/**
	 * The consumer secret
	 *
	 * @var	string
	 */
	private $consumerSecret;


	/**
	 * The oAuth-token
	 *
	 * @var	string
	 */
	private $oAuthToken = '';


	/**
	 * The oAuth-token-secret
	 *
	 * @var	string
	 */
	private $oAuthTokenSecret = '';


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
	 * @param	string $consumerKey		The consumer key to use.
	 * @param	string $consumerSecret	The consumer secret to use.
	 */
	public function __construct($consumerKey, $consumerSecret)
	{
		$this->setConsumerKey($consumerKey);
		$this->setConsumerSecret($consumerSecret);
	}


	/**
	 * Default destructor
	 *
	 * @return	void
	 */
	public function __destruct()
	{
		if($this->curl != null) curl_close($this->curl);
	}


	/**
	 * Format the parameters as a querystring
	 *
	 * @return	string
	 * @param	array $parameters	The parameters.
	 */
	private function buildQuery(array $parameters)
	{
		// no parameters?
		if(empty($parameters)) return '';

		// encode the keys
		$keys = self::urlencode_rfc3986(array_keys($parameters));

		// encode the values
		$values = self::urlencode_rfc3986(array_values($parameters));

		// reset the parameters
		$parameters = array_combine($keys, $values);

		// sort parameters by key
		uksort($parameters, 'strcmp');

		// loop parameters
		foreach($parameters as $key => $value)
		{
			// sort by value
			if(is_array($value)) $parameters[$key] = natsort($value);
		}

		// process parameters
		foreach($parameters as $key => $value) $chunks[] = $key . '=' . str_replace('%25', '%', $value);

		// return
		return implode('&', $chunks);
	}


	/**
	 * All OAuth 1.0 requests use the same basic algorithm for creating a signature base string and a signature.
	 * The signature base string is composed of the HTTP method being used, followed by an ampersand ("&") and then the URL-encoded base URL being accessed,
	 * complete with path (but not query parameters), followed by an ampersand ("&").
	 * Then, you take all query parameters and POST body parameters (when the POST body is of the URL-encoded type, otherwise the POST body is ignored),
	 * including the OAuth parameters necessary for negotiation with the request at hand, and sort them in lexicographical order by first parameter name and
	 * then parameter value (for duplicate parameters), all the while ensuring that both the key and the value for each parameter are URL encoded in isolation.
	 * Instead of using the equals ("=") sign to mark the key/value relationship, you use the URL-encoded form of "%3D". Each parameter is then joined by the
	 * URL-escaped ampersand sign, "%26".
	 *
	 * @return	string
	 * @param	string $url			The URL.
	 * @param	string $method		The method to use.
	 * @param	array $parameters	The parameters.
	 */
	private function calculateBaseString($url, $method, array $parameters)
	{
		// redefine
		$url = (string) $url;
		$parameters = (array) $parameters;

		// init var
		$pairs = array();
		$chunks = array();

		// sort parameters by key
		uksort($parameters, 'strcmp');

		// loop parameters
		foreach($parameters as $key => $value)
		{
			// sort by value
			if(is_array($value)) $parameters[$key] = natsort($value);
		}

		// process queries
		foreach($parameters as $key => $value)
		{
			// only add if not already in the url
			if(substr_count($url, $key . '=' . $value) == 0) $chunks[] = self::urlencode_rfc3986($key) . '%3D' . self::urlencode_rfc3986($value);
		}

		// buils base
		$base = $method . '&';
		$base .= urlencode($url);
		$base .= (substr_count($url, '?')) ? '%26' : '&';
		$base .= implode('%26', $chunks);
		$base = str_replace('%3F', '&', $base);

		// return
		return $base;
	}


	/**
	 * Build the Authorization header
	 * @later: fix me
	 *
	 * @return	string
	 * @param	array $parameters	The parameters.
	 * @param	string $url			The URL.
	 */
	private function calculateHeader(array $parameters, $url)
	{
		// redefine
		$url = (string) $url;

		// divide into parts
		$parts = parse_url($url);

		// init var
		$chunks = array();

		// process queries
		foreach($parameters as $key => $value) $chunks[] = str_replace('%25', '%', self::urlencode_rfc3986($key) . '="' . self::urlencode_rfc3986($value) . '"');

		// build return
		$return = 'Authorization: OAuth realm="' . $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . '", ';
		$return .= implode(',', $chunks);

		// prepend name and OAuth part
		return $return;
	}


	/**
	 * Make an call to the oAuth
	 * @todo	refactor me
	 *
	 * @return	array
	 * @param	string $method					The method.
	 * @param	array[optional] $parameters		The parameters.
	 */
	private function doOAuthCall($method, array $parameters = null)
	{
		// redefine
		$method = (string) $method;

		// append default parameters
		$parameters['oauth_consumer_key'] = $this->getConsumerKey();
		$parameters['oauth_nonce'] = md5(microtime() . rand());
		$parameters['oauth_timestamp'] = time();
		$parameters['oauth_signature_method'] = 'HMAC-SHA1';
		$parameters['oauth_version'] = '1.0';

		// calculate the base string
		$base = $this->calculateBaseString(self::SECURE_API_URL . '/oauth/' . $method, 'POST', $parameters);

		// add sign into the parameters
		$parameters['oauth_signature'] = $this->hmacsha1($this->getConsumerSecret() . '&' . $this->getOAuthTokenSecret(), $base);

		// calculate header
		$header = $this->calculateHeader($parameters, self::SECURE_API_URL . '/oauth/' . $method);

		// set options
		$options[CURLOPT_URL] = self::SECURE_API_URL . '/oauth/' . $method;
		$options[CURLOPT_PORT] = self::SECURE_API_PORT;
		$options[CURLOPT_USERAGENT] = $this->getUserAgent();
		if(ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) $options[CURLOPT_FOLLOWLOCATION] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_TIMEOUT] = (int) $this->getTimeOut();
		$options[CURLOPT_SSL_VERIFYPEER] = false;
		$options[CURLOPT_SSL_VERIFYHOST] = false;
		$options[CURLOPT_HTTPHEADER] = array('Expect:');
		$options[CURLOPT_POST] = true;
		$options[CURLOPT_POSTFIELDS] = $this->buildQuery($parameters);

		// init
		$this->curl = curl_init();

		// set options
		curl_setopt_array($this->curl, $options);

		// execute
		$response = curl_exec($this->curl);
		$headers = curl_getinfo($this->curl);

		// fetch errors
		$errorNumber = curl_errno($this->curl);
		$errorMessage = curl_error($this->curl);

		// error?
		if($errorNumber != '') throw new TwitterException($errorMessage, $errorNumber);

		// init var
		$return = array();

		// parse the string
		parse_str($response, $return);

		// return
		return $return;
	}


	/**
	 * Make the call
	 *
	 * @return	string
	 * @param	string $url						The url to call.
	 * @param	array[optional] $parameters		Optional parameters.
	 * @param	bool[optional] $authenticate	Should we authenticate.
	 * @param	bool[optional] $method			The method to use. Possible values are GET, POST.
	 * @param	string[optional] $filePath		The path to the file to upload.
	 * @param	bool[optional] $expectJSON		Do we expect JSON.
	 * @param	bool[optional] $returnHeaders	Should the headers be returned?
	 */
	private function doCall($url, array $parameters = null, $authenticate = false, $method = 'GET', $filePath = null, $expectJSON = true, $returnHeaders = false)
	{
		// allowed methods
		$allowedMethods = array('GET', 'POST');

		// redefine
		$url = (string) $url;
		$parameters = (array) $parameters;
		$authenticate = (bool) $authenticate;
		$method = (string) $method;
		$expectJSON = (bool) $expectJSON;

		// validate method
		if(!in_array($method, $allowedMethods)) throw new TwitterException('Unknown method (' . $method . '). Allowed methods are: ' . implode(', ', $allowedMethods));

		// append default parameters
		$oauth['oauth_consumer_key'] = $this->getConsumerKey();
		$oauth['oauth_nonce'] = md5(microtime() . rand());
		$oauth['oauth_timestamp'] = time();
		$oauth['oauth_token'] = $this->getOAuthToken();
		$oauth['oauth_signature_method'] = 'HMAC-SHA1';
		$oauth['oauth_version'] = '1.0';

		// set data
		$data = $oauth;
		if(!empty($parameters)) $data = array_merge($data, $parameters);

		// calculate the base string
		$base = $this->calculateBaseString(self::API_URL . '/' . $url, $method, $data);

		// based on the method, we should handle the parameters in a different way
		if($method == 'POST')
		{
			// file provided?
			if($filePath != null)
			{
				// build a boundary
				$boundary = md5(time());

				// process file
				$fileInfo = pathinfo($filePath);

				// set mimeType
				$mimeType = 'application/octet-stream';
				if($fileInfo['extension'] == 'jpg' || $fileInfo['extension'] == 'jpeg') $mimeType = 'image/jpeg';
				elseif($fileInfo['extension'] == 'gif') $mimeType = 'image/gif';
				elseif($fileInfo['extension'] == 'png') $mimeType = 'image/png';

				// init var
				$content = '--' . $boundary . "\r\n";

				// set file
				$content .= 'Content-Disposition: form-data; name=image; filename="' . $fileInfo['basename'] . '"' . "\r\n";
				$content .= 'Content-Type: ' . $mimeType . "\r\n";
				$content .= "\r\n";
				$content .= file_get_contents($filePath);
				$content .= "\r\n";
				$content .= "--" . $boundary . '--';

				// build headers
				$headers[] = 'Content-Type: multipart/form-data; boundary=' . $boundary;
				$headers[] = 'Content-Length: ' . strlen($content);

				// set content
				$options[CURLOPT_POSTFIELDS] = $content;
			}

			// no file
			else $options[CURLOPT_POSTFIELDS] = $this->buildQuery($parameters);

			// enable post
			$options[CURLOPT_POST] = true;
		}

		else
		{
			// add the parameters into the querystring
			if(!empty($parameters)) $url .= '?' . $this->buildQuery($parameters);

			$options[CURLOPT_POST] = false;
		}

		// add sign into the parameters
		$oauth['oauth_signature'] = $this->hmacsha1($this->getConsumerSecret() . '&' . $this->getOAuthTokenSecret(), $base);

		$headers[] = $this->calculateHeader($oauth, self::API_URL . '/' . $url);
		$headers[] = 'Expect:';

		// set options
		$options[CURLOPT_URL] = self::API_URL . '/' . $url;
		$options[CURLOPT_PORT] = self::API_PORT;
		$options[CURLOPT_USERAGENT] = $this->getUserAgent();
		if(ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) $options[CURLOPT_FOLLOWLOCATION] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_TIMEOUT] = (int) $this->getTimeOut();
		$options[CURLOPT_SSL_VERIFYPEER] = false;
		$options[CURLOPT_SSL_VERIFYHOST] = false;
		$options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
		$options[CURLOPT_HTTPHEADER] = $headers;

		// init
		if($this->curl == null) $this->curl = curl_init();

		// set options
		curl_setopt_array($this->curl, $options);

		// execute
		$response = curl_exec($this->curl);
		$headers = curl_getinfo($this->curl);

		// fetch errors
		$errorNumber = curl_errno($this->curl);
		$errorMessage = curl_error($this->curl);

		// return the headers
		if($returnHeaders) return $headers;

		// we don't expext JSON, return the response
		if(!$expectJSON) return $response;

		// replace ids with their string values, added because of some PHP-version can't handle these large values
		$response = preg_replace('/id":(\d+)/', 'id":"\1"', $response);

		// we expect JSON, so decode it
		$json = @json_decode($response, true);

		// validate JSON
		if($json === null)
		{
			// should we provide debug information
			if(self::DEBUG)
			{
				// make it output proper
				echo '<pre>';

				// dump the header-information
				var_dump($headers);

				// dump the error
				var_dump($errorMessage);

				// dump the raw response
				var_dump($response);

				// end proper format
				echo '</pre>';
			}

			// throw exception
			throw new TwitterException('Invalid response.');
		}


		// any errors
		if(isset($json['errors']))
		{
			// should we provide debug information
			if(self::DEBUG)
			{
				// make it output proper
				echo '<pre>';

				// dump the header-information
				var_dump($headers);

				// dump the error
				var_dump($errorMessage);

				// dump the raw response
				var_dump($response);

				// end proper format
				echo '</pre>';
			}

			// throw exception
			if(isset($json['errors'][0]['message'])) throw new TwitterException($json['errors'][0]['message']);
			elseif(isset($json['errors']) && is_string($json['errors'])) throw new TwitterException($json['errors']);
			else throw new TwitterException('Invalid response.');
		}


		// any error
		if(isset($json['error']))
		{
			// should we provide debug information
			if(self::DEBUG)
			{
				// make it output proper
				echo '<pre>';

				// dump the header-information
				var_dump($headers);

				// dump the raw response
				var_dump($response);

				// end proper format
				echo '</pre>';
			}

			// throw exception
			throw new TwitterException($json['error']);
		}

		// return
		return $json;
	}


	/**
	 * Make the call
	 *
	 * @return	string
	 * @param	string $url						The url to call.
	 * @param	array[optional] $parameters		Optional parameters.
	 */
	private function doSearchCall($url, array $parameters = null)
	{
		// redefine
		$url = (string) $url;
		$parameters = (array) $parameters;

		// add the parameters into the querystring
		if(!empty($parameters)) $url .= '?' . $this->buildQuery($parameters);

		// set options
		$options[CURLOPT_URL] = self::SEARCH_API_URL . '/' . $url;
		$options[CURLOPT_PORT] = self::SEARCH_API_PORT;
		$options[CURLOPT_USERAGENT] = $this->getUserAgent();
		if(ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) $options[CURLOPT_FOLLOWLOCATION] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_TIMEOUT] = (int) $this->getTimeOut();
		$options[CURLOPT_SSL_VERIFYPEER] = false;
		$options[CURLOPT_SSL_VERIFYHOST] = false;
		$options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;

		// init
		if($this->curl == null) $this->curl = curl_init();

		// set options
		curl_setopt_array($this->curl, $options);

		// execute
		$response = curl_exec($this->curl);
		$headers = curl_getinfo($this->curl);

		// fetch errors
		$errorNumber = curl_errno($this->curl);
		$errorMessage = curl_error($this->curl);

		// replace ids with their string values, added because of some PHP-version can't handle these large values
		$response = preg_replace('/id":(\d+)/', 'id":"\1"', $response);

		// we expect JSON, so decode it
		$json = @json_decode($response, true);

		// validate JSON
		if($json === null)
		{
			// should we provide debug information
			if(self::DEBUG)
			{
				// make it output proper
				echo '<pre>';

				// dump the header-information
				var_dump($headers);

				// dump the error
				var_dump($errorMessage);

				// dump the raw response
				var_dump($response);

				// end proper format
				echo '</pre>';
			}

			// throw exception
			throw new TwitterException('Invalid response.');
		}


		// any errors
		if(isset($json['errors']))
		{
			// should we provide debug information
			if(self::DEBUG)
			{
				// make it output proper
				echo '<pre>';

				// dump the header-information
				var_dump($headers);

				// dump the error
				var_dump($errorMessage);

				// dump the raw response
				var_dump($response);

				// end proper format
				echo '</pre>';
			}

			// throw exception
			if(isset($json['errors'][0]['message'])) throw new TwitterException($json['errors'][0]['message']);
			else throw new TwitterException('Invalid response.');
		}


		// any error
		if(isset($json['error']))
		{
			// should we provide debug information
			if(self::DEBUG)
			{
				// make it output proper
				echo '<pre>';

				// dump the header-information
				var_dump($headers);

				// dump the raw response
				var_dump($response);

				// end proper format
				echo '</pre>';
			}

			// throw exception
			throw new TwitterException($json['error']);
		}

		// return
		return $json;
	}


	/**
	 * Get the consumer key
	 *
	 * @return	string
	 */
	private function getConsumerKey()
	{
		return $this->consumerKey;
	}


	/**
	 * Get the consumer secret
	 *
	 * @return	string
	 */
	private function getConsumerSecret()
	{
		return $this->consumerSecret;
	}


	/**
	 * Get the oAuth-token
	 *
	 * @return	string
	 */
	private function getOAuthToken()
	{
		return $this->oAuthToken;
	}


	/**
	 * Get the oAuth-token-secret
	 *
	 * @return	string
	 */
	private function getOAuthTokenSecret()
	{
		return $this->oAuthTokenSecret;
	}


	/**
	 * Get the timeout
	 *
	 * @return	int
	 */
	public function getTimeOut()
	{
		return (int) $this->timeOut;
	}


	/**
	 * Get the useragent that will be used. Our version will be prepended to yours.
	 * It will look like: "PHP Twitter/<version> <your-user-agent>"
	 *
	 * @return	string
	 */
	public function getUserAgent()
	{
		return (string) 'PHP Twitter/' . self::VERSION . ' ' . $this->userAgent;
	}


	/**
	 * Set the consumer key
	 *
	 * @return	void
	 * @param	string $key		The consumer key to use.
	 */
	private function setConsumerKey($key)
	{
		$this->consumerKey = (string) $key;
	}


	/**
	 * Set the consumer secret
	 *
	 * @return	void
	 * @param	string $secret	The consumer secret to use.
	 */
	private function setConsumerSecret($secret)
	{
		$this->consumerSecret = (string) $secret;
	}


	/**
	 * Set the oAuth-token
	 *
	 * @return	void
	 * @param	string $token	The token to use.
	 */
	public function setOAuthToken($token)
	{
		$this->oAuthToken = (string) $token;
	}


	/**
	 * Set the oAuth-secret
	 *
	 * @return	void
	 * @param	string $secret	The secret to use.
	 */
	public function setOAuthTokenSecret($secret)
	{
		$this->oAuthTokenSecret = (string) $secret;
	}


	/**
	 * Set the timeout
	 *
	 * @return	void
	 * @param	int $seconds	The timeout in seconds.
	 */
	public function setTimeOut($seconds)
	{
		$this->timeOut = (int) $seconds;
	}


	/**
	 * Get the useragent that will be used. Our version will be prepended to yours.
	 * It will look like: "PHP Twitter/<version> <your-user-agent>"
	 *
	 * @return	void
	 * @param	string $userAgent	Your user-agent, it should look like <app-name>/<app-version>.
	 */
	public function setUserAgent($userAgent)
	{
		$this->userAgent = (string) $userAgent;
	}


	/**
	 * Build the signature for the data
	 *
	 * @return	string
	 * @param	string $key		The key to use for signing.
	 * @param	string $data	The data that has to be signed.
	 */
	private function hmacsha1($key, $data)
	{
		return base64_encode(hash_hmac('SHA1', $data, $key, true));
	}


	/**
	 * URL-encode method for internal use
	 *
	 * @return	string
	 * @param	mixed $value	The value to encode.
	 */
	private static function urlencode_rfc3986($value)
	{
		if(is_array($value)) return array_map(array('Twitter', 'urlencode_rfc3986'), $value);
		else
		{
			$search = array('+', ' ', '%7E', '%');
			$replace = array('%20', '%20', '~', '%25');

			return str_replace($search, $replace, urlencode($value));
		}
	}


// Timeline resources
	/**
	 * Returns the 20 most recent statuses from non-protected users who have set a custom user icon.
	 * The public timeline is cached for 60 seconds and requesting it more often than that is unproductive and a waste of resources.
	 *
	 * @return	array
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesPublicTimeline($trimUser = false, $includeEntities = false)
	{
		// redefine
		$trimUser = (bool) $trimUser;
		$includeEntities = (bool) $includeEntities;

		// build parameters
		$parameters = array();
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/public_timeline.json', $parameters);
	}


	/**
	 * Returns the 20 most recent statuses, including retweets if they exist, posted by the authenticating user and the user's they follow. This is the same timeline seen by a user when they login to twitter.com.
	 * This method is identical to statusesFriendsTimeline, except that this method always includes retweets.
	 *
	 * @return	array
	 * @param	string[optional] $sinceId			Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param	string[optional] $maxId				Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 200.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesHomeTimeline($sinceId = null, $maxId = null, $count = null, $page = null, $trimUser = false, $includeEntities = false)
	{
		// build parameters
		$parameters = array();
		if($sinceId != null) $parameters['since_id'] = (string) $sinceId;
		if($maxId != null) $parameters['max_id'] = (string) $maxId;
		if($count != null) $parameters['count'] = (int) $count;
		if($page != null) $parameters['page'] = (int) $page;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/home_timeline.json', $parameters, true);
	}


	/**
	 * Returns the 20 most recent statuses posted by the authenticating user and the user's they follow. This is the same timeline seen by a user when they login to twitter.com.
	 * This method is identical to statuses/home_timeline, except that this method will only include retweets if the includeRts parameter is set.
	 *
	 * @return	array
	 * @param	string[optional] $sinceId			Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param	string[optional] $maxId				Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 200.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeRts			When set to true the timeline will contain native retweets (if they exist) in addition to the standard stream of tweets. The output format of retweeted tweets is identical to the representation you see in home_timeline. Note: If you're using the trim_user parameter in conjunction with include_rts, the retweets will still contain a full user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesFriendsTimeline($sinceId = null, $maxId = null, $count = null, $page = null, $trimUser = false, $includeRts = false, $includeEntities = false)
	{
		// build parameters
		$parameters = array();
		if($sinceId != null) $parameters['since_id'] = (string) $sinceId;
		if($maxId != null) $parameters['max_id'] = (string) $maxId;
		if($count != null) $parameters['count'] = (int) $count;
		if($page != null) $parameters['page'] = (int) $page;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeRts) $parameters['include_rts'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/friends_timeline.json', $parameters, true);
	}


	/**
	 * Returns the 20 most recent statuses posted by the authenticating user. It is also possible to request another user's timeline by using the screen_name or user_id parameter. The other users timeline will only be visible if they are not protected, or if the authenticating user's follow request was accepted by the protected user.
	 * The timeline returned is the equivalent of the one seen when you view a user's profile on twitter.com.
	 *
	 * @return	array
	 * @param	string[optional] $userId			Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	string[optional] $screenName		Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $sinceId			Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param	string[optional] $maxId				Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 200.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeRts			When set to true the timeline will contain native retweets (if they exist) in addition to the standard stream of tweets. The output format of retweeted tweets is identical to the representation you see in home_timeline. Note: If you're using the trim_user parameter in conjunction with include_rts, the retweets will still contain a full user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesUserTimeline($userId = null, $screenName = null, $sinceId = null, $maxId = null, $count = null, $page = null, $trimUser = false, $includeRts = false, $includeEntities = false)
	{
		// build parameters
		$parameters = array();
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($sinceId != null) $parameters['since_id'] = (string) $sinceId;
		if($maxId != null) $parameters['max_id'] = (string) $maxId;
		if($count != null) $parameters['count'] = (int) $count;
		if($page != null) $parameters['page'] = (int) $page;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeRts) $parameters['include_rts'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/user_timeline.json', $parameters);
	}


	/**
	 * Returns the 20 most recent mentions (status containing @username) for the authenticating user.
	 * The timeline returned is the equivalent of the one seen when you view your mentions on twitter.com.
	 *
	 * @return	array
	 * @param	string[optional] $sinceId			Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param	string[optional] $maxId				Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 200.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $trimUser			When set to either true, each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeRts			When set to either true, the timeline will contain native retweets (if they exist) in addition to the standard stream of tweets. The output format of retweeted tweets is identical to the representation you see in home_timeline. Note: If you're using the trim_user parameter in conjunction with include_rts, the retweets will still contain a full user object.
	 * @param	bool[optional] $includeEntities		When set to either true, each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags. While entities are opt-in on timelines at present, they will be made a default component of output in the future.
	 */
	public function statusesMentions($sinceId = null, $maxId = null, $count = null, $page = null, $trimUser = false, $includeRts = false, $includeEntities = false)
	{
		// validate
		if($count != null && $count > 200) throw new TwitterException('Count may not be greater than 200.');

		// build parameters
		$parameters = array();
		if($sinceId != null) $parameters['since_id'] = (string) $sinceId;
		if($maxId != null) $parameters['max_id'] = (string) $maxId;
		if($count != null) $parameters['count'] = (int) $count;
		if($page != null) $parameters['page'] = (int) $page;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeRts) $parameters['include_rts'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/mentions.json', $parameters, true);
	}


	/**
	 * Returns the 20 most recent retweets posted by the authenticating user.
	 *
	 * @return	array
	 * @param	string[optional] $sinceId			Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param	string[optional] $maxId				Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 200.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesRetweetedByMe($sinceId = null, $maxId = null, $count = null, $page = null, $trimUser = false, $includeEntities = false)
	{
		// validate
		if($count != null && $count > 200) throw new TwitterException('Count may not be greater than 200.');

		// build parameters
		$parameters = array();
		if($sinceId != null) $parameters['since_id'] = (string) $sinceId;
		if($maxId != null) $parameters['max_id'] = (string) $maxId;
		if($count != null) $parameters['count'] = (int) $count;
		if($page != null) $parameters['page'] = (int) $page;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/retweeted_by_me.json', $parameters, true);
	}


	/**
	 * Returns the 20 most recent retweets posted by users the authenticating user follow.
	 *
	 * @return	array
	 * @param	string[optional] $sinceId			Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param	string[optional] $maxId				Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 200.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesRetweetedToMe($sinceId = null, $maxId = null, $count = null, $page = null, $trimUser = false, $includeEntities = false)
	{
		// validate
		if($count != null && $count > 200) throw new TwitterException('Count may not be greater than 200.');

		// build parameters
		$parameters = array();
		if($sinceId != null) $parameters['since_id'] = (string) $sinceId;
		if($maxId != null) $parameters['max_id'] = (string) $maxId;
		if($count != null) $parameters['count'] = (int) $count;
		if($page != null) $parameters['page'] = (int) $page;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/retweeted_by_me.json', $parameters, true);
	}


	/**
	 * Returns the 20 most recent tweets of the authenticated user that have been retweeted by others.
	 *
	 * @return	array
	 * @param	string[optional] $sinceId			Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param	string[optional] $maxId				Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 200.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesReweetsOfMe($sinceId = null, $maxId = null, $count = null, $page = null, $trimUser = false, $includeEntities = false)
	{
		// validate
		if($count != null && $count > 200) throw new TwitterException('Count may not be greater than 200.');

		// build parameters
		$parameters = array();
		if($sinceId != null) $parameters['since_id'] = (string) $sinceId;
		if($maxId != null) $parameters['max_id'] = (string) $maxId;
		if($count != null) $parameters['count'] = (int) $count;
		if($page != null) $parameters['page'] = (int) $page;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/retweets_of_me.json', $parameters, true);
	}


// Tweets resources
	/**
	 * Returns a single status, specified by the id parameter below. The status's author will be returned inline.
	 *
	 * @return	array
	 * @param	string $id							The numerical ID of the desired status.
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesShow($id, $trimUser = false, $includeEntities = false)
	{
		// build parameters
		$parameters['id'] = (string) $id;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/show.json', $parameters, true);
	}


	/**
	 * Updates the authenticating user's status. A status update with text identical to the authenticating user's text identical to the authenticating user's current status will be ignored to prevent duplicates.
	 *
	 * @return	array
	 * @param	string $status							The text of your status update, up to 140 characters. URL encode as necessary.
	 * @param	string[optional] $inReplyToStatusId		The ID of an existing status that the update is in reply to.
	 * @param	float[optional] $lat					The location's latitude that this tweet refers to.
	 * @param	float[optional] $long					The location's longitude that this tweet refers to.
	 * @param	string[optional] $placeId				A place in the world. These IDs can be retrieved from geo/reverse_geocode.
	 * @param	bool[optional] $displayCoordinates		Whether or not to put a pin on the exact coordinates a tweet has been sent from.
	 * @param	bool[optional] $trimUser				When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeEntities			When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesUpdate($status, $inReplyToStatusId = null, $lat = null, $long = null, $placeId = null, $displayCoordinates = false, $trimUser = false, $includeEntities = false)
	{
		// build parameters
		$parameters['status'] = (string) $status;
		if($inReplyToStatusId != null) $parameters['in_reply_to_status_id'] = (string) $inReplyToStatusId;
		if($lat != null) $parameters['lat'] = (float) $lat;
		if($long != null) $parameters['long'] = (float) $long;
		if($placeId != null) $parameters['place_id'] = (string) $placeId;
		if($displayCoordinates) $parameters['display_coordinates'] = 'true';
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/update.json', $parameters, true, 'POST');
	}


	/**
	 * Destroys the status specified by the required ID parameter.
	 * Usage note: The authenticating user must be the author of the specified status.
	 *
	 * @return	bool
	 * @param	string $id							The numerical ID of the desired status.
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesDestroy($id, $trimUser = false, $includeEntities = false)
	{
		// build parameters
		$parameters['id'] = (string) $id;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/destroy.json', $parameters, true, 'POST');
	}


	/**
	 * Retweets a tweet. Returns the original tweet with retweet details embedded.
	 *
	 * @return	array
	 * @param	string $id							The numerical ID of the desired status.
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesRetweet($id, $trimUser = false, $includeEntities = false)
	{
		$parameters = null;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/retweet/' . $id . '.json', $parameters, true, 'POST');
	}


	/**
	 * Returns up to 100 of the first retweets of a given tweet.
	 *
	 * @return	array
	 * @param	string $id							The numerical ID of the desired status.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 100.
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesRetweets($id, $count = null, $trimUser = false, $includeEntities = false)
	{
		// validate
		if($count != null && $count > 100) throw new TwitterException('Count may not be greater than 100.');

		// build parameters
		$parameters = null;
		if($count != null) $parameters['count'] = (int) $count;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/retweets/' . $id . '.json', $parameters);
	}


	/**
	 * Show user objects of up to 100 members who retweeted the status.
	 *
	 * @return	array
	 * @param	string $id							The numerical ID of the desired status.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 200.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesIdRetweetedBy($id, $count = null, $page = null, $trimUser = false, $includeEntities = false)
	{
		// validate
		if($count != null && $count > 200) throw new TwitterException('Count may not be greater than 200.');

		// build parameters
		$parameters = null;
		if($count != null) $parameters['count'] = (int) $count;
		if($page != null) $parameters['page'] = (int) $page;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/' . (string) $id . '/retweeted_by.json', $parameters, true);
	}


	/**
	 * Show user ids of up to 100 users who retweeted the status.
	 *
	 * @return	array
	 * @param	string $id							The numerical ID of the desired status.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 200.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $trimUser			When set to true each tweet returned in a timeline will include a user object including only the status authors numerical ID. Omit this parameter to receive the complete user object.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesIdRetweetedByIds($id, $count = null, $page = null, $trimUser = false, $includeEntities = false)
	{
		// validate
		if($count != null && $count > 200) throw new TwitterException('Count may not be greater than 200.');

		// build parameters
		$parameters = null;
		if($count != null) $parameters['count'] = (int) $count;
		if($page != null) $parameters['page'] = (int) $page;
		if($trimUser) $parameters['trim_user'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/' . (string) $id . '/retweeted_by/ids.json', $parameters, true);
	}


// User resources
	/**
	 * Returns extended information of a given user, specified by ID or screen name as per the required id parameter.
	 * The author's most recent status will be returned inline.
	 *
	 * @return	array
	 * @param	string[optional] $userId			Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	string[optional] $screenName		Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function usersShow($userId = null, $screenName = null, $includeEntities = false)
	{
		// validate
		if($userId == '' && $screenName == '') throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		$parameters = null;
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('users/show.json', $parameters);
	}


	/**
	 * Return up to 100 users worth of extended information, specified by either ID, screen name, or combination of the two.
	 * The author's most recent status (if the authenticating user has permission) will be returned inline.
	 *
	 * @return	array
	 * @param	mixed[optional] $userIds			An array of user IDs, up to 100 in total.
	 * @param	mixed[optional] $screenNames		An array of screen names, up to 100 in total.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function usersLookup($userIds = null, $screenNames = null, $includeEntities = false)
	{
		// redefine
		$userIds = (array) $userIds;
		$screenNames = (array) $screenNames;

		// validate
		if(empty($userIds) && empty($screenNames)) throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		$parameters = null;
		if(!empty($userIds)) $parameters['user_id'] = implode(',', $userIds);
		if(!empty($screenNames)) $parameters['screen_name'] = implode(',', $screenNames);
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('users/lookup.json', $parameters, true);

	}


	/**
	 * Run a search for users similar to the Find People button on Twitter.com; the same results returned by people search on Twitter.com will be returned by using this API.
	 * Usage note: It is only possible to retrieve the first 1000 matches from this API.
	 *
	 * @return	array
	 * @param	string $q							The search query term.
	 * @param	int[optional] $perPage				Specifies the number of results to retrieve.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function usersSearch($q, $perPage = null, $page = null, $includeEntities = false)
	{
		// build parameters
		$parameters['q'] = (string) $q;
		if($perPage != null) $parameters['per_page'] = (int) $perPage;
		if($page != null) $parameters['page'] = (int) $page;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('users/search.json', $parameters, true);
	}


	/**
	 * Access to Twitter's suggested user list. This returns the list of suggested user categories. The category can be used in the users/suggestions/category  endpoint to get the users in that category.
	 *
	 * @return	array
	 */
	public function usersSuggestions()
	{
		return (array) $this->doCall('users/suggestions.json');
	}


	/**
	 * Access the users in a given category of the Twitter suggested user list.
	 * It is recommended that end clients cache this data for no more than one hour.
	 *
	 * @return	array
	 * @param	string $slug	The short name of list or a category.
	 */
	public function usersSuggestionsSlug($slug)
	{
		return (array) $this->doCall('users/suggestions/' . (string) $slug . '.json');
	}


	/**
	 * Access the profile image in various sizes for the user with the indicated screen_name. If no size is provided the normal image is returned.
	 * This method return an URL to the actual image resource.
	 * This method should only be used by application developers to lookup or check the profile image URL for a user.
	 * This method must not be used as the image source URL presented to users of your application.
	 *
	 * @return	string
	 * @param	string $screenName			The screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $size		Specifies the size of image to fetch. Not specifying a size will give the default, normal size of 48px by 48px. Valid options include: bigger (73x73px), normal (48x48px), mini (24x24px).
	 */
	public function usersProfileImage($screenName, $size = 'normal')
	{
		// possible modes
		$allowedSizes = array('normal', 'bigger', 'mini');

		// validate
		if($size != null && !in_array($size, $allowedSizes)) throw new TwitterException('Invalid size (' . $size . '), possible values are: ' . implode($allowedSizes) . '.');

		// build parameters
		$parameters['size'] = (string) $size;

		$headers = $this->doCall('users/profile_image/' . (string) $screenName . '.json', $parameters, false, 'GET', null, false, true);

		// return the URL
		if(isset($headers['url'])) return $headers['url'];

		// fallback
		return false;
	}


	/**
	 * Returns a user's friends, each with current status inline. They are ordered by the order in which the user followed them, most recently followed first, 100 at a time.
	 * (Please note that the result set isn't guaranteed to be 100 every time as suspended users will be filtered out.)
	 *
	 * Use the cursor option to access older friends.
	 * With no user specified, request defaults to the authenticated user's friends.
	 * It's also possible to request another user's friends list via the id, screen_name or user_id parameter.
	 *
	 * @return	array
	 * @param	string[optional] $userId			Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	string[optional] $screenName		Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	int[optional] $cursor				Breaks the results into pages. This is recommended for users who are following many users. Provide a value of -1  to begin paging. Provide values as returned to in the response body's next_cursor  and previous_cursor attributes to page back and forth in the list.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesFriends($userId = null, $screenName = null, $cursor = null, $includeEntities = false)
	{
		// build parameters
		$parameters = array();
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($cursor != null) $parameters['cursor'] = $cursor;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/friends.json', $parameters, true);
	}


	/**
	 * Returns the authenticating user's followers, each with current status inline. They are ordered by the order in which they followed the user, 100 at a time. (Please note that the result set isn't guaranteed to be 100 every time as suspended users will be filtered out.)
	 * Use the cursor parameter to access earlier followers.
	 *
	 * @return	array
	 * @param	string[optional] $userId			Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	string[optional] $screenName		Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	int[optional] $cursor				Breaks the results into pages. This is recommended for users who are following many users. Provide a value of -1  to begin paging. Provide values as returned to in the response body's next_cursor  and previous_cursor attributes to page back and forth in the list.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function statusesFollowers($userId = null, $screenName = null, $cursor = null, $includeEntities = false)
	{
		// build parameters
		$parameters = array();
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($cursor != null) $parameters['cursor'] = $cursor;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('statuses/followers.json', $parameters, true);
	}


// Trends resources
	/**
	 * Returns the top ten topics that are currently trending on Twitter.
	 * The response includes the time of the request, the name of each trend, and the url to the Twitter Search results page for that topic.
	 *
	 * @return	array
	 */
	public function trends()
	{
		return (array) $this->doCall('trends.json');
	}


	/**
	 * Returns the current top 10 trending topics on Twitter. The response includes the time of the request, the name of each trending topic, and query used on Twitter Search results page for that topic.
	 *
	 * @return	array
	 * @param	string[optional] $exclude	Setting this equal to hashtags will remove all hashtags from the trends list.
	 */
	public function trendsCurrent($exclude = null)
	{
		// build parameters
		$parameters = null;
		if($exclude != null) $parameters['exclude'] = (string) $exclude;

		// make the call
		return (array) $this->doCall('trends/current.json', $parameters);
	}


	/**
	 * Returns the top 20 trending topics for each hour in a given day.
	 *
	 * @return	array
	 * @param	string[optional] $date		Permits specifying a start date for the report. The date should be formatted YYYY-MM-DD.
	 * @param	string[optional] $exclude	Setting this equal to hashtags will remove all hashtags from the trends list.
	 */
	public function trendsDaily($date = null, $exclude = null)
	{
		// build parameters
		$parameters = null;
		if($date != null) $parameters['date'] = (string) $date;
		if($exclude != null) $parameters['exclude'] = (string) $exclude;

		// make the call
		return (array) $this->doCall('trends/daily.json', $parameters);
	}


	/**
	 * Returns the top 30 trending topics for each day in a given week.
	 *
	 * @return	array
	 * @param	string[optional] $date		Permits specifying a start date for the report. The date should be formatted YYYY-MM-DD.
	 * @param	string[optional] $exclude	Setting this equal to hashtags will remove all hashtags from the trends list.
	 */
	public function trendsWeekly($date = null, $exclude = null)
	{
		// build parameters
		$parameters = null;
		if($date != null) $parameters['date'] = (string) $date;
		if($exclude != null) $parameters['exclude'] = (string) $exclude;

		// make the call
		return (array) $this->doCall('trends/weekly.json', $parameters);
	}


// List resources
	/**
	 * Creates a new list for the authenticated user. Accounts are limited to 20 lists.
	 *
	 * @return	array
	 * @param	string $user					The user.
	 * @param	string $name					The name of the list you are creating.
	 * @param	string[optional] $mode			Whether your list is public or private. Values can be public or private. Lists are public by default if no mode is specified.
	 * @param	string[optional] $description	The description of the list you are creating.
	 */
	public function userListsCreate($user, $name, $mode = null, $description = null)
	{
		// possible modes
		$allowedModes = array('public', 'private');

		// validate
		if($mode != null && !in_array($mode, $allowedModes)) throw new TwitterException('Invalid mode (), possible values are: ' . implode($allowedModes) . '.');

		// build parameters
		$parameters['name'] = (string) $name;
		if($mode != null) $parameters['mode'] = (string) $mode;
		if($description != null) $parameters['description'] = (string) $description;

		// make the call
		return (array) $this->doCall((string) $user . '/lists.json', $parameters, true, 'POST');
	}


	/**
	 * List the lists of the specified user. Private lists will be included if the authenticated users is the same as the user who's lists are being returned.
	 *
	 * @return	array
	 * @param	string $user				The user.
	 * @param	string[optional] $cursor	Breaks the results into pages. This is recommended for users who are following many users. Provide a value of -1  to begin paging. Provide values as returned to in the response body's next_cursor  and previous_cursor attributes to page back and forth in the list.
	 */
	public function userLists($user, $cursor = null)
	{
		$parameters = null;
		if($cursor != null) $parameters['cursor'] = (string) $cursor;

		// make the call
		return (array) $this->doCall((string) $user . '/lists.json', $parameters, true);
	}


	/**
	 * Show the specified list. Private lists will only be shown if the authenticated user owns the specified list.
	 *
	 * @return	array
	 * @param	string $user	The user.
	 * @param	string $id		The id of the list.
	 */
	public function userListsId($user, $id)
	{
		// make the call
		return (array) $this->doCall((string) $user . '/lists/' . (string) $id . '.json', null, true);
	}


	/**
	 * Updates the specified list.
	 *
	 * @return	array
	 * @param	string $user					The user.
	 * @param	string $id						The id of the list.
	 * @param	string[optional] $name			The name of the list you are creating.
	 * @param	string[optional] $mode			Whether your list is public or private. Values can be public or private. Lists are public by default if no mode is specified.
	 * @param	string[optional] $description	The description of the list you are creating.
	 */
	public function userListsIdUpdate($user, $id, $name = null, $mode = null, $description = null)
	{
		// possible modes
		$allowedModes = array('public', 'private');

		// validate
		if($mode != null && !in_array($mode, $allowedModes)) throw new TwitterException('Invalid mode (), possible values are: ' . implode($allowedModes) . '.');

		// build parameters
		if($name != null) $parameters['name'] = (string) $name;
		if($mode != null) $parameters['mode'] = (string) $mode;
		if($description != null) $parameters['description'] = (string) $description;

		// make the call
		return (array) $this->doCall((string) $user . '/lists/' . (string) $id . '.json', $parameters, true, 'POST');
	}


	/**
	 * Show tweet timeline for members of the specified list.
	 *
	 * @return	array
	 * @param	string $user						The user.
	 * @param	string $id							The id of the list.
	 * @param	string[optional] $sinceId			Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param	string[optional] $maxId				Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 200.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function userListsIdStatuses($user, $id, $sinceId = null, $maxId = null, $count = null, $page = null, $includeEntities = false)
	{
		// validate
		if($count != null && $count > 200) throw new TwitterException('Count may not be greater than 200.');

		// build parameters
		$parameters = array();
		if($sinceId != null) $parameters['since_id'] = (string) $sinceId;
		if($maxId != null) $parameters['max_id'] = (string) $maxId;
		if($count != null) $parameters['per_page'] = (int) $count;
		if($page != null) $parameters['page'] = (int) $page;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall((string) $user . '/lists/' . (string) $id . '/statuses.json', $parameters);
	}


	/**
	 * List the lists the specified user has been added to.
	 *
	 * @return	array
	 * @param	string $user				The user.
	 * @param	string[optional] $cursor	Breaks the results into pages. This is recommended for users who are following many users. Provide a value of -1  to begin paging. Provide values as returned to in the response body's next_cursor  and previous_cursor attributes to page back and forth in the list.
	 */
	public function userListsMemberships($user, $cursor = null)
	{
		// build parameters
		$parameters = null;
		if($cursor != null) $parameters['cursor'] = (string) $cursor;

		// make the call
		return (array) $this->doCall((string) $user . '/lists/memberships.json', $parameters, true);
	}


	/**
	 * List the lists the specified user follows.
	 *
	 * @return	array
	 * @param	string $user				The user.
	 * @param	string[optional] $cursor	Breaks the results into pages. This is recommended for users who are following many users. Provide a value of -1  to begin paging. Provide values as returned to in the response body's next_cursor  and previous_cursor attributes to page back and forth in the list.
	 */
	public function userListsSubscriptions($user, $cursor = null)
	{
		// build parameters
		$parameters = null;
		if($cursor != null) $parameters['cursor'] = (string) $cursor;

		// make the call
		return (array) $this->doCall((string) $user . '/lists/subscriptions.json', $parameters, true);
	}


// List Members resources
	/**
	 * Returns the members of the specified list.
	 *
	 * @return	array
	 * @param	string $user						The user.
	 * @param	string $id							The id of the list.
	 * @param	string[optional] $cursor			Breaks the results into pages. This is recommended for users who are following many users. Provide a value of -1  to begin paging. Provide values as returned to in the response body's next_cursor  and previous_cursor attributes to page back and forth in the list.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function userListMembers($user, $id, $cursor = null, $includeEntities = false)
	{
		// build parameters
		$parameters = null;
		if($cursor != null) $parameters['cursor'] = (string) $cursor;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall((string) $user . '/' . (string) $id . '/members.json', $parameters, true);
	}


	/**
	 * Add a member to a list. The authenticated user must own the list to be able to add members to it. Lists are limited to having 500 members.
	 *
	 * @return	array
	 * @param	string $user	The user.
	 * @param	string $id		The id of the list.
	 * @param	string $userId	The id or screen name of the user to add as a member of the list.
	 */
	public function userListMembersCreate($user, $id, $userId)
	{
		// build parameters
		$parameters['id'] = (string) $userId;

		// make the call
		return (array) $this->doCall((string) $user . '/' . (string) $id . '/members.json', $parameters, true, 'POST');
	}


	/**
	 * Adds multiple members to a list, by specifying a comma-separated list of member ids or screen names. The authenticated user must own the list to be able to add members to it. Lists are limited to having 500 members, and you are limited to adding up to 100 members to a list at a time with this method.
	 *
	 * @return	array
	 * @param	string $user					The user.
	 * @param	string $id						The id of the list.
	 * @param	mixed[optional] $userIds		An array of user IDs, up to 100 in total.
	 * @param	mixed[optional] $screenNames	An array of screen names, up to 100 in total.
	 */
	public function userListMembersCreateAll($user, $id, $userIds = null, $screenNames = null)
	{
		// redefine
		$userIds = (array) $userIds;
		$screenNames = (array) $screenNames;

		// validate
		if(empty($userIds) && empty($screenNames)) throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		$parameters = null;
		if(!empty($userIds)) $parameters['user_id'] = implode(',', $userIds);
		if(!empty($screenNames)) $parameters['screen_name'] = implode(',', $screenNames);

		// make the call
		return (array) $this->doCall((string) $user . '/' . (string) $id . '/create_all.json', $parameters, true, 'POST');
	}


	/**
	 * Removes the specified member from the list. The authenticated user must be the list's owner to remove members from the list.
	 *
	 * @return	mixed
	 * @param	string $user	The user.
	 * @param	string $id		The id of the list.
	 * @param	string $userId	Specfies the ID of the user for whom to return results for.
	 */
	public function userListMembersDelete($user, $id, $userId)
	{
		// build parameters
		$parameters['id'] = (string) $userId;
		$parameters['_method'] = 'DELETE';

		// make the call
		return (array) $this->doCall((string) $user . '/' . (string) $id . '/members.json', $parameters, true, 'POST');
	}


	/**
	 * Check if a user is a member of the specified list.
	 *
	 * @return	mixed
	 * @param	string $user						The user.
	 * @param	string $id							The id of the list.
	 * @param	string $userId						Specfies the ID of the user for whom to return results for.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function userListMembersId($user, $id, $userId, $includeEntities = false)
	{
		try
		{
			// build parameters
			$parameters = null;
			if($includeEntities) $parameters['include_entities'] = 'true';

			// make the call
			return (array) $this->doCall((string) $user . '/' . (string) $id . '/members/' . (string) $userId . '.json', $parameters, true);
		}

		// catch exceptions
		catch(TwitterException $e)
		{
			if($e->getMessage() == 'The specified user is not a member of this list') return false;
			else throw $e;
		}
	}


// List Subscribers resources
	/**
	 * Returns the subscribers of the specified list.
	 *
	 * @return	array
	 * @param	string $user						The user.
	 * @param	string $id							The id of the list.
	 * @param	string[optional] $cursor			Breaks the results into pages. This is recommended for users who are following many users. Provide a value of -1  to begin paging. Provide values as returned to in the response body's next_cursor  and previous_cursor attributes to page back and forth in the list.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function userListSubscribers($user, $id, $cursor = null, $includeEntities = false)
	{
		// build parameters
		$parameters = null;
		if($cursor != null) $parameters['cursor'] = (string) $cursor;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall((string) $user . '/' . (string) $id . '/subscribers.json', $parameters, true);
	}


	/**
	 * Make the authenticated user follow the specified list.
	 *
	 * @return	array
	 * @param	string $user	The user.
	 * @param	string $id		The id of the list.
	 */
	public function userListSubscribersCreate($user, $id)
	{
		// make the call
		return (array) $this->doCall((string) $user . '/' . (string) $id . '/subscribers.json', null, true, 'POST');
	}


	/**
	 * Unsubscribes the authenticated user form the specified list.
	 *
	 * @return	array
	 * @param	string $user	The user.
	 * @param	string $id		The id of the list.
	 */
	public function userListSubscribersDelete($user, $id)
	{
		// build parameters
		$parameters['_method'] = 'DELETE';

		// make the call
		return (array) $this->doCall((string) $user . '/' . (string) $id . '/subscribers.json', $parameters, true, 'POST');
	}


	/**
	 * Check if the specified user is a subscriber of the specified list.
	 *
	 * @return	mixed
	 * @param	string $user						The user.
	 * @param	string $id							The id of the list.
	 * @param	string $userId						Specfies the ID of the user for whom to return results for.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function userListSubscribersId($user, $id, $userId, $includeEntities = false)
	{
		try
		{
			// build parameters
			$parameters = null;
			if($includeEntities) $parameters['include_entities'] = 'true';

			// make the call
			return (array) $this->doCall((string) $user . '/' . (string) $id . '/subscribers/' . (string) $userId . '.json', $parameters, true);
		}

		// catch exceptions
		catch(TwitterException $e)
		{
			if($e->getMessage() == 'The specified user is not a subscriber of this list') return false;
			else throw $e;
		}

	}


// Direct Messages resources
	/**
	 * Returns a list of the 20 most recent direct messages sent to the authenticating user.
	 *
	 * @return	array
	 * @param	string[optional] $sinceId			Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param	string[optional] $maxId				Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 200.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function directMessages($sinceId = null, $maxId = null, $count = null, $page = null, $includeEntities = false)
	{
		// validate
		if($count != null && $count > 200) throw new TwitterException('Count may not be greater than 200.');

		// build parameters
		$parameters = array();
		if($sinceId != null) $parameters['since_id'] = (string) $sinceId;
		if($maxId != null) $parameters['max_id'] = (string) $maxId;
		if($count != null) $parameters['count'] = (int) $count;
		if($page != null) $parameters['page'] = (int) $page;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('direct_messages.json', $parameters, true);
	}


	/**
	 * Returns a list of the 20 most recent direct messages sent by the authenticating user.
	 *
	 * @return	array
	 * @param	string[optional] $sinceId			Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param	string[optional] $maxId				Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param	int[optional] $count				Specifies the number of records to retrieve. May not be greater than 200.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function directMessagesSent($sinceId = null, $maxId = null, $count = null, $page = null, $includeEntities = false)
	{
		// validate
		if($count != null && $count > 200) throw new TwitterException('Count may not be greater than 200.');

		// build parameters
		$parameters = array();
		if($sinceId != null) $parameters['since_id'] = (string) $sinceId;
		if($maxId != null) $parameters['max_id'] = (string) $maxId;
		if($count != null) $parameters['count'] = (int) $count;
		if($page != null) $parameters['page'] = (int) $page;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('direct_messages/sent.json', $parameters, true);
	}


	/**
	 * Sends a new direct message to the specified user from the authenticating user.
	 * Requires both the user and text parameters. Returns the sent message in the requested format when successful.
	 *
	 * @return	array
	 * @param	string $text						The text of your direct message. Be sure to URL encode as necessary, and keep it under 140 characters.
	 * @param 	string[optional] $userId			Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $screenName		Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function directMessagesNew($text, $userId = null, $screenName = null, $includeEntities = false)
	{
		// validate
		if($userId == '' && $screenName == '') throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		$parameters['text'] = (string) $text;
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('direct_messages/new.json', $parameters, true, 'POST');
	}


	/**
	 * Destroys the direct message specified in the required ID parameter. The authenticating user must be the recipient of the specified direct message.
	 *
	 * @return	array
	 * @param	string $id							The ID of the desired direct message.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function directMessagesDestroy($id, $includeEntities = false)
	{
		// build parameters
		$parameters['id'] = (string) $id;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('direct_messages/destroy.json', $parameters, true, 'POST');
	}


// Friendship resources
	/**
	 * Allows the authenticating users to follow the user specified in the ID parameter.
	 * Returns the befriended user in the requested format when successful.
	 * Returns a string describing the failure condition when unsuccessful.
	 *
	 * @return	mixed
	 * @param 	string[optional] $userId			Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $screenName		Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	bool[optional] $follow				Returns public statuses that reference the given set of users.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function friendshipsCreate($userId = null, $screenName = null, $follow = false, $includeEntities = false)
	{
		// validate
		if($userId == '' && $screenName == '') throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		$parameters = null;
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		$parameters['follow'] = ($follow) ? 'true' : 'false';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('friendships/create.json', $parameters, true, 'POST');
	}


	/**
	 * Allows the authenticating users to unfollow the user specified in the ID parameter.
	 * Returns the unfollowed user in the requested format when successful. Returns a string describing the failure condition when unsuccessful.
	 *
	 * @return	array
	 * @param 	string[optional] $userId			Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $screenName		Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function friendshipsDestroy($userId = null, $screenName = null, $includeEntities = false)
	{
		// validate
		if($userId == '' && $screenName == '') throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('friendships/destroy.json', $parameters, true, 'POST');
	}


	/**
	 * Tests for the existence of friendship between two users. Will return true if user_a follows user_b, otherwise will return false.
	 *
	 * @return	bool
	 * @param	string $userA	The ID or screen_name of the subject user.
	 * @param	string $userB	The ID or screen_name of the user to test for following.
	 */
	public function friendshipsExists($userA, $userB)
	{
		// build parameters
		$parameters['user_a'] = (string) $userA;
		$parameters['user_b'] = (string) $userB;

		// make the call
		return (bool) $this->doCall('friendships/exists.json', $parameters);
	}


	/**
	 * Returns detailed information about the relationship between two users.
	 *
	 * @return	array
	 * @param 	string[optional] $sourceId				The user_id of the subject user.
	 * @param 	string[optional] $sourceScreenName		The screen_name of the subject user.
	 * @param 	string[optional] $targetId				The screen_name of the subject user.
	 * @param 	string[optional] $targetScreenName		The screen_name of the target user.
	 */
	public function friendshipsShow($sourceId = null, $sourceScreenName = null, $targetId = null, $targetScreenName = null)
	{
		// validate
		if($sourceId == '' && $sourceScreenName == '') throw new TwitterException('Specify an sourceId or a sourceScreenName.');
		if($targetId == '' && $targetScreenName == '') throw new TwitterException('Specify an targetId or a targetScreenName.');

		// build parameters
		if($sourceId != null) $parameters['source_id'] = (string) $sourceId;
		if($sourceScreenName != null) $parameters['source_screen_name'] = (string) $sourceScreenName;
		if($targetId != null) $parameters['target_id'] = (string) $targetId;
		if($targetScreenName != null) $parameters['target_screen_name'] = (string) $targetScreenName;

		// make the call
		return (array) $this->doCall('friendships/show.json', $parameters);
	}


	/**
	 * Returns an array of numeric IDs for every user who has a pending request to follow the authenticating user.
	 *
	 * @return	array
	 * @param	string[optional] $cursor	Breaks the results into pages. This is recommended for users who are following many users. Provide a value of -1  to begin paging. Provide values as returned to in the response body's next_cursor  and previous_cursor attributes to page back and forth in the list.
	 */
	public function friendshipsIncoming($cursor = null)
	{
		// build parameters
		$parameters = null;
		if($cursor != null) $parameters['cursor'] = (string) $cursor;

		// make the call
		return (array) $this->doCall('friendships/incoming.json', $parameters, true);
	}


	/**
	 * Returns an array of numeric IDs for every protected user for whom the authenticating user has a pending follow request.
	 *
	 * @return	array
	 * @param	string[optional] $cursor	Breaks the results into pages. This is recommended for users who are following many users. Provide a value of -1  to begin paging. Provide values as returned to in the response body's next_cursor  and previous_cursor attributes to page back and forth in the list.
	 */
	public function friendshipsOutgoing($cursor = null)
	{
		// build parameters
		$parameters = null;
		if($cursor != null) $parameters['cursor'] = (string) $cursor;

		// make the call
		return (array) $this->doCall('friendships/outgoing.json', $parameters, true);
	}


// Friends and Followers resources
	/**
	 * Returns an array of numeric IDs for every user the specified user is following.
	 *
	 * @return	array
	 * @param 	string[optional] $userId		Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $screenName	Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	string[optional] $cursor	Breaks the results into pages. This is recommended for users who are following many users. Provide a value of -1  to begin paging. Provide values as returned to in the response body's next_cursor  and previous_cursor attributes to page back and forth in the list.
	 */
	public function friendsIds($userId = null, $screenName = null, $cursor = null)
	{
		// validate
		if($userId == '' && $screenName == '') throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		$parameters = null;
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($cursor != null) $parameters['cursor'] = (string) $cursor;

		// make the call
		return (array) $this->doCall('friends/ids.json', $parameters, true);
	}


	/**
	 * Returns an array of numeric IDs for every user following the specified user.
	 *
	 * @return	array
	 * @param 	string[optional] $userId		Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $screenName	Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	string[optional] $cursor	Breaks the results into pages. This is recommended for users who are following many users. Provide a value of -1  to begin paging. Provide values as returned to in the response body's next_cursor  and previous_cursor attributes to page back and forth in the list.
	 */
	public function followersIds($userId = null, $screenName = null, $cursor = null)
	{
		// validate
		if($userId == '' && $screenName == '') throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($cursor != null) $parameters['cursor'] = (string) $cursor;

		// make the call
		return (array) $this->doCall('followers/ids.json', $parameters, true);
	}


// Account resources
	/**
	 * Returns an HTTP 200 OK response code and a representation of the requesting user if authentication was successful; returns a 401 status code and an error message if not. Use this method to test if supplied user credentials are valid.
	 *
	 * @return	array
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function accountVerifyCredentials($includeEntities = false)
	{
		// build parameters
		$parameters = null;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('account/verify_credentials.json', $parameters, true);
	}


	/**
	 * Returns the remaining number of API requests available to the requesting user before the API limit is reached for the current hour. Calls to rate_limit_status do not count against the rate limit.
	 * If authentication credentials are provided, the rate limit status for the authenticating user is returned. Otherwise, the rate limit status for the requester's IP address is returned.
	 *
	 * @return	array
	 */
	public function accountRateLimitStatus()
	{
		// make the call
		return (array) $this->doCall('account/rate_limit_status.json', null);
	}


	/**
	 * Ends the session of the authenticating user, returning a null cookie. Use this method to sign users out of client-facing applications like widgets.
	 *
	 * @return	bool
	 */
	public function accountEndSession()
	{
		try
		{
			// make the call
			$this->doCall('account/end_session.json', null, true, 'POST');
		}

		// catch exceptions
		catch(TwitterException $e)
		{
			if($e->getMessage() == 'Logged out.') return true;
			else throw $e;
		}
	}


	/**
	 * Sets which device Twitter delivers updates to for the authenticating user. Sending none as the device parameter will disable IM or SMS updates.
	 *
	 * @return	array
	 * @param	string $device						Delivery device type to send updates to.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function accountUpdateDeliveryDevices($device, $includeEntities = false)
	{
		// build parameters
		$parameters['device'] = (string) $device;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('account/update_delivery_device.json', $parameters, true, 'POST');
	}


	/**
	 * Sets one or more hex values that control the color scheme of the authenticating user's profile page on twitter.com.
	 * Each parameter's value must be a valid hexidecimal value, and may be either three or six characters (ex: #fff or #ffffff).
	 *
	 * @return	array
	 * @param	string[optional] $profileBackgroundColor		Profile background color.
	 * @param	string[optional] $profileTextColor				Profile text color.
	 * @param	string[optional] $profileLinkColor				Profile link color.
	 * @param	string[optional] $profileSidebarFillColor		Profile sidebar's background color.
	 * @param	string[optional] $profileSidebarBorderColor		Profile sidebar's border color.
	 * @param	bool[optional] $includeEntities					When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function accountUpdateProfileColors($profileBackgroundColor = null, $profileTextColor = null, $profileLinkColor = null, $profileSidebarFillColor = null, $profileSidebarBorderColor = null, $includeEntities = false)
	{
		// validate
		if($profileBackgroundColor == '' && $profileTextColor == '' && $profileLinkColor == '' && $profileSidebarFillColor == '' && $profileSidebarBorderColor == '') throw new TwitterException('Specify a profileBackgroundColor, profileTextColor, profileLinkColor, profileSidebarFillColor or a profileSidebarBorderColor.');

		// build parameters
		if($profileBackgroundColor != null) $parameters['profile_background_color'] = (string) $profileBackgroundColor;
		if($profileTextColor != null) $parameters['profile_text_color'] = (string) $profileTextColor;
		if($profileLinkColor != null) $parameters['profile_link_color'] = (string) $profileLinkColor;
		if($profileSidebarFillColor != null) $parameters['profile_sidebar_fill_color'] = (string) $profileSidebarFillColor;
		if($profileSidebarBorderColor != null) $parameters['profile_sidebar_border_color'] = (string) $profileSidebarBorderColor;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('account/update_profile_colors.json', $parameters, true, 'POST');
	}


	/**
	 * Updates the authenticating user's profile image.
	 *
	 * @return	array
	 * @param	string $image						The path to the avatar image for the profile. Must be a valid GIF, JPG, or PNG image of less than 700 kilobytes in size. Images with width larger than 500 pixels will be scaled down.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function accountUpdateProfileImage($image, $includeEntities = false)
	{
		// validate
		if(!file_exists($image)) throw new TwitterException('Image (' . $image . ') doesn\'t exists.');

		// build parameters
		$parameters = null;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('account/update_profile_image.json', $parameters, true, 'POST', $image);
	}


	/**
	 * Updates the authenticating user's profile background image.
	 *
	 * @return	array
	 * @param	string $image						The path to the background image for the profile. Must be a valid GIF, JPG, or PNG image of less than 800 kilobytes in size. Images with width larger than 2048 pixels will be forceably scaled down.
	 * @param	bool[optional] $tile				Whether or not to tile the background image. If set to true the background image will be displayed tiled. The image will not be tiled otherwise.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function accountUpdateProfileBackgroundImage($image, $tile = false, $includeEntities = false)
	{
		// validate
		if(!file_exists($image)) throw new TwitterException('Image (' . $image . ') doesn\'t exists.');

		// build parameters
		$parameters = null;
		if($tile) $parameters['tile'] = 'true';
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('account/update_profile_background_image.json', $parameters, true, 'POST', $image);
	}


	/**
	 * Sets values that users are able to set under the "Account" tab of their settings page. Only the parameters specified will be updated.
	 *
	 * @return	array
	 * @param	string[optional] $name			Full name associated with the profile. Maximum of 20 characters.
	 * @param	string[optional] $url			URL associated with the profile. Will be prepended with "http://" if not present. Maximum of 100 characters.
	 * @param	string[optional] $location		The city or country describing where the user of the account is located. The contents are not normalized or geocoded in any way. Maximum of 30 characters.
	 * @param	string[optional] $description	A description of the user owning the account. Maximum of 160 characters.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function accountUpdateProfile($name = null, $url = null, $location = null, $description = null, $includeEntities = false)
	{
		// build parameters
		$parameters = null;
		if($name != null) $parameters['name'] = (string) $name;
		if($url != null) $parameters['url'] = (string) $url;
		if($location != null) $parameters['location'] = (string) $location;
		if($description != null) $parameters['description'] = (string) $description;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('account/update_profile.json', $parameters, true, 'POST');
	}


// Favorites resources
	/**
	 * Returns the 20 most recent favorite statuses for the authenticating user or user specified by the ID parameter in the requested format.
	 *
	 * @return	array
	 * @param	string[optional] $id				Specifies the ID or screen name of the user for whom to return results for.
	 * @param	int[optional] $page					Specifies the page of results to retrieve.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function favorites($id = null, $page = null, $includeEntities = false)
	{
		// build parameters
		$parameters = null;
		if($id != null) $parameters['id'] = (string) $id;
		if($page != null) $parameters['page'] = (int) $page;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('favorites.json', $parameters, true);
	}


	/**
	 * Favorites the status specified in the ID parameter as the authenticating user. Returns the favorite status when successful.
	 *
	 * @return	array
	 * @param	string $id							The numerical ID of the desired status.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function favoritesCreate($id, $includeEntities = false)
	{
		// build parameters
		$parameters = null;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('favorites/create/' . $id . '.json', $parameters, true, 'POST');
	}


	/**
	 * Un-favorites the status specified in the ID parameter as the authenticating user. Returns the un-favorited status in the requested format when successful.
	 *
	 * @return	array
	 * @param	string $id							The numerical ID of the desired status.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function favoritesDestroy($id, $includeEntities = false)
	{
		// build parameters
		$parameters = null;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('favorites/destroy/' . $id . '.json', $parameters, true, 'POST');
	}


// Notification resources
	/**
	 * Enables device notifications for updates from the specified user. Returns the specified user when successful.
	 *
	 * @return	array
	 * @param 	string[optional] $userId			Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $screenName		Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function notificationsFollow($userId = null, $screenName = null, $includeEntities = false)
	{
		// validate
		if($userId == '' && $screenName == '') throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('notifications/follow.json', $parameters, true, 'POST');
	}


	/**
	 * Disables notifications for updates from the specified user to the authenticating user. Returns the specified user when successful.
	 *
	 * @return	array
	 * @param 	string[optional] $userId			Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $screenName		Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function notificationsLeave($userId = null, $screenName = null, $includeEntities = false)
	{
		// validate
		if($userId == '' && $screenName == '') throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('notifications/leave.json', $parameters, true, 'POST');
	}


// Block resources
	/**
	 * Blocks the user specified in the ID parameter as the authenticating user. Destroys a friendship to the blocked user if it exists. Returns the blocked user in the requested format when successful.
	 *
	 * @return	array
	 * @param 	string[optional] $userId			Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $screenName		Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function blocksCreate($userId = null, $screenName = null, $includeEntities = false)
	{
		// validate
		if($userId == '' && $screenName == '') throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('blocks/create.json', $parameters, true, 'POST');
	}


	/**
	 * Un-blocks the user specified in the ID parameter for the authenticating user. Returns the un-blocked user in the requested format when successful.
	 *
	 * @return	array
	 * @param 	string[optional] $userId			Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $screenName		Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function blocksDestroy($userId = null, $screenName = null, $includeEntities = false)
	{
		// validate
		if($userId == '' && $screenName == '') throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('blocks/destroy.json', $parameters, true, 'POST');
	}


	/**
	 * Un-blocks the user specified in the ID parameter for the authenticating user. Returns the un-blocked user in the requested format when successful.
	 *
	 * @return	mixed
	 * @param 	string[optional] $userId			Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $screenName		Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function blocksExists($userId = null, $screenName = null, $includeEntities = false)
	{
		// validate
		if($userId == '' && $screenName == '') throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($includeEntities) $parameters['include_entities'] = 'true';

		try
		{
			// make the call
			return (array) $this->doCall('blocks/exists.json', $parameters, true);
		}
		// catch exceptions
		catch(TwitterException $e)
		{
			if($e->getMessage() == 'You are not blocking this user.') return false;
			else throw $e;
		}
	}


	/**
	 * Returns an array of user objects that the authenticating user is blocking.
	 *
	 * @return	array
	 * @param	int[optional] $page					Specifies the page of results to retrieve. Note: there are pagination limits. See the FAQ for details.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function blocksBlocking($page = null, $includeEntities = false)
	{
		// build parameters
		$parameters = null;
		if($page != null) $parameters['page'] = (int) $page;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('blocks/blocking.json', $parameters, true);
	}


	/**
	 * Returns an array of numeric user ids the authenticating user is blocking.
	 *
	 * @return	array
	 */
	public function blocksBlockingIds()
	{
		// make the call
		return (array) $this->doCall('blocks/blocking/ids.json', null, true);
	}


// Spam Reporting resources
	/**
	 * The user specified in the id is blocked by the authenticated user and reported as a spammer.
	 *
	 * @return	array
	 * @param 	string[optional] $userId		Specfies the screen name of the user for whom to return results for. Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param	string[optional] $screenName	Specfies the ID of the user for whom to return results for. Helpful for disambiguating when a valid user ID is also a valid screen name.
	 * @param	bool[optional] $includeEntities		When set to true each tweet will include a node called "entities,". This node offers a variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 */
	public function reportSpam($userId = null, $screenName = null, $includeEntities = false)
	{
		// validate
		if($userId == '' && $screenName == '') throw new TwitterException('Specify an userId or a screenName.');

		// build parameters
		if($userId != null) $parameters['user_id'] = (string) $userId;
		if($screenName != null) $parameters['screen_name'] = (string) $screenName;
		if($includeEntities) $parameters['include_entities'] = 'true';

		// make the call
		return (array) $this->doCall('report_spam.json', $parameters, true, 'POST');
	}


// Search resources
	/**
	 * Returns tweets that match a specified query.
	 *
	 * @return	array
	 * @param	string $q						Search query. Should be URL encoded. Queries will be limited by complexity.
	 * @param 	string[optional] $lang			Restricts tweets to the given language, given by an ISO 639-1 code.
	 * @param 	string[optional] $locale		Specify the language of the query you are sending (only ja is currently effective). This is intended for language-specific clients and the default should work in the majority of cases.
	 * @param 	int[optional] $rpp				The number of tweets to return per page, up to a max of 100.
	 * @param 	int[optional] $page				The page number (starting at 1) to return, up to a max of roughly 1500 results (based on rpp * page).
	 * @param 	string[optional] $sinceId		Returns results with an ID greater than (that is, more recent than) the specified ID. There are limits to the number of Tweets which can be accessed through the API. If the limit of Tweets has occurred since the since_id, the since_id will be forced to the oldest ID available.
	 * @param 	string[optional] $until			Returns tweets generated before the given date. Date should be formatted as YYYY-MM-DD.
	 * @param 	string[optional] $geocode		Returns tweets by users located within a given radius of the given latitude/longitude. The location is preferentially taking from the Geotagging API, but will fall back to their Twitter profile. The parameter value is specified by "latitude,longitude,radius", where radius units must be specified as either "mi" (miles) or "km" (kilometers). Note that you cannot use the near operator via the API to geocode arbitrary locations; however you can use this geocode parameter to search near geocodes directly.
	 * @param 	bool[optional] $showUser		When true, prepends ":" to the beginning of the tweet. This is useful for readers that do not display Atom's author field. The default is false.
	 * @param 	string[optional] $resultType	Specifies what type of search results you would prefer to receive. The current default is "mixed." Valid values include: mixed, recent, popular.
	 */
	public function search($q, $lang = null, $locale = null, $rpp = null, $page = null, $sinceId = null, $until = null, $geocode = null, $showUser = false, $resultType = null)
	{
		$parameters['q'] = (string) $q;
		if($lang !== null) $parameters['lang'] = (string) $lang;
		if($locale !== null) $parameters['locale'] = (string) $locale;
		if($rpp !== null) $parameters['rpp'] = (int) $rpp;
		if($page !== null) $parameters['page'] = (int) $page;
		if($sinceId !== null) $parameters['since_id'] = (string) $sinceId;
		if($until !== null) $parameters['until'] = (string) $until;
		if($geocode !== null) $parameters['geocode'] = (string) $geocode;
		if($showUser === true) $parameters['show_user'] = 'true';
		if($resultType !== null) $parameters['result_type'] = (string) $resultType;

		return (array) $this->doSearchCall('search.json', $parameters);
	}


// Saved Searches resources
	/**
	 * Returns the authenticated user's saved search queries.
	 *
	 * @return	array
	 */
	public function savedSearches()
	{
		// make the call
		return (array) $this->doCall('saved_searches.json', null, true);
	}


	/**
	 * Retrieve the data for a saved search owned by the authenticating user specified by the given id.
	 *
	 * @return	array
	 * @param	string $id	The ID of the desired saved search.
	 */
	public function savedSearchesShow($id)
	{
		// make the call
		return (array) $this->doCall('saved_searches/show/' . (string) $id . '.json', null, true);
	}


	/**
	 * Creates a saved search for the authenticated user.
	 *
	 * @return	array
	 * @param	string $query	The query of the search the user would like to save.
	 */
	public function savedSearchesCreate($query)
	{
		// build parameters
		$parameters['query'] = (string) $query;

		// make the call
		return (array) $this->doCall('saved_searches/create.json', $parameters, true, 'POST');
	}


	/**
	 * Destroys a saved search for the authenticated user. The search specified by id must be owned by the authenticating user.
	 *
	 * @return	array
	 * @param	string $id	The ID of the desired saved search.
	 */
	public function savedSearchesDestroy($id)
	{
		return (array) $this->doCall('saved_searches/destroy/' . (string) $id . '.json', null, true, 'POST');
	}


// OAuth resources
	/**
	 * Allows a Consumer application to obtain an OAuth Request Token to request user authorization.
	 * This method fulfills Secion 6.1 of the OAuth 1.0 authentication flow.
	 *
	 * @return	array							An array containg the token and the secret
	 * @param	string[optional] $callbackURL	The callback URL.
	 */
	public function oAuthRequestToken($callbackURL = null)
	{
		// init var
		$parameters = null;

		// set callback
		if($callbackURL != null) $parameters['oauth_callback'] = (string) $callbackURL;

		// make the call
		$response = $this->doOAuthCall('request_token', $parameters);

		// validate
		if(!isset($response['oauth_token'], $response['oauth_token_secret'])) throw new TwitterException(implode(', ', array_keys($response)));

		// set some properties
		if(isset($response['oauth_token'])) $this->setOAuthToken($response['oauth_token']);
		if(isset($response['oauth_token_secret'])) $this->setOAuthTokenSecret($response['oauth_token_secret']);

		// return
		return $response;
	}


	/**
	 * Allows a Consumer application to exchange the OAuth Request Token for an OAuth Access Token.
	 * This method fulfills Secion 6.3 of the OAuth 1.0 authentication flow.
	 *
	 * @return	array
	 * @param	string $token		The token to use.
	 * @param	string $verifier	The verifier.
	 */
	public function oAuthAccessToken($token, $verifier)
	{
		// init var
		$parameters = array();
		$parameters['oauth_token'] = (string) $token;
		$parameters['oauth_verifier'] = (string) $verifier;

		// make the call
		$response = $this->doOAuthCall('access_token', $parameters);

		// set some properties
		if(isset($response['oauth_token'])) $this->setOAuthToken($response['oauth_token']);
		if(isset($response['oauth_token_secret'])) $this->setOAuthTokenSecret($response['oauth_token_secret']);

		// return
		return $response;
	}


	/**
	 * Will redirect to the page to authorize the applicatione
	 *
	 * @return	void
	 * @param	string	$token		The token.
	 */
	public function oAuthAuthorize($token)
	{
		header('Location: ' . self::SECURE_API_URL . '/oauth/authorize?oauth_token=' . $token);
	}


	/**
	 * Allows a Consumer application to use an OAuth request_token to request user authorization. This method is a replacement fulfills Secion 6.2 of the OAuth 1.0 authentication flow for applications using the Sign in with Twitter authentication flow. The method will use the currently logged in user as the account to for access authorization unless the force_login parameter is set to true
	 * REMARK: This method seems not to work	@later
	 *
	 * @return	void
	 * @param	bool[optional] $force	Force the authentication.
	 */
	public function oAuthAuthenticate($force = false)
	{
		throw new TwitterException('Not implemented');

		// build parameters
		$parameters = null;
		if((bool) $force) $parameters['force_login'] = 'true';

		// make the call
		return $this->doCall('/oauth/authenticate.oauth', $parameters);
	}


// Local Trends resources
	/**
	 * Returns the locations that Twitter has trending topic information for.
	 * The response is an array of "locations" that encode the location's WOEID (a Yahoo! Where On Earth ID) and some other human-readable information such as a canonical name and country the location belongs in.
	 * The WOEID that is returned in the location object is to be used when querying for a specific trend.
	 *
	 * @return	array
	 * @param	float[optional] $lat	If passed in conjunction with long, then the available trend locations will be sorted by distance to the lat  and long passed in. The sort is nearest to furthest.
	 * @param	float[optional] $long	If passed in conjunction with lat, then the available trend locations will be sorted by distance to the lat  and long passed in. The sort is nearest to furthest.
	 */
	public function trendsAvailable($lat = null, $long = null)
	{
		// build parameters
		$parameters = null;
		if($lat != null) $parameters['lat_for_trends'] = (float) $lat;
		if($long != null) $parameters['long_for_trends'] = (float) $long;

		// make the call
		return (array) $this->doCall('trends/available.json', $parameters);
	}


	/**
	 * Returns the top 10 trending topics for a specific location Twitter has trending topic information for.
	 * The response is an array of "trend" objects that encode the name of the trending topic, the query parameter that can be used to search for the topic on Search, and the direct URL that can be issued against Search.
	 * This information is cached for five minutes, and therefore users are discouraged from querying these endpoints faster than once every five minutes. Global trends information is also available from this API by using a WOEID of 1.
	 *
	 * @return	array
	 * @param	string $woeid	The WOEID of the location to be querying for.
	 */
	public function trendsLocation($woeid)
	{
		// make the call
		return (array) $this->doCall('trends/' . (string) $woeid . '.json');
	}


// Geo resources
	/**
	 * Search for places that can be attached to a statuses/update. Given a latitude and a longitude pair, an IP address, or a name, this request will return a list of all the valid places that can be used as the place_id when updating a status.
	 * Conceptually, a query can be made from the user's location, retrieve a list of places, have the user validate the location he or she is at, and then send the ID of this location with a call to statuses/update.
	 * This is the recommended method to use find places that can be attached to statuses/update. Unlike geo/reverse_geocode which provides raw data access, this endpoint can potentially re-order places with regards to the user who is authenticated. This approach is also preferred for interactive place matching with the user.
	 *
	 * @return	array
	 * @param	float[optional] $lat				The latitude to search around. This parameter will be ignored unless it is inside the range -90.0 to +90.0 (North is positive) inclusive. It will also be ignored if there isn't a corresponding long parameter.
	 * @param	float[optional] $long				The longitude to search around. The valid ranges for longitude is -180.0 to +180.0 (East is positive) inclusive. This parameter will be ignored if outside that range, if it is not a number, if geo_enabled is disabled, or if there not a corresponding lat parameter.
	 * @param	string[optional] $query				Free-form text to match against while executing a geo-based query, best suited for finding nearby locations by name.
	 * @param	string[optional] $ip				An IP address. Used when attempting to fix geolocation based off of the user's IP address.
	 * @param	string[optional] $accuracy			A hint on the "region" in which to search. If a number, then this is a radius in meters, but it can also take a string that is suffixed with ft to specify feet. If this is not passed in, then it is assumed to be 0m. If coming from a device, in practice, this value is whatever accuracy the device has measuring its location (whether it be coming from a GPS, WiFi triangulation, etc.).
	 * @param	string[optional] $granularity		The minimal granularity of data to return. If this is not passed in, then neighborhood is assumed. city can also be passed.
	 * @param	int[optional] $maxResults			A hint as to the number of results to return. This does not guarantee that the number of results returned will equal max_results, but instead informs how many "nearby" results to return. Ideally, only pass in the number of places you intend to display to the user here.
	 * @param	string[optional] $containedWithin	This is the place_id which you would like to restrict the search results to. Setting this value means only places within the given place_id will be found.
	 * @param	array[optional] $attributes			This parameter searches for places which have this given. This should be an key-value-pair-array.
	 */
	public function geoSearch($lat = null, $long = null, $query = null, $ip = null, $accuracy = null, $granularity = null, $maxResults = null, $containedWithin = null, array $attributes = null)
	{
		// build parameters
		if($lat != null) $parameters['lat'] = (float) $lat;
		if($long != null) $parameters['long'] = (float) $long;
		if($query != null) $parameters['query'] = (string) $query;
		if($ip != null) $parameters['ip'] = (string) $ip;
		if($accuracy != null) $parameters['accuracy'] = (string) $accuracy;
		if($granularity != null) $parameters['granularity'] = (string) $granularity;
		if($maxResults != null) $parameters['max_results'] = (int) $maxResults;
		if($containedWithin != null) $parameters['contained_within'] = (string) $containedWithin;
		if($attributes != null)
		{
			// loop
			foreach($attributes as $key => $value) $parameters['attribute:' . $key] = (string) $value;
		}

		// make the call
		return (array) $this->doCall('geo/search.json', $parameters);
	}


	/**
	 * Locates places near the given coordinates which are similar in name.
	 * Conceptually you would use this method to get a list of known places to choose from first. Then, if the desired place doesn't exist, make a request to post/geo/place to create a new one.
	 * The token contained in the response is the token needed to be able to create a new place.
	 *
	 * @return	array
	 * @param	float $lat							The location's latitude that this tweet refers to.
	 * @param	float $long							The location's longitude that this tweet refers to.
	 * @param	string $name						The name a place is known as.
	 * @param	string[optional] $containedWithin	This is the place_id which you would like to restrict the search results to. Setting this value means only places within the given place_id will be found.
	 * @param	array[optional] $attributes			This parameter searches for places which have this given. This should be an key-value-pair-array.
	 */
	public function geoSimilarPlaces($lat, $long, $name, $containedWithin = null, array $attributes = null)
	{
		// build parameters
		$parameters['lat'] = (float) $lat;
		$parameters['long'] = (float) $long;
		$parameters['name'] = (string) $name;
		if($containedWithin != null) $parameters['contained_within'] = (string) $containedWithin;
		if($attributes != null)
		{
			// loop
			foreach($attributes as $key => $value) $parameters['attribute:' . $key] = (string) $value;
		}

		// make the call
		return (array) $this->doCall('geo/similar_places.json', $parameters);
	}


	/**
	 * Search for places (cities and neighborhoods) that can be attached to a statuses/update. Given a latitude and a longitude, return a list of all the valid places that can be used as a place_id when updating a status.
	 * Conceptually, a query can be made from the user's location, retrieve a list of places, have the user validate the location he or she is at, and then send the ID of this location up with a call to statuses/update.
	 * There are multiple granularities of places that can be returned -- "neighborhoods", "cities", etc. At this time, only United States data is available through this method.
	 * This API call is meant to be an informative call and will deliver generalized results about geography.
	 *
	 * @return	array
	 * @param	float $lat						The location's latitude that this tweet refers to.
	 * @param	float $long						The location's longitude that this tweet refers to.
	 * @param	string[optional] $accuracy		A hint on the "region" in which to search. If a number, then this is a radius in meters, but it can also take a string that is suffixed with ft to specify feet. If this is not passed in, then it is assumed to be 0m. If coming from a device, in practice, this value is whatever accuracy the device has measuring its location (whether it be coming from a GPS, WiFi triangulation, etc.).
	 * @param	string[optional] $granularity	The minimal granularity of data to return. If this is not passed in, then neighborhood is assumed. city can also be passed.
	 * @param	int[optional] $maxResults		A hint as to the number of results to return. This does not guarantee that the number of results returned will equal max_results, but instead informs how many "nearby" results to return. Ideally, only pass in the number of places you intend to display to the user here.
	 */
	public function geoReverseGeoCode($lat, $long, $accuracy = null, $granularity = null, $maxResults = null)
	{
		// build parameters
		$parameters['lat'] = (float) $lat;
		$parameters['long'] = (float) $long;
		if($accuracy != null) $parameters['accuracy'] = (string) $accuracy;
		if($granularity != null) $parameters['granularity'] = (string) $granularity;
		if($maxResults != null) $parameters['max_results'] = (int) $maxResults;

		// make the call
		return (array) $this->doCall('geo/reverse_geocode.json', $parameters);
	}


	/**
	 * Find out more details of a place that was returned from the geo/reverse_geocode method.
	 *
	 * @return	array
	 * @param	string $id					The id of the place.
	 * @param	string[optional] $placeId	A place in the world. These IDs can be retrieved from geo/reverse_geocode.
	 */
	public function geoId($id, $placeId = null)
	{
		// build parameters
		$parameters = null;
		if($placeId != null) $parameters['place_id'] = (string) $placeId;

		// make the call
		return (array) $this->doCall('geo/id/' . (string) $id . '.json', $parameters);
	}


	/**
	 * Creates a new place at the given latitude and longitude.
	 *
	 * @return	array
	 * @param	string $name					The name a place is known as.
	 * @param	string $containedWithin			This is the place_id which you would like to restrict the search results to. Setting this value means only places within the given place_id will be found.
	 * @param	string $token					The token found in the response from geo/similar_places.
	 * @param	float $lat						The latitude the place is located at. This parameter will be ignored unless it is inside the range -90.0 to +90.0 (North is positive) inclusive. It will also be ignored if there isn't a corresponding long parameter.
	 * @param	float $long						The longitude the place is located at. The valid ranges for longitude is -180.0 to +180.0 (East is positive) inclusive. This parameter will be ignored if outside that range, if it is not a number, if geo_enabled is disabled, or if there not a corresponding lat parameter.
	 * @param	array[optional] $attributes		This parameter searches for places which have this given. This should be an key-value-pair-array.
	 */
	public function geoPlaceCreate($name, $containedWithin, $token, $lat, $long, array $attributes = null)
	{
		// build parameters
		$parameters['name'] = (string) $name;
		$parameters['contained_within'] = (string) $containedWithin;
		$parameters['token'] = (string) $token;
		$parameters['lat'] = (float) $lat;
		$parameters['long'] = (float) $long;
		if($attributes != null)
		{
			// loop
			foreach($attributes as $key => $value) $parameters['attribute:' . $key] = (string) $value;
		}

		// make the call
		return (array) $this->doCall('geo/place.json', $parameters, true, 'POST');
	}


// legal resources
	/**
	 * Returns Twitter's' Terms of Service in the requested format. These are not the same as the Developer Terms of Service.
	 *
	 * @return	string
	 */
	public function legalToS()
	{
		// make the call
		$response = $this->doCall('legal/tos.json');

		// validate and return
		if(isset($response['tos'])) return $response['tos'];

		// fallback
		return false;
	}


	/**
	 * Returns Twitter's Privacy Policy
	 *
	 * @return	string
	 */
	public function legalPrivacy()
	{
		// make the call
		$response = $this->doCall('legal/privacy.json');

		// validate and return
		if(isset($response['privacy'])) return $response['privacy'];

		// fallback
		return false;
	}


// Help resources
	/**
	 * Test
	 *
	 * @return	bool
	 */
	public function helpTest()
	{
		// make the call
		return ($this->doCall('help/test.json', null, null, 'GET', null, false) == '"ok"');
	}
}


/**
 * Twitter Exception class
 *
 * @author	Tijs Verkoyen <php-twitter@verkoyen.eu>
 */
class TwitterException extends Exception
{
}

?>