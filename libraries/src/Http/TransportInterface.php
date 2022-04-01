<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Http;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;

/**
 * HTTP transport class interface.
 *
 * @since  1.7.3
 */
interface TransportInterface
{
	/**
	 * Constructor.
	 *
	 * @param   Registry  $options  Client options object.
	 *
	 * @since   1.7.3
	 */
	public function __construct(Registry $options);

	/**
	 * Send a request to the server and return a HttpResponse object with the response.
	 *
	 * @param   string   $method     The HTTP method for sending the request.
	 * @param   Uri      $uri        The URI to the resource to request.
	 * @param   mixed    $data       Either an associative array or a string to be sent with the request.
	 * @param   array    $headers    An array of request headers to send with the request.
	 * @param   integer  $timeout    Read timeout in seconds.
	 * @param   string   $userAgent  The optional user agent string to send with the request.
	 *
	 * @return  Response
	 *
	 * @since   1.7.3
	 */
	public function request($method, Uri $uri, $data = null, array $headers = null, $timeout = null, $userAgent = null);

	/**
	 * Method to check if HTTP transport is available for use
	 *
	 * @return  boolean  True if available else false
	 *
	 * @since   3.0.0
	 */
	public static function isSupported();
}
