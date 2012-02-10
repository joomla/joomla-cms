<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTTP
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

jimport('joomla.environment.uri');

/**
 * HTTP client class.
 *
 * @package     Joomla.Platform
 * @subpackage  HTTP
 * @since       11.3
 */
class JHttp
{
	/**
	 * @var    JRegistry  Options for the HTTP client.
	 * @since  11.3
	 */
	protected $options;

	/**
	 * @var    JHttpTransport  The HTTP transport object to use in sending HTTP requests.
	 * @since  11.3
	 */
	protected $transport;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry       &$options   Client options object.
	 * @param   JHttpTransport  $transport  The HTTP transport object.
	 * @param   mixed           $adapters   Adapter (string) or queue of adapters (array) to use for communication
	 *
	 * @since   11.3
	 */
	public function __construct(JRegistry &$options = null, JHttpTransport $transport = null, $adapters = null)
	{
		$this->options   = isset($options) ? $options : new JRegistry;
		$this->transport = isset($transport) ? $transport : self::getAvailableDriver($this->options, $adapters);
	}

	/**
	 * Get an option from the HTTP client.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   11.3
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the HTTP client.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JHttp  This object for method chaining.
	 *
	 * @since   11.3
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}

	/**
	 * Method to send the OPTIONS command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   11.3
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
	 * @since   11.3
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
	 * @since   11.3
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
	 * @since   11.3
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
	 * @since   11.3
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
	 * @since   11.3
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
	 * @since   11.3
	 */
	public function trace($url, array $headers = null)
	{
		return $this->transport->request('TRACE', new JUri($url), null, $headers);
	}

	/**
	 * Finds an available http transport object for communication
	 *
	 * @param   JRegistery  $options the option for creating http transport object
	 * @param   mixed       $default adapter (string) or queue of adapters (array) to use
	 *
	 * @return  JHttpTransport Interface sub-class
	 *
	 * @since   12.1
	 */
	public static function getAvailableDriver(JRegistry $options, $default = null)
	{
		if (is_null($default))
		{
			$available_adapters = self::getHttpTransports();
		}
		else
		{
			settype($default, 'array');
			$available_adapters = $default;
		}
		// Check if there is available http transport adapters
		if (!count($available_adapters))
		{
			return false;
		}
		foreach ($available_adapters as $adapter)
		{
			$class = 'JHttpTransport' . ucfirst($adapter);
			/**
			 * on J!2.5 (PHP 5.2) the condition should be:
			 * call_user_func_array(array($class, 'isSupported'), array())
			 */
			if ($class::isSupported())
			{
				return new $class($options);
			}
		}
		return false;
	}
	
	/**
	 * Get the http transport handlers
	 *
	 * @return  array    An array of available transport handlers
	 *
	 * @since   12.1
	 * @todo make this function more generic cause the behaviour taken from cache (getStores)
	 */
	public static function getHttpTransports()
	{
		$basedir = __DIR__ . '/transport';
		$handlers = JFolder::files($basedir, '.php');

		$names = array();
		foreach ($handlers as $handler)
		{
			$names[] = substr($handler, 0, strrpos($handler, '.'));
		}

		return $names;
	}

}
