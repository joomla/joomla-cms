<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTTP
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTTP factory class.
 *
 * @package     Joomla.Platform
 * @subpackage  HTTP
 * @since       12.1
 */
class JHttpFactory
{
	/**
	 * method to recieve Http instance.
	 *
	 * @param   JRegistry  $options   Client options object.
	 * @param   mixed      $adapters  Adapter (string) or queue of adapters (array) to use for communication.
	 *
	 * @return  JHttp      Joomla Http class
	 *
	 * @since   12.1
	 */
	public static function getHttp(JRegistry $options = null, $adapters = null)
	{
		if (empty($options))
		{
			$options = new JRegistry;
		}
		return new JHttp($options, self::getAvailableDriver($options, $adapters));
	}

	/**
	 * Finds an available http transport object for communication
	 *
	 * @param   JRegistry  $options  Option for creating http transport object
	 * @param   mixed      $default  Adapter (string) or queue of adapters (array) to use
	 *
	 * @return  JHttpTransport Interface sub-class
	 *
	 * @since   12.1
	 */
	public static function getAvailableDriver(JRegistry $options, $default = null)
	{
		if (is_null($default))
		{
			$availableAdapters = self::getHttpTransports();
		}
		else
		{
			settype($default, 'array');
			$availableAdapters = $default;
		}
		// Check if there is available http transport adapters
		if (!count($availableAdapters))
		{
			return false;
		}
		foreach ($availableAdapters as $adapter)
		{
			$class = 'JHttpTransport' . ucfirst($adapter);

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
	 * @return  array  An array of available transport handlers
	 *
	 * @since   12.1
	 */
	public static function getHttpTransports()
	{
		$names = array();
		$iterator = new DirectoryIterator(__DIR__ . '/transport');
		foreach ($iterator as $file)
		{
			$fileName = $file->getFilename();

			// Only load for php files.
			// Note: DirectoryIterator::getExtension only available PHP >= 5.3.6
			if ($file->isFile() && substr($fileName, strrpos($fileName, '.') + 1) == 'php')
			{
				$names[] = substr($fileName, 0, strrpos($fileName, '.'));
			}
		}

		return $names;
	}
}
