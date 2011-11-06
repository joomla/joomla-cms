<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTTP
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

jimport('joomla.environment.uri');

/**
 * HTTP client class.
 *
 * @package     Joomla.Platform
 * @subpackage  HTTP
 * @since       11.4
 */
class JHttp
{
	/**
	 * @var    JHttpTransport  The HTTP transport object to use in sending HTTP requests.
	 * @since  11.4
	 */
	protected $transport;

	/**
	 * Constructor.
	 *
	 * @param   JHttpTransport  $transport  The HTTP transport object.
	 *
	 * @since   11.4
	 */
	public function __construct(JHttpTransport $transport = null)
	{
		$this->transport = isset($transport) ? $transport : new JHttpTransportStream;
	}

	/**
	 * Method to send the HEAD command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   11.4
	 */
	public function options($url, array $headers = null)
	{
		return $this->transport->request('OPTIONS', new JUri($url), null, $headers);
	}

	/**
	 * Method to send the HEAD command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   11.4
	 */
	public function head($url, array $headers = null)
	{
		return $this->transport->request('HEAD', new JUri($url), null, $headers);
	}

	/**
	 * Method to send the GET command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   11.4
	 */
	public function get($url, array $headers = null)
	{
		return $this->transport->request('GET', new JUri($url), null, $headers);
	}

	/**
	 * Method to send the POST command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   mixed   $data     Either an associative array or a string to be sent with the request.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   11.4
	 */
	public function post($url, $data, array $headers = null)
	{
		return $this->transport->request('POST', new JUri($url), $data, $headers);
	}

	/**
	 * Method to send the PUT command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   mixed   $data     Either an associative array or a string to be sent with the request.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   11.4
	 */
	public function put($url, $data, array $headers = null)
	{
		return $this->transport->request('PUT', new JUri($url), $data, $headers);
	}

	/**
	 * Method to send the DELETE command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   11.4
	 */
	public function delete($url, array $headers = null)
	{
		return $this->transport->request('DELETE', new JUri($url), null, $headers);
	}

	/**
	 * Method to send the TRACE command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   11.4
	 */
	public function trace($url, array $headers = null)
	{
		return $this->transport->request('TRACE', new JUri($url), null, $headers);
	}
}
