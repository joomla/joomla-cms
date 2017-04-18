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
 * Memcached cache storage handler using AWS ElastiCache PHP Client
 *
 * @link   https://docs.aws.amazon.com/AmazonElastiCache/latest/UserGuide/AutoDiscovery.html
 * @link   https://github.com/awslabs/aws-elasticache-cluster-client-memcached-for-php
 * @since  __DEPLOY_VERSION__
 */
class JCacheStorageMemcachedaws extends JCacheStorageMemcached
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($options = array())
	{
		JCacheStorage::__construct($options);

		if (parent::$_db === null)
		{
			$this->getConnection();
		}
	}

	/**
	 * Create the MemcachedAWS connection
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  RuntimeException
	 */
	protected function getConnection()
	{
		if (!static::isSupported())
		{
			throw new RuntimeException('Memcached AWS Extension is not available');
		}

		if(JFactory::getConfig()->get('memcached_autodiscovery', 0))
		{
			$this->getDynamicClientConnection();
		}
		else
		{
			$this->getStaticClientConnection();
		}
	}


	/**
	 * The following will initialize a Memcached client to utilize the Auto Discovery feature.
	 *
	 * By configuring the client with the Dynamic client mode with single endpoint, the
	 * client will periodically use the configuration endpoint to retrieve the current cache
	 * cluster configuration. This allows scaling the cache cluster up or down in number of nodes
	 * without requiring any changes to the PHP application.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getDynamicClientConnection()
	{
		$config = JFactory::getConfig();

		$host = $config->get('memcached_server_host', 'localhost');
		$port = $config->get('memcached_server_port', 11211);

		if ($config->get('memcached_persist', true))
		{
			parent::$_db = new Memcached($this->_hash);
			$servers     = parent::$_db->getServerByKey($host);

			if (!$servers)
			{
				parent::$_db->resetServerList();
				$servers = array();
			}

			if (!$servers)
			{
				parent::$_db->addServer($host, $port);
			}
		}
		else
		{
			parent::$_db = new Memcached;
			parent::$_db->addServer($host, $port);
		}

		parent::$_db->setOption(Memcached::OPT_COMPRESSION, $config->get('memcached_compress', false) ? Memcached::OPT_COMPRESSION : 0);
		parent::$_db->setOption(Memcached::OPT_CLIENT_MODE, Memcached::DYNAMIC_CLIENT_MODE);
		parent::$_db->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);

		$servers = parent::$_db->getServerByKey($host);

		$stats  = parent::$_db->getStats();
		$result = !empty($stats[$servers['host'].':'.$port]) && $stats[$servers['host'].':'.$port]['pid'] > 0;

		$this->checkConnection($result);
	}

	/**
	 * Configuring the client with Static client mode disables the usage of Auto Discovery
	 * and the client operates as it did before the introduction of Auto Discovery.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getStaticClientConnection()
	{
		$config = JFactory::getConfig();

		$host = $config->get('memcached_server_host', 'localhost');
		$port = $config->get('memcached_server_port', 11211);

		if ($config->get('memcached_persist', true))
		{
			parent::$_db = new Memcached($this->_hash);
			$servers = parent::$_db->getServerList();

			if ($servers && ($servers[0]['host'] != $host || $servers[0]['port'] != $port))
			{
				parent::$_db->resetServerList();
				$servers = array();
			}

			if (!$servers)
			{
				parent::$_db->addServer($host, $port);
			}
		}
		else
		{
			parent::$_db = new Memcached;
			parent::$_db->addServer($host, $port);
		}

		parent::$_db->setOption(Memcached::OPT_COMPRESSION, $config->get('memcached_compress', false) ? Memcached::OPT_COMPRESSION : 0);
		parent::$_db->setOption(Memcached::OPT_CLIENT_MODE, Memcached::STATIC_CLIENT_MODE);

		$stats  = parent::$_db->getStats();
		$result = !empty($stats["$host:$port"]) && $stats["$host:$port"]['pid'] > 0;

		$this->checkConnection($result);
	}

	/**
	 * Check connection to memcached server.
	 *
	 * @param   boolean Was connection to memcached successful.
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  JCacheExceptionConnecting
	 */
	protected function checkConnection($result)
	{
		if (!$result)
		{
			$message = parent::$_db->getResultMessage();
			// Null out the connection to inform the constructor it will need to attempt to connect if this class is instantiated again
			parent::$_db = null;

			throw new JCacheExceptionConnecting('Could not connect to memcached server. '. $message);
		}
	}

	/**
	 * Test to see if the storage handler is available.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		return parent::isSupported() && defined('Memcached::OPT_CLIENT_MODE');
	}

}
