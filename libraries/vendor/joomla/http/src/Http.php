<?php
/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

use Joomla\Uri\Uri;
use Joomla\Uri\UriInterface;
use Psr\Http\Message\RequestInterface;

/**
 * HTTP client class.
 *
 * @since  1.0
 */
class Http
{
	/**
	 * Options for the HTTP client.
	 *
	 * @var    array|\ArrayAccess
	 * @since  1.0
	 */
	protected $options;

	/**
	 * The HTTP transport object to use in sending HTTP requests.
	 *
	 * @var    TransportInterface
	 * @since  1.0
	 */
	protected $transport;

	/**
	 * Constructor.
	 *
	 * @param   array|\ArrayAccess  $options    Client options array. If the registry contains any headers.* elements,
	 *                                          these will be added to the request headers.
	 * @param   TransportInterface  $transport  The HTTP transport object.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function __construct($options = [], TransportInterface $transport = null)
	{
		if (!is_array($options) && !($options instanceof \ArrayAccess))
		{
			throw new \InvalidArgumentException(
				'The options param must be an array or implement the ArrayAccess interface.'
			);
		}

		$this->options = $options;

		if (!$transport)
		{
			$transport = (new HttpFactory)->getAvailableDriver($this->options);

			// Ensure the transport is a TransportInterface instance or bail out
			if (!($transport instanceof TransportInterface))
			{
				throw new \InvalidArgumentException('A valid TransportInterface object was not set.');
			}
		}

		$this->transport = $transport;
	}

	/**
	 * Get an option from the HTTP client.
	 *
	 * @param   string  $key      The name of the option to get.
	 * @param   mixed   $default  The default value if the option is not set.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   1.0
	 */
	public function getOption($key, $default = null)
	{
		return $this->options[$key] ?? $default;
	}

	/**
	 * Set an option for the HTTP client.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setOption($key, $value)
	{
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * Method to send the OPTIONS command to the server.
	 *
	 * @param   string|UriInterface  $url      The URI to the resource to request.
	 * @param   array                $headers  An array of request headers to send with the request.
	 * @param   integer              $timeout  Read timeout in seconds.
	 *
	 * @return  Response
	 *
	 * @since   1.0
	 */
	public function options($url, array $headers = [], $timeout = null)
	{
		return $this->makeTransportRequest('OPTIONS', $url, null, $headers, $timeout);
	}

	/**
	 * Method to send the HEAD command to the server.
	 *
	 * @param   string|UriInterface  $url      The URI to the resource to request.
	 * @param   array                $headers  An array of request headers to send with the request.
	 * @param   integer              $timeout  Read timeout in seconds.
	 *
	 * @return  Response
	 *
	 * @since   1.0
	 */
	public function head($url, array $headers = [], $timeout = null)
	{
		return $this->makeTransportRequest('HEAD', $url, null, $headers, $timeout);
	}

	/**
	 * Method to send the GET command to the server.
	 *
	 * @param   string|UriInterface  $url      The URI to the resource to request.
	 * @param   array                $headers  An array of request headers to send with the request.
	 * @param   integer              $timeout  Read timeout in seconds.
	 *
	 * @return  Response
	 *
	 * @since   1.0
	 */
	public function get($url, array $headers = [], $timeout = null)
	{
		return $this->makeTransportRequest('GET', $url, null, $headers, $timeout);
	}

	/**
	 * Method to send the POST command to the server.
	 *
	 * @param   string|UriInterface  $url      The URI to the resource to request.
	 * @param   mixed                $data     Either an associative array or a string to be sent with the request.
	 * @param   array                $headers  An array of request headers to send with the request.
	 * @param   integer              $timeout  Read timeout in seconds.
	 *
	 * @return  Response
	 *
	 * @since   1.0
	 */
	public function post($url, $data, array $headers = [], $timeout = null)
	{
		return $this->makeTransportRequest('POST', $url, $data, $headers, $timeout);
	}

