<?php
/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

/**
 * Abstract transport class.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class AbstractTransport implements TransportInterface
{
	/**
	 * The client options.
	 *
	 * @var    array|\ArrayAccess
	 * @since  __DEPLOY_VERSION__
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * @param   array|\ArrayAccess  $options  Client options array.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function __construct($options = [])
	{
		if (!static::isSupported())
		{
			throw new \RuntimeException(sprintf('The %s transport is not supported in this environment.', get_class($this)));
		}

		if (!is_array($options) && !($options instanceof \ArrayAccess))
		{
			throw new \InvalidArgumentException(
				'The options param must be an array or implement the ArrayAccess interface.'
			);
		}

		$this->options = $options;
	}

	/**
	 * Get an option from the HTTP transport.
	 *
	 * @param   string  $key      The name of the option to get.
	 * @param   mixed   $default  The default value if the option is not set.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOption($key, $default = null)
	{
		return isset($this->options[$key]) ? $this->options[$key] : $default;
	}

	/**
	 * Processes the headers from a transport's response data.
	 *
	 * @param   array  $headers  The headers to process.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function processHeaders(array $headers)
	{
		$verifiedHeaders = [];

		// Add the response headers to the response object.
		foreach ($headers as $header)
		{
			$pos     = strpos($header, ':');
			$keyName = trim(substr($header, 0, $pos));

			if (!isset($verifiedHeaders[$keyName]))
			{
				$verifiedHeaders[$keyName] = [];
			}

			$verifiedHeaders[$keyName][] = trim(substr($header, ($pos + 1)));
		}

		return $verifiedHeaders;
	}
}
