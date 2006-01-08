<?php
/**
* PHP Text Cache is a simple caching class for for saving/retrieving local copies of url data
* @package php_text_cache
* @version 0.3-pre
* @copyright (C) 2004 John Heinstein. All rights reserved
* @license  http://www.gnu.org/copyleft/lesser.html LGPL License
* @author John Heinstein <johnkarl@nbnet.nb.ca>
* @link http://www.engageinteractive.com/php_text_cache/ PHP Text Cache Home Page
* PHP Text Cache is Free Software
**/

if (!defined('PHP_TEXT_CACHE_INCLUDE_PATH')) {
	define('PHP_TEXT_CACHE_INCLUDE_PATH', (dirname(__FILE__) . "/"));
}

require_once(PHP_TEXT_CACHE_INCLUDE_PATH . 'php_http_connector.php');

/**
* A simple caching class for saving/retrieving local copies of url data
*
* @package php_text_cache
* @author John Heinstein <johnkarl@nbnet.nb.ca>
*/
class php_text_cache extends php_http_connector {
	/** @var string The directory in which cached files are stored */
	var $cacheDir;
	/** @var int The amount time of time to wait before a cached file should be updated  */
	var $cacheTime;
	/** @var boolean True if an HTTP client should be used to establish the connection  */
	var $doUseHTTPClient;
	/** @var int Time in seconds to disconnect after attempting an http connection  */
	var $httpTimeout;

	/**
	* Constructor
	* @param string Directory in which to store the cache files
	* @param int Expiry time for cache file (-1 signifies no expiry limit)
	* @param int Time in seconds to disconnect after attempting an http connection
	*/
	function php_text_cache($cacheDir = './', $cacheTime = -1, $timeout = 0) {
		$this->cacheDir = $cacheDir;
		$this->cacheTime = $cacheTime;
		$this->timeout = $timeout;
	} //php_text_cache

	/**
	* Specifies the default timeout value for connecting to a host
	* @param int The number of seconds to timeout when attempting to connect to a server
	*/
	function setTimeout($timeout) {
		$this->timeout = $timeout;
	} //setTimeout

	/**
	* Gets data from an url, or its cache file
	*
	* @param string The url of the data
	* @return string The data at the specified url
	*/
	function getData($url) {
		$cacheFile = $this->getCacheFileName($url);

		if (is_file($cacheFile)) {
			$fileStats = stat($cacheFile);
			$lastChangeTime = $fileStats[9]; //mtime
			$currTime = time();

			if (($this->cacheTime != -1) && ($currTime - $lastChangeTime) > $this->cacheTime) { //get data from url
				return $this->fromURL($url, $cacheFile);
			}
			else { //get data from file
				return $this->fromCache($cacheFile);
			}
		}
		else {
			return $this->fromURL($url, $cacheFile);
		}
	} //getData

	/**
	* Given an url, returns the path to the cache file
	*
	* Uses an md5 hash of the url. This can be
	* overridden if a different approach is required
	*
	* @param string The url of the data
	* @return string The cache file name
	*/
	function getCacheFileName($url) {
		return ($this->cacheDir . md5($url));
	} //getCacheFileName

	/**
	* Establishes a connection, given an url
	* @param string The url of the data
	*/
	function establishConnection($url) {
		require_once(PHP_TEXT_CACHE_INCLUDE_PATH . 'php_http_client_generic.php');

		$host = php_http_connection::formatHost($url);
		$host = substr($host, 0, strpos($host, '/'));

		$this->setConnection($host, '/', 80, $this->timeout);
	} //establishConnection

	/**
	* Specifies whether an HTTP client should be used to establish a connection
	* @param boolean True if an HTTP client is to be used to establish the connection
	*/
	function useHTTPClient($truthVal) {
		// fixes bug identified here: sarahk.pcpropertymanager.com/blog/using-domit-rss/225/
		//$this->doUseHTTPClient = truthVal;
		$this->doUseHTTPClient = $truthVal;
	} //useHTTPClient

	/**
	* Gets data from an url and caches a copy of the data
	* @param string The url for the data
	* @param string The cache file path
	* @return string The contents of the url
	*/
	function fromURL($url, $cacheFile) {
		$fileContents = '';

		if ($this->httpConnection != null) {
			$response =& $this->httpConnection->get($url);

			if ($response != null) {
				$fileContents = $response->getResponse();
			}
		}
		else if ($this->doUseHTTPClient) {
			$this->establishConnection($url);
			$response =& $this->httpConnection->get($url);

			if ($response != null) {
				$fileContents = $response->getResponse();
			}
		}
		else {
			$fileContents = $this->fromFile($url);
		}

		//if file is empty, might need to establish an
		//http connection to get the data
		if (($fileContents == '') && !$this->doUseHTTPClient) {
			$this->establishConnection($url);
			$response =& $this->httpConnection->get($url);

			if ($response != null) {
				$fileContents = $response->getResponse();
			}
		}

		if ($fileContents != '') {
			require_once(PHP_TEXT_CACHE_INCLUDE_PATH . 'php_file_utilities.php');
			php_file_utilities::putDataToFile($cacheFile, $fileContents, 'w');
		}

		return $fileContents;
	} //fromURL

	/**
	* Get text from cache file
	* @param string The file path
	* @return string The text contained in the file, or an empty string
	*/
	function fromCache($cacheFile) {
		return $this->fromFile($cacheFile);
	} //fromCache

	/**
	* Get text from an url or file
	* @param string The url or file path
	* @return string The text contained in the url or file, or an empty string
	*/
	function fromFile($filename) {
		if (function_exists('file_get_contents')) {
			return @file_get_contents($filename);
		}
		else {
			require_once(PHP_TEXT_CACHE_INCLUDE_PATH . 'php_file_utilities.php');
			$fileContents =& php_file_utilities::getDataFromFile($filename, 'r');
			return $fileContents;
		}

		return '';
	} //fromFile
} //php_text_cache

?>