	/**
	 * Method to send the PUT command to the server.
	 *
	 * @param   string|UriInterface  $url      The URI to the resource to request.
	 * @param   mixed                $data     Either an associative array or a string to be sent with the request.
	 * @param   array                $headers  An array of request headers to send with the request.
	 * @param   integer              $timeout  Read timeout in seconds.
	 *
	 * @return  Response
	 *
	 * @since   1.0
	 */
	public function put($url, $data, array $headers = [], $timeout = null)
	{
		return $this->makeTransportRequest('PUT', $url, $data, $headers, $timeout);
	}

	/**
	 * Method to send the DELETE command to the server.
	 *
	 * @param   string|UriInterface  $url      The URI to the resource to request.
	 * @param   array                $headers  An array of request headers to send with the request.
	 * @param   integer              $timeout  Read timeout in seconds.
	 * @param   mixed                $data     Either an associative array or a string to be sent with the request.
	 *
	 * @return  Response
	 *
	 * @since   1.0
	 */
	public function delete($url, array $headers = [], $timeout = null, $data = null)
	{
		return $this->makeTransportRequest('DELETE', $url, $data, $headers, $timeout);
	}

	/**
	 * Method to send the TRACE command to the server.
	 *
	 * @param   string|UriInterface  $url      The URI to the resource to request.
	 * @param   array                $headers  An array of request headers to send with the request.
	 * @param   integer              $timeout  Read timeout in seconds.
	 *
	 * @return  Response
	 *
	 * @since   1.0
	 */
	public function trace($url, array $headers = [], $timeout = null)
	{
		return $this->makeTransportRequest('TRACE', $url, null, $headers, $timeout);
	}

	/**
	 * Method to send the PATCH command to the server.
	 *
	 * @param   string|UriInterface  $url      The URI to the resource to request.
	 * @param   mixed                $data     Either an associative array or a string to be sent with the request.
	 * @param   array                $headers  An array of request headers to send with the request.
	 * @param   integer              $timeout  Read timeout in seconds.
	 *
	 * @return  Response
	 *
	 * @since   1.0
	 */
	public function patch($url, $data, array $headers = [], $timeout = null)
	{
		return $this->makeTransportRequest('PATCH', $url, $data, $headers, $timeout);
	}

	/**
	 * Send a request to a remote server based on a PSR-7 RequestInterface object.
	 *
	 * @param   RequestInterface  $request  The PSR-7 request object.
	 *
	 * @return  Response
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function sendRequest(RequestInterface $request)
	{
		$data = $request->getBody()->getContents();

		return $this->makeTransportRequest(
			$request->getMethod(),
			new Uri((string) $request->getUri()),
			empty($data) ? null : $data,
			$request->getHeaders()
		);
	}

	/**
	 * Send a request to the server and return a Response object with the response.
	 *
	 * @param   string               $method   The HTTP method for sending the request.
	 * @param   string|UriInterface  $url      The URI to the resource to request.
	 * @param   mixed                $data     Either an associative array or a string to be sent with the request.
	 * @param   array                $headers  An array of request headers to send with the request.
	 * @param   integer              $timeout  Read timeout in seconds.
	 *
	 * @return  Response
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	protected function makeTransportRequest($method, $url, $data = null, array $headers = [], $timeout = null)
	{
		// Look for headers set in the options.
		if (isset($this->options['headers']))
		{
			$temp = (array) $this->options['headers'];

			foreach ($temp as $key => $val)
			{
				if (!isset($headers[$key]))
				{
					$headers[$key] = $val;
				}
			}
		}

		// Look for timeout set in the options.
		if ($timeout === null && isset($this->options['timeout']))
		{
			$timeout = $this->options['timeout'];
		}

		$userAgent = isset($this->options['userAgent']) ? $this->options['userAgent'] : null;

		// Convert to a Uri object if we were given a string
		if (is_string($url))
		{
			$url = new Uri($url);
		}
		elseif (!($url instanceof UriInterface))
		{
			throw new \InvalidArgumentException(sprintf('A string or UriInterface object must be provided, a "%s" was provided.', gettype($url)));
		}

		return $this->transport->request($method, $url, $data, $headers, $timeout, $userAgent);
	}
}
