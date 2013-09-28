<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Redis storage handler
 *
 * @package     Joomla.Platform
 * @subpackage  Cache
 * @author      Piotr Gasiorowski <p.gasiorowski@vipserv.org>
 */
class JCacheStorageRedis extends JCacheStorage
{

    const REDIS_HOST = '127.0.0.1';
    const REDIS_PORT = 6379;

    /**
     * Static cache of the Redis instance
     *
     * @var    object
     * @since  11.1
     */
    protected static $redis = null;


    /**
     * Constructor
     *
     * @param   array  $options  Optional parameters.
     *
     * @since   11.1
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        if (self::$redis === null)
        {
            $config = JFactory::getConfig();
            $host   = $config->get('redis_server_host', self::REDIS_HOST);
            $port   = $config->get('redis_server_port', self::REDIS_PORT);

            $params = array(
                'host'   => trim($host),
                'port'   => (int) $port,
                'schema' => ($host == 'localhost' || $this->isIP($host))? 'tcp' : 'unix',
            );

            if ($this->isPredisAvailable())
            {
                self::$redis = $this->initRedisPear($params);
            }
            elseif ($this->isExtensionAvailable())
            {
                self::$redis = $this->initRedisExtension($params);
            }
        }
    }

    /**
     * Instantiates the Predis object using Redis PHP Extension
     * Only initializes the engine if it does not already exist.
     * Note this is a protected method
     *
     * @param   array       $params  Connection parameters
     *
     * @return  object      Redis
     * @throws Exception
     * @see https://github.com/nicolasff/phpredis
     */
    protected function initRedisExtension($params)
    {
        $uri = ($params['schema'] == 'tcp')? 'tcp://'. $params['host'] .':'. $params['port'] : $params['host'];
        $port = ($params['schema'] == 'tcp')? $params['port'] : null;

        $redis = new Redis();
        $redis->connect($uri, $port);

        return $redis;
    }

    /**
     * Instantiates the Predis object using Predis Class from PEAR
     * Only initializes the engine if it does not already exist.
     * Note this is a protected method
     *
     * @param   array       $params  Connection parameters
     *
     * @return  object      Predis\Client
     * @throws Exception
     * @see https://github.com/nrk/predis
     */
    protected function initRedisPear($params)
    {
        $uri = ($params['schema'] == 'tcp')? 'tcp://'. $params['host'] .':'. $params['port'] : $params['host'];
        $connection = null;

        // Check if additional php extension is available
        if ($this->isPhpiredisAvailable())
        {
            $connection = array();

            if ($params['schema'] == 'tcp')
            {
                $connection['tcp'] = 'Predis\Connection\PhpiredisConnection';
            }
            else
            {
                $connection['unix'] = 'Predis\Connection\PhpiredisStreamConnection';
            }
        }

        @include_once 'Predis/Autoloader.php';
        \Predis\Autoloader::register();

        $redis = new Predis\Client($uri, $connection);

        return $redis;
    }

    /**
     * Get cached data from a file by id and group
     *
     * @param   string   $id         The cache data id.
     * @param   string   $group      The cache data group.
     * @param   boolean  $checkTime  True to verify cache time expiration threshold.
     *
     * @return  mixed  Boolean false on failure or a cached data string.
     *
     * @since   11.1
     */
    public function get($id, $group, $checkTime = true)
    {
        $data = self::$redis->get($this->_getCacheId($id, $group));

        return is_null($data)? false : $data;
    }

    /**
     * Get all cached data
     *
     * @return  array
     *
     * @since   11.1
     */
    public function getAll()
    {
        parent::getAll();

        // Get Regis keys
        $keys = self::$redis->keys($this->_hash.'-cache-*');

        if (empty($keys))
            return array();

        $data = array();

        foreach ($keys as $key)
        {
            // Extract group name
            list(,,$group) = explode('-' ,$key);

            if (!isset($data[$group]))
            {
                $data[$group] = new JCacheStorageHelper($group);
            }

            if ($value = self::$redis->get($key))
            {
                $data[$group]->updateSize(strlen($value));
            }
        }

        return $data;
    }

