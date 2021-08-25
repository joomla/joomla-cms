<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Http;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Http\TransportInterface;
use Joomla\CMS\Uri\Uri;

/**
 * HTTP factory class.
 *
 * @since  3.0.0
 */
class HttpFactory
{
	/**
	 * method to receive Http instance.
	 *
	 * @param   Registry  $options   Client options object.
	 * @param   mixed     $adapters  Adapter (string) or queue of adapters (array) to use for communication.
	 *
	 * @return  Http      Joomla Http class
	 *
	 * @throws  \RuntimeException
	 *
	 * @since   3.0.0
	 */
	public static function getHttp(Registry $options = null, $adapters = null)
	{
		if (empty($options))
		{
			$options = new Registry;
		}

		if (!$driver = self::getAvailableDriver($options, $adapters))
		{
			throw new \RuntimeException('No transport driver available.');
		}

		return new Http($options, $driver);
	}

	/**
	 * Finds an available http transport object for communication
	 *
	 * @param   Registry  $options  Option for creating http transport object
	 * @param   mixed     $default  Adapter (string) or queue of adapters (array) to use
	 *
	 * @return  TransportInterface Interface sub-class
	 *
	 * @since   3.0.0
	 */
	public static function getAvailableDriver(Registry $options, $default = null)
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

		// Check if there is at least one available http transport adapter
		if (!count($availableAdapters))
		{
			return false;
		}

		foreach ($availableAdapters as $adapter)
		{
			$class = __NAMESPACE__ . '\\Transport\\' . ucfirst($adapter) . 'Transport';

			if (!class_exists($class))
			{
				$class = 'JHttpTransport' . ucfirst($adapter);
			}

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
	 * @since   3.0.0
	 */
	public static function getHttpTransports()
	{
		$names = array();
		$iterator = new \DirectoryIterator(__DIR__ . '/Transport');

		/** @type  $file  \DirectoryIterator */
		foreach ($iterator as $file)
		{
			$fileName = $file->getFilename();

			// Only load for php files.
			if ($file->isFile() && $file->getExtension() == 'php')
			{
				$names[] = substr($fileName, 0, strrpos($fileName, 'Transport.'));
			}
		}

		// Keep alphabetical order across all environments
		sort($names);

		// If curl is available set it to the first position
		if ($key = array_search('Curl', $names))
		{
			unset($names[$key]);
			array_unshift($names, 'Curl');
		}

		return $names;
	}
}
