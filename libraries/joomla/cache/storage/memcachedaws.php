<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Memcached cache storage handler using AWS ElastiCache PHP Client
 *
 * @see http://docs.aws.amazon.com/AmazonElastiCache/latest/UserGuide/AutoDiscovery.html
 * @see https://github.com/awslabs/aws-elasticache-cluster-client-memcached-for-php
 * @author jdolinski Douglas-Omaha Technology Commission
 */
class JCacheStorageMemcachedaws extends JCacheStorageMemcached {

    /**
     * Constructor
     *
     * @param   array  $options  Optional parameters.
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
     * Create memcached connection.
     */
    protected function getConnection()
    {
        if (!static::isSupported())
        {
            throw new RuntimeException('Memcached Extension is not available');
        }

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
     */
    protected function getDynamicClientConnection() {
        parent::$_db->setOption(Memcached::OPT_CLIENT_MODE, Memcached::DYNAMIC_CLIENT_MODE);
        parent::$_db->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);

        $this->checkConnection();
    }

    /**
     * Configuring the client with Static client mode disables the usage of Auto Discovery
     * and the client operates as it did before the introduction of Auto Discovery.
     */
    protected function getStaticClientConnection() {
        parent::$_db->setOption(Memcached::OPT_CLIENT_MODE, Memcached::STATIC_CLIENT_MODE);

        $this->checkConnection();
    }

    /**
     * Check connection to memcached server.
     */
    protected function checkConnection()
    {
        $config = JFactory::getConfig();

        $host = $config->get('memcached_server_host', 'localhost');
        $port = $config->get('memcached_server_port', 11211);

        $stats  = parent::$_db->getStats();
        $result = !empty($stats["$host:$port"]) && $stats["$host:$port"]['pid'] > 0;

        if (!$result)
        {
            // Null out the connection to inform the constructor it will need to attempt to connect if this class is instantiated again
            parent::$_db = null;

            throw new JCacheExceptionConnecting('Could not connect to memcached server. '. parent::$_db->getResultMessage());
        }
    }

}