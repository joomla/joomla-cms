<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTTP
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTTP transport class interface.
 *
 * @package     Joomla.Platform
 * @subpackage  HTTP
 * @since       11.3
 */
interface JHttpTransport
{
	/**
	 * Constructor.
	 *
	 * @param   JRegistry  $options  Client options object.
	 *
	 * @since   11.3
	 */
	public function __construct(JRegistry $options);

	/**
	 * Send a request to the server and return a JHttpResponse object with the response.
	 *
	 * @param   string   $method     The HTTP method for sending the request.
	 * @param   JUri     $uri        The URI to the resource to request.
	 * @param   mixed    $data       Either an associative array or a string to be sent with the request.
	 * @param   array    $headers    An array of request headers to send with the request.
	 * @param   integer  $timeout    Read timeout in seconds.
	 * @param   string   $userAgent  The optional user agent string to send with the request.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   11.3
	 */
	public function request($method, JUri $uri, $data = null, array $headers = null, $timeout = null, $userAgent = null);

	/**
	 * Method to check if HTTP transport is available for use
	 *
	 * @return  boolean  True if available else false
	 *
	 * @since   12.1
	 */
	public static function isSupported();
}
