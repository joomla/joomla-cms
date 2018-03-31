<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Response
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JResponse is deprecated.', JLog::WARNING, 'deprecated');

/**
 * JResponse Class.
 *
 * This class serves to provide the Joomla Platform with a common interface to access
 * response variables.  This includes header and body.
 *
 * @since       11.1
 * @deprecated  1.5  Use JApplicationWeb instead
 */
class JResponse
{
	/**
	 * Response body
	 *
	 * @var    array
	 * @since  1.6
	 * @deprecated  3.2
	 */
	protected static $body = array();

	/**
	 * Flag if the response is cachable
	 *
	 * @var    boolean
	 * @since  1.6
	 * @deprecated  3.2
	 */
	protected static $cachable = false;

	/**
	 * Response headers
	 *
	 * @var    array
	 * @since  1.6
	 * @deprecated  3.2
	 */
	protected static $headers = array();

	/**
	 * Set/get cachable state for the response.
	 *
	 * If $allow is set, sets the cachable state of the response.  Always returns current state.
	 *
	 * @param   boolean  $allow  True to allow browser caching.
	 *
	 * @return  boolean  True if browser caching should be allowed
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use JApplicationWeb::allowCache() instead
	 */
	public static function allowCache($allow = null)
	{
		return JFactory::getApplication()->allowCache($allow);
	}

	/**
	 * Set a header.
	 *
	 * If $replace is true, replaces any headers already defined with that $name.
	 *
	 * @param   string   $name     The name of the header to set.
	 * @param   string   $value    The value of the header to set.
	 * @param   boolean  $replace  True to replace any existing headers by name.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use JApplicationWeb::setHeader() instead
	 */
	public static function setHeader($name, $value, $replace = false)
	{
		JFactory::getApplication()->setHeader($name, $value, $replace);
	}

	/**
	 * Return array of headers.
	 *
	 * @return  array
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use JApplicationWeb::getHeaders() instead
	 */
	public static function getHeaders()
	{
		return JFactory::getApplication()->getHeaders();
	}

	/**
	 * Clear headers.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use JApplicationWeb::clearHeaders() instead
	 */
	public static function clearHeaders()
	{
		JFactory::getApplication()->clearHeaders();
	}

	/**
	 * Send all headers.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use JApplicationWeb::sendHeaders() instead
	 */
	public static function sendHeaders()
	{
		JFactory::getApplication()->sendHeaders();
	}

	/**
	 * Set body content.
	 *
	 * If body content already defined, this will replace it.
	 *
	 * @param   string  $content  The content to set to the response body.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use JApplicationWeb::setBody() instead
	 */
	public static function setBody($content)
	{
		JFactory::getApplication()->setBody($content);
	}

	/**
	 * Prepend content to the body content
	 *
	 * @param   string  $content  The content to prepend to the response body.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use JApplicationWeb::prependBody() instead
	 */
	public static function prependBody($content)
	{
		JFactory::getApplication()->prependBody($content);
	}

	/**
	 * Append content to the body content
	 *
	 * @param   string  $content  The content to append to the response body.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use JApplicationWeb::appendBody() instead
	 */
	public static function appendBody($content)
	{
		JFactory::getApplication()->appendBody($content);
	}

	/**
	 * Return the body content
	 *
	 * @param   boolean  $toArray  Whether or not to return the body content as an array of strings or as a single string; defaults to false.
	 *
	 * @return  string  array
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use JApplicationWeb::getBody() instead
	 */
	public static function getBody($toArray = false)
	{
		return JFactory::getApplication()->getBody($toArray);
	}

	/**
	 * Sends all headers prior to returning the string
	 *
	 * @param   boolean  $compress  If true, compress the data
	 *
	 * @return  string
	 *
	 * @since   1.5
	 * @deprecated  3.2  Use JApplicationCms::toString() instead
	 */
	public static function toString($compress = false)
	{
		return JFactory::getApplication()->toString($compress);
	}

	/**
	 * Compress the data
	 *
	 * Checks the accept encoding of the browser and compresses the data before
	 * sending it to the client.
	 *
	 * @param   string  $data  Content to compress for output.
	 *
	 * @return  string  compressed data
	 *
	 * @note    Replaces _compress method from 1.5
	 * @since   1.7
	 * @deprecated  3.2  Use JApplicationWeb::compress() instead
	 */
	protected static function compress($data)
	{
		$encoding = self::clientEncoding();

		if (!$encoding)
		{
			return $data;
		}

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression'))
		{
			return $data;
		}

		if (headers_sent())
		{
			return $data;
		}

		if (connection_status() !== 0)
		{
			return $data;
		}

		// Ideal level
		$level = 4;

		/*
		$size    = strlen($data);
		$crc     = crc32($data);
		$gzdata  = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
		$gzdata .= gzcompress($data, $level);
		$gzdata  = substr($gzdata, 0, strlen($gzdata) - 4);
		$gzdata .= pack("V",$crc) . pack("V", $size);
		*/

		$gzdata = gzencode($data, $level);

		self::setHeader('Content-Encoding', $encoding);

		// Header will be removed at 4.0
		if (defined('JVERSION') && JFactory::getConfig()->get('MetaVersion', 0))
		{
			self::setHeader('X-Content-Encoded-By', 'Joomla! ' . JVERSION);
		}

		return $gzdata;
	}

	/**
	 * Check, whether client supports compressed data
	 *
	 * @return  boolean
	 *
	 * @since   1.7
	 * @note    Replaces _clientEncoding method from 1.5
	 * @deprecated  3.2  Use JApplicationWebClient instead
	 */
	protected static function clientEncoding()
	{
		if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']))
		{
			return false;
		}

		$encoding = false;

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
		{
			$encoding = 'gzip';
		}

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip'))
		{
			$encoding = 'x-gzip';
		}

		return $encoding;
	}
}
