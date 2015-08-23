<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Redis
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_PLATFORM') or die;
/**
 * Joomla Platform Redis Interface
 *
 * @since  3.5
*/
interface JRedisInterface
{
	/**
	 * Test to see if the connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   3.5
	 */
	public static function isSupported();

}

/**
 * Joomla Platform Redis Driver Class
 *
 * @since  3.5
 *
 */
abstract class JRedis implements JRedisInterface
{

	protected static $instances = array();
	protected static $error     = null;
	/**
	 * Instances are unique to the given options and new objects are only created when a unique options array is
	 * passed into the method.  This ensures that we don't end up with unnecessary datastore connection resources.
	 *
	 * @var    resource  The Redis connection resource.
	 * @since  3.5
	 */
	protected $connection = null;

	public static function getInstance($settings)
	{

		$conf = JFactory::getConfig();
		$options = array(
			'host'   => $settings['host'],
			'port'   => $settings['port'],
			'auth'   => $settings['auth'],
			'db'     => $settings['db'],
			'driver' => 'redis',
		);

		$is_supported = self::isSupported();
		if (!$is_supported)
		{
			throw new RuntimeException('Redis not supported');
		}
		$signature = md5(serialize($options));

		if (empty(self::$instances[$signature]))
		{
			try
			{
				$instance = new Redis;
				$connected = @$instance->pconnect($options['host'], $options['port']);
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException('Redis unable to connect');
			}

			if (!$connected)
			{
				throw new RuntimeException('Redis unable to connect at '.$options['host'] . ':' . $options['port'], 404);
			}

			if ($options['auth'] != null)
			{
				try
				{
					$auth = $instance->auth($options['auth']);
				}
				catch (RuntimeException $e)
				{
					throw new RuntimeException('Redis auth failed');
				}
				if (!$auth)
				{
					throw new RuntimeException('Redis unable to verify password at ' . $options['host'] . ':' . $options['port']);
				}
			}

			try
			{
				$db = $instance->select($options['db']);
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException('Redis unable to select db');
			}

			if (!$db)
			{
				throw new RuntimeException('Redis unable to select db at ' . $options['host'] . ':' . $options['port'] . ' database:' . $options['db']);
			}
			try
			{
				$pong = $instance->ping();
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException('Redis unable to ping');
			}

			if ($pong != '+PONG')
			{
				throw new RuntimeException('Redis unable to ping at '.$options['host'] . ':' . $options['port']);
			}
			// Set the new connector to the global instances based on signature.
			self::$instances[$signature] = $instance;
		}

		return self::$instances[$signature];
	}
	/**
	 * Determines if Redis is supported.
	 *
	 * @return  boolean  True if supported.
	 *
	 * @since   3.5
	 */
	public static function isSupported()
	{
		return (extension_loaded('redis') && class_exists('Redis'));
	}

}
