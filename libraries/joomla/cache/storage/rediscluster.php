<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_PLATFORM') or die;
/**
 * Redis Cluster cache storage handler for PECL
 *
 * @since  3.4
 */
class JCacheStorageRediscluster extends JCacheStorageRedis
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   3.4
	 */
	public function __construct($options = array())
	{
		JCacheStorage::__construct($options);
		if (static::$_redis === null)
		{
			$this->getConnection();
		}
	}
	/**
	 * Create the Redis Cluster connection
	 *
	 * @return  Redis|boolean  Redis connection object on success, boolean on failure
	 *
	 * @since   3.4
	 * @note    As of 4.0 this method will throw a JCacheExceptionConnecting object on connection failure
	 */
	protected function getConnection()
	{
		if (static::isSupported() == false)
		{
			return false;
		}
		$app = JFactory::getApplication();
		$string = $app->get('redis_server_cluster_host_port');
		$string = str_replace(' ', '', $string);
		$arr = explode(",",$string);
		if (empty($arr)) {
			return false;
		}
		static::$_redis = new RedisCluster(NULL, $arr);
		// The default option, only send commands to master nodes
		static::$_redis->setOption(RedisCluster::OPT_SLAVE_FAILOVER, RedisCluster::FAILOVER_NONE);
		// In the event we can't reach a master, and it has slaves, failover for read commands
		static::$_redis->setOption(RedisCluster::OPT_SLAVE_FAILOVER, RedisCluster::FAILOVER_ERROR);
		// Always distribute readonly commands between masters and slaves, at random
		static::$_redis->setOption(RedisCluster::OPT_SLAVE_FAILOVER, RedisCluster::FAILOVER_DISTRIBUTE);
		try
		{
			static::$_redis->ping();
		}
		catch (RedisClusterException $e)
		{
			static::$_redis = null;
			if ($app->isClient('administrator'))
			{
				JError::raiseWarning(500, 'Redis Cluster ping failed');
			}
			return false;
		}
		return static::$_redis;
	}
	/**
	 * Test to see if the storage handler is available.
	 *
	 * @return  boolean
	 *
	 * @since   3.4
	 */
	public static function isSupported()
	{
		return class_exists('RedisCluster');
	}
	/**
	 * Test to see if the Redis connection is available.
	 *
	 * @return  boolean
	 *
	 * @since   3.4
	 */
	public static function isConnected()
	{
		return static::$_redis instanceof RedisCluster;
	}
}