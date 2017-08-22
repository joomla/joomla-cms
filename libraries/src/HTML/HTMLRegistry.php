<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML;

defined('JPATH_PLATFORM') or die;

/**
 * Service registry for JHtml services
 *
 * @since  __DEPLOY_VERSION__
 */
final class HTMLRegistry
{
	/**
	 * Array holding the registered services
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $serviceMap = [];

	/**
	 * Get the service for a given key
	 *
	 * @param   string  $key  The service key to look up
	 *
	 * @return  string|object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getService($key)
	{
		if (!$this->hasService($key))
		{
			throw new \InvalidArgumentException("The '$key' service key is not registered.");
		}

		return $this->serviceMap[$key];
	}

	/**
	 * Check if the registry has a service for the given key
	 *
	 * @param   string  $key  The service key to look up
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasService($key)
	{
		return isset($this->serviceMap[$key]);
	}

	/**
	 * Register a service
	 *
	 * @param   string         $key      The service key to be registered
	 * @param   string|object  $handler  The handler for the service as either a PHP class name or class object
	 * @param   boolean        $replace  Flag indicating the service key may replace an existing definition
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function register($key, $handler, $replace = false)
	{
		// If the key exists already and we aren't instructed to replace existing services, bail early
		if (isset($this->serviceMap[$key]) && !$replace)
		{
			throw new \RuntimeException("The '$key' service key is already registered.");
		}

		// If the handler is a string, it must be a class that exists
		if (is_string($handler) && !class_exists($handler))
		{
			throw new \RuntimeException("The '$handler' class for service key '$key' does not exist.");
		}

		// Otherwise the handler must be a class object
		if (!is_string($handler) && !is_object($handler))
		{
			throw new \RuntimeException(
				sprintf(
					'The handler for service key %1$s must be a PHP class name or class object, a %2$s was given.',
					$key,
					gettype($handler)
				)
			);
		}

		$this->serviceMap[$key] = $handler;
	}
}
