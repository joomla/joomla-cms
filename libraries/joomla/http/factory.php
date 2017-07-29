<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTTP
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Http\TransportInterface;

/**
 * HTTP factory class.
 *
 * @since  12.1
 */
class JHttpFactory
{
	/**
	 * Method to create a JHttp instance.
	 *
	 * @param   array|ArrayAccess  $options   Client options array.
	 * @param   array|string       $adapters  Adapter (string) or queue of adapters (array) to use for communication.
	 *
	 * @return  JHttp
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public static function getHttp($options = [], $adapters = null)
	{
		if (!is_array($options) && !($options instanceof ArrayAccess))
		{
			throw new \InvalidArgumentException(
				'The options param must be an array or implement the ArrayAccess interface.'
			);
		}

		// Set default userAgent if nothing else is set
		if (!isset($options['userAgent']))
		{
			$version = new JVersion;
			$options['userAgent'] = $version->getUserAgent('Joomla', true, false);
		}

		if (!$driver = static::getAvailableDriver($options, $adapters))
		{
			throw new RuntimeException('No transport driver available.');
		}

		return new JHttp($options, $driver);
	}

	/**
	 * Finds an available http transport object for communication
	 *
	 * @param   array|ArrayAccess  $options  Options for creating TransportInterface object
	 * @param   array|string       $default  Adapter (string) or queue of adapters (array) to use
	 *
	 * @return  TransportInterface|boolean  Interface sub-class or boolean false if no adapters are available
	 *
	 * @since   12.1
	 */
	public static function getAvailableDriver($options = [], $default = null)
	{
		if (is_null($default))
		{
			$availableAdapters = static::getHttpTransports();
		}
		else
		{
			settype($default, 'array');
			$availableAdapters = $default;
		}

		// Check if there is at least one available http transport adapter
		if (!count($availableAdapters))
		{
			return false;
		}

		foreach ($availableAdapters as $adapter)
		{
			/** @var $class TransportInterface */
			$class = 'JHttpTransport' . ucfirst($adapter);

			if (class_exists($class) && $class::isSupported())
			{
				return new $class($options);
			}
		}

		return false;
	}

	/**
	 * Get the http transport handlers
	 *
	 * @return  array  An array of available transport handlers
	 *
	 * @since   12.1
	 */
	public static function getHttpTransports()
	{
		$names = array();
		$iterator = new DirectoryIterator(__DIR__ . '/transport');

		/* @type  $file  DirectoryIterator */
		foreach ($iterator as $file)
		{
			$fileName = $file->getFilename();

			// Only load for php files.
			if ($file->isFile() && $file->getExtension() == 'php')
			{
				$names[] = substr($fileName, 0, strrpos($fileName, '.'));
			}
		}

		// Keep alphabetical order across all environments
		sort($names);

		// If curl is available set it to the first position
		if ($key = array_search('curl', $names))
		{
			unset($names[$key]);
			array_unshift($names, 'curl');
		}

		return $names;
	}
}
