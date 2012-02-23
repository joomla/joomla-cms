<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTTP
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

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
	 * @param   mixed      $adapters  Adapter (string) or queue of adapters (array) to use for communication
	 *
	 * @return  JHttp      Joomla Http class
	 * 
	 * @since   12.1
	 */
	public static function getHttp($options = null, $adapters = null)
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
	 * @param   JRegistery  $options  Option for creating http transport object
	 * @param   mixed       $default  Adapter (string) or queue of adapters (array) to use
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
