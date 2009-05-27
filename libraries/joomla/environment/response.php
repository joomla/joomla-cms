<?php
/**
 * @version		$Id: response.php 10707 2008-08-21 09:52:47Z eddieajau $
 * @package		Joomla.Framework
 * @subpackage	Environment
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

/**
 * Create the response global object
 */
$GLOBALS['_JRESPONSE'] = new stdClass();
$GLOBALS['_JRESPONSE']->cachable = false;
$GLOBALS['_JRESPONSE']->headers  = array();
$GLOBALS['_JRESPONSE']->body	 = array();

 /**
 * JResponse Class
 *
 * This class serves to provide the Joomla Framework with a common interface to access
 * response variables.  This includes header and body.
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	Environment
 * @since		1.5
 */
class JResponse
{
	/**
	 * Set/get cachable state for the response
	 *
	 * If $allow is set, sets the cachable state of the response.  Always returns current state
	 *
	 * @static
	 * @param	boolean	$allow
	 * @return	boolean 	True of browser caching should be allowed
	 * @since	1.5
	 */
	function allowCache($allow = null)
	{
		if (!is_null($allow)) {
			$GLOBALS['_JRESPONSE']->cachable = (bool) $allow;
		}
		return $GLOBALS['_JRESPONSE']->cachable;
	}

	/**
	 * Set a header
	 *
	 * If $replace is true, replaces any headers already defined with that
	 * $name.
	 *
	 * @access public
	 * @param string 	$name
	 * @param string 	$value
	 * @param boolean 	$replace
	 */
	function setHeader($name, $value, $replace = false)
	{
		$name	= (string) $name;
		$value	= (string) $value;

		if ($replace)
		{
			foreach ($GLOBALS['_JRESPONSE']->headers as $key => $header) {
				if ($name == $header['name']) {
					unset($GLOBALS['_JRESPONSE']->headers[$key]);
				}
			}
		}

		$GLOBALS['_JRESPONSE']->headers[] = array(
			'name'	=> $name,
			'value'	=> $value
		);
	}

	/**
	 * Return array of headers;
	 *
	 * @access public
	 * @return array
	 */
	function getHeaders() {
		return  $GLOBALS['_JRESPONSE']->headers;
	}

	/**
	 * Clear headers
	 *
	 * @access public
	 */
	function clearHeaders() {
		$GLOBALS['_JRESPONSE']->headers = array();
	}

	/**
	 * Send all headers
	 *
	 * @access public
	 * @return void
	 */
	function sendHeaders()
	{
		if (!headers_sent())
		{
			foreach ($GLOBALS['_JRESPONSE']->headers as $header)
			{
				if ('status' == strtolower($header['name']))
				{
					// 'status' headers indicate an HTTP status, and need to be handled slightly differently
					header(ucfirst(strtolower($header['name'])) . ': ' . $header['value'], null, (int) $header['value']);
				} else {
					header($header['name'] . ': ' . $header['value']);
				}
			}
		}
	}

	/**
	 * Set body content
	 *
	 * If body content already defined, this will replace it.
	 *
	 * @access public
	 * @param string $content
	 */
	function setBody($content) {
		$GLOBALS['_JRESPONSE']->body = array((string) $content);
	}

	 /**
	 * Prepend content to the body content
	 *
	 * @access public
	 * @param string $content
	 */
	function prependBody($content) {
		array_unshift($GLOBALS['_JRESPONSE']->body, (string) $content);
	}

	/**
	 * Append content to the body content
	 *
	 * @access public
	 * @param string $content
	 */
	function appendBody($content) {
		array_push($GLOBALS['_JRESPONSE']->body, (string) $content);
	}

	/**
	 * Return the body content
	 *
	 * @access public
	 * @param boolean $toArray Whether or not to return the body content as an
	 * array of strings or as a single string; defaults to false
	 * @return string|array
	 */
	function getBody($toArray = false)
	{
		if ($toArray) {
			return $GLOBALS['_JRESPONSE']->body;
		}

		ob_start();
		foreach ($GLOBALS['_JRESPONSE']->body as $content) {
			echo $content;
		}
		return ob_get_clean();
	}

	/**
	 * Sends all headers prior to returning the string
	 *
	 * @access public
	 * @param boolean 	$compress	If true, compress the data
	 * @return string
	 */
	function toString($compress = false)
	{
		$data = JResponse::getBody();

		// Don't compress something if the server is going todo it anyway. Waste of time.
		if ($compress && !ini_get('zlib.output_compression') && ini_get('output_handler')!='ob_gzhandler') {
			$data = JResponse::_compress($data);
		}

		if (JResponse::allowCache() === false)
		{
			JResponse::setHeader('Expires', 'Mon, 1 Jan 2001 00:00:00 GMT', true); 				// Expires in the past
			JResponse::setHeader('Last-Modified', gmdate("D, d M Y H:i:s") . ' GMT', true); 		// Always modified
			JResponse::setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', false);
			JResponse::setHeader('Pragma', 'no-cache'); 											// HTTP 1.0
		}

		JResponse::sendHeaders();
		return $data;
	}

	/**
	* Compress the data
	*
	* Checks the accept encoding of the browser and compresses the data before
	* sending it to the client.
	*
	* @access	public
	* @param	string		data
	* @return	string		compressed data
	*/
	function _compress($data)
	{
		$encoding = JResponse::_clientEncoding();

		if (!$encoding)
			return $data;

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
			return $data;
		}

		if (headers_sent())
			return $data;

		if (connection_status() !== 0)
			return $data;


		$level = 4; //ideal level

		/*
		$size		= strlen($data);
		$crc		= crc32($data);

		$gzdata		= "\x1f\x8b\x08\x00\x00\x00\x00\x00";
		$gzdata		.= gzcompress($data, $level);

		$gzdata 	= substr($gzdata, 0, strlen($gzdata) - 4);
		$gzdata 	.= pack("V",$crc) . pack("V", $size);
		*/

		$gzdata = gzencode($data, $level);

		JResponse::setHeader('Content-Encoding', $encoding);
		JResponse::setHeader('X-Content-Encoded-By', 'Joomla! 1.5');

		return $gzdata;
	}

	 /**
	* check, whether client supports compressed data
	*
	* @access	private
	* @return	boolean
	*/
	function _clientEncoding()
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
