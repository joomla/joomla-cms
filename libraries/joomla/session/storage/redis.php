<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Redis session storage handler for PHP
 *
 * @since  13.1
 */
class JSessionStorageRedis extends JSessionStorage
{
	/**
	 * @var array Container for redis server conf arrays
	 */
	private $_servers = array();

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   13.1
	 * @throws  RuntimeException
	 */
	public function __construct($options = array())
	{
		if (!self::isSupported())
		{
			throw new RuntimeException('Redis Extension is not available', 404);
		}

		$config = JFactory::getConfig();

		// This will be an array of loveliness
		// @todo: multiple servers
		$this->_servers = array(
			array(
				'host' => $config->get('session_redis_server_host', 'localhost'),
				'port' => $config->get('session_redis_server_port', 6379),
				'persist' => $config->get('session_redis_persist', 1),
				'weight' => $config->get('session_redis_weight', 1),
				'db' => $config->get('session_redis_db', 0)
			)
		);

		parent::__construct($options);
	}

	/**
	 * Register the functions of this class with PHP's session handler
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function register()
	{
		if (!empty($this->_servers) && isset($this->_servers[0]))
		{
			$serverConf = current($this->_servers);
			if ($serverConf['port'] == -1 || $serverConf['port'] == 0)
			{
				$savepath_connection = 'unix://' . $serverConf['host'];
			}
			else
			{
				$savepath_connection = 'tcp://' . $serverConf['host'] . ':' . $serverConf['port'];
			}
			$savepath_options = '?persistent=' . $serverConf['persist'] . '&weight=' . $serverConf['weight'] . '&database=' . $serverConf['db'];
			ini_set('session.save_path', $savepath_connection . $savepath_options);
			ini_set('session.save_handler', 'redis');
		}
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 *
	 * @since   13.1
	 */
	static public function isSupported()
	{
		return (class_exists('Redis'));
	}
}