    /**
     * Store the data to a file by id and group
     *
     * @param   string  $id     The cache data id.
     * @param   string  $group  The cache data group.
     * @param   string  $data   The data to store in cache.
     *
     * @return  boolean  True on success, false otherwise
     *
     * @since   11.1
     */
    public function store($id, $group, $data)
    {
        $hashId = $this->_getCacheId($id, $group);

        if (!self::$redis->set($hashId, $data))
            return false;

        if($this->_lifetime)
        {
            if (!self::$redis->expire($hashId, $this->_lifetime * 60))
                return false;
        }

        return true;
    }

    /**
     * Remove a cached data file by id and group
     *
     * @param   string  $id     The cache data id
     * @param   string  $group  The cache data group
     *
     * @return  boolean  True on success, false otherwise
     *
     * @since   11.1
     */
    public function remove($id, $group)
    {
        $result = self::$redis->del($this->_getCacheId($id, $group));

        return (bool) $result;
    }

    /**
     * Clean cache for a group given a mode.
     *
     * @param   string  $group  The cache data group.
     * @param   string  $mode   The mode for cleaning cache [group|notgroup].
     * group mode    : cleans all cache in the group
     * notgroup mode : cleans all cache not in the group
     *
     * @return  boolean  True on success, false otherwise.
     * @throws InvalidArgumentException if used mode is not any of group|notgroup
     * @since   11.1
     */
    public function clean($group, $mode = null)
    {
        $result = true;

        if ($mode == 'group')
        {
            // Escape special characters
            $groupName = str_replace(array( '?',  '*',  '[',  ']'),
                array('\?', '\*', '\[', '\]'),
                $group);

            // Get all keys that match the pattern
            $keys = self::$redis->keys($this->_hash . '-cache-' . $groupName . '-*');

            if (empty($keys))
                return true;

            foreach ($keys as $key)
            {
                // Delete key
                if (!self::$redis->del($key))
                    $result = false;
            }
        }
        elseif ($mode == 'notgroup')
        {
            // Get all keys that match the pattern
            $keys = self::$redis->keys($this->_hash . '-cache-*');

            if (empty($keys))
                return true;

            foreach ($keys as $key)
            {
                list(,, $groupName) = explode('-', $key);

                if ($groupName == $group)
                    continue;

                // Delete key
                if (!self::$redis->del($key))
                    $result = false;
            }

        }

        return (bool) $result;
    }


    /**
     * Garbage collect expired cache data
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   11.1
     */
    public function gc()
    {
        // Redis handles it on its own
        return true;
    }


    /**
     * Test to see if the cache storage is available.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   12.1
     */
    public static function isSupported()
    {
        if (self::isExtensionAvailable() || self::isPredisAvailable())
        {
            return true;
        }
        else
        {
            return false;
        }
    }


    /**
     * Check if Redis PHP Extension is available
     *
     * @return  boolean  True on success, false otherwise.
     */
    protected static function isExtensionAvailable()
    {
        return (extension_loaded('redis') && class_exists('Redis'));
    }

    /**
     * Check if Predis is available from PEAR
     *
     * @return  boolean  True on success, false otherwise.
     */
    protected static function isPredisAvailable()
    {
        @include_once 'Predis/Autoloader.php';

        return class_exists('Predis\Autoloader');
    }

    /**
     * Check if phpiredis PHP Extension is available
     *
     * @return  boolean  True on success, false otherwise.
     */
    protected static function isPhpiredisAvailable()
    {
        return (extension_loaded('phpiredis') && function_exists('phpiredis_connect'));
    }


    /**
     * Checks is given string is a valid TCP string
     */
    protected function isIP($ip = '')
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }
}
