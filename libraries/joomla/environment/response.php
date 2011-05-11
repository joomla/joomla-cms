<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Environment
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JResponse Class.
 *
 * This class serves to provide the Joomla Framework with a common interface to access
 * response variables.  This includes header and body.
 *
 * @package     Joomla.Platform
 * @subpackage  Environment
 * @since       11.1
 */
class JResponse
{
	/**
	 * @var    array  Body
	 * @since  11.1
	 */
	protected static $body = array();

	/**
	 * @var    boolean  Cachable
	 * @since  11.1
	 */
	protected static $cachable = false;

	/**
	 * @var    array  Headers
	 * @since  11.1
	 */
	protected static $headers = array();

	/**
	 * Set/get cachable state for the response.
	 *
	 * If $allow is set, sets the cachable state of the response.  Always returns current state.
	 *
	 * @param   boolean  $allow
	 *
	 * @return  boolean  True of browser caching should be allowed
	 * @since   11.1
	 */
	public static function allowCache($allow = null)
	{
		if (!is_null($allow)) {
			self::$cachable = (bool) $allow;
		}

		return self::$cachable;
	}

	/**
	 * Set a header.
	 *
	 * If $replace is true, replaces any headers already defined with that $name.
	 *
	 * @param   string   $name
	 * @param   string   $value
	 * @param   boolean  $replace
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function setHeader($name, $value, $replace = false)
	{
		$name	= (string) $name;
		$value	= (string) $value;

		if ($replace) {
			foreach (self::$headers as $key => $header)
			{
				if ($name == $header['name']) {
					unset(self::$headers[$key]);
				}
			}
		}

		self::$headers[] = array(
			'name'	=> $name,
			'value'	=> $value
		);
	}

	/**
	 * Return array of headers.
	 *
	 * @return  array
	 * @since   11.1
	 */
	public static function getHeaders()
	{
		return  self::$headers;
	}

	/**
	 * Clear headers.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function clearHeaders()
	{
		self::$headers = array();
	}

	/**
	 * Send all headers.
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function sendHeaders()
	{
		if (!headers_sent()) {
			foreach (self::$headers as $header)
			{
				if ('status' == strtolower($header['name'])) {
					// 'status' headers indicate an HTTP status, and need to be handled slightly differently
					header(ucfirst(strtolower($header['name'])) . ': ' . $header['value'], null, (int) $header['value']);
				}
				else {
					header($header['name'] . ': ' . $header['value']);
				}
			}
		}
	}

	/**
	 * Set body content.
	 *
	 * If body content already defined, this will replace it.
	 *
	 * @param   string   $content
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function setBody($content)
	{
		self::$body = array((string) $content);
	}

	/**
	 * Prepend content to the body content
	 *
	 * @param   string   $content
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function prependBody($content)
	{
		array_unshift(self::$body, (string) $content);
	}

	/**
	 * Append content to the body content
	 *
	 * @param   string   $content
	 *
	 * @return  void
	 * @since   11.1
	 */
	public static function appendBody($content)
	{
		array_push(self::$body, (string) $content);
	}

	/**
	 * Return the body content
	 *
	 * @param   boolean  $toArray	Whether or not to return the body content as an array of strings or as a single string; defaults to false.
	 *
	 * @return  string  array
	 * @since   11.1
	 */
	public static function getBody($toArray = false)
	{
		if ($toArray) {
			return self::$body;
		}

		ob_start();
		foreach (self::$body as $content)
		{
			echo $content;
		}

		return ob_get_clean();
	}

	/**
	 * Sends all headers prior to returning the string
	 *
	 * @param   boolean  $compress	If true, compress the data
	 *
	 * @return  string
	 * @since   11.1
	 */
	public static function toString($compress = false)
	{
		$data = self::getBody();

		// Don't compress something if the server is going to do it anyway. Waste of time.
		if ($compress && !ini_get('zlib.output_compression') && ini_get('output_handler')!='ob_gzhandler') {
			$data = self::compress($data);
		}

		if (self::allowCache() === false) {
			self::setHeader('Cache-Control', 'no-cache', false);
			// HTTP 1.0
			self::setHeader('Pragma', 'no-cache');
		}

		self::sendHeaders();

		return $data;
	}

	/**
	 * Compress the data
	 *
	 * Checks the accept encoding of the browser and compresses the data before
	 * sending it to the client.
	 *
	 * @param   string  $data	data
	 *
	 * @return  string  compressed data
	 *
	 * @note    Replaces _compress method in 11.1
	 * @since   11.1
	 */
	protected static function compress($data)
	{
		$encoding = self::clientEncoding();

		if (!$encoding) {
			return $data;
		}

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
			return $data;
		}

		if (headers_sent()) {
			return $data;
		}

		if (connection_status() !== 0) {
			return $data;
		}

		// Ideal level
		$level = 4;

		/*
		$size		= strlen($data);
		$crc		= crc32($data);

		$gzdata		= "\x1f\x8b\x08\x00\x00\x00\x00\x00";
		$gzdata		.= gzcompress($data, $level);

		$gzdata	= substr($gzdata, 0, strlen($gzdata) - 4);
		$gzdata	.= pack("V",$crc) . pack("V", $size);
		*/

		$gzdata = gzencode($data, $level);

		self::setHeader('Content-Encoding', $encoding);
		self::setHeader('X-Content-Encoded-By', 'Joomla! 1.6');

		return $gzdata;
	}

	/**
	 * Check, whether client supports compressed data
	 *
	 * @return  boolean
	 * @since   11.1
	 * @note    Replaces _clientEncoding method from 11.1
	 */
	protected static function clientEncoding()
	{
		if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			return false;
		}

		$encoding = false;

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			$encoding = 'gzip';
		}

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip')) {
			$encoding = 'x-gzip';
		}

		return $encoding;
	}
}
