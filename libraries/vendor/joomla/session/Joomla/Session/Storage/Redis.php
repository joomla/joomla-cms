<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Storage;

use Joomla\Session\Storage;

/**
 * Redis session storage handler for PHP
 *
 * @since  13.1
 * @deprecated  The joomla/session package is deprecated
 */
class Redis extends Storage
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   13.1
	 * @throws  \RuntimeException
	 */
	public function __construct($options = array())
	{
		if (!self::isSupported())
		{
			throw new \RuntimeException('Redis Extension is not available', 404);
		}

		// This will be an array of loveliness
		// @todo: multiple servers
		$this->_servers = array(
			array(
				'host' => isset($options['session_redis_server_host']) ? $options['session_redis_server_host'] : 'localhost',
				'port' => isset($options['session_redis_server_port']) ? $options['session_redis_server_port'] : 6379,
				'persist' => isset($options['session_redis_persist']) ? $options['session_redis_persist'] : 1,
				'weight' => isset($options['session_redis_weight']) ? $options['session_redis_weight'] : 1,
				'db' => isset($options['session_redis_db']) ? $options['session_redis_db'] : 0		
			)
		);

		// Only construct parent AFTER host and port are sent, otherwise when register is called this will fail.
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
		if ($this->_servers[0]['port'] == -1 || $this->_servers[0]['port'] == 0) 
		{
			$savepath_connection = 'unix://' . $this->_servers[0]['host'];
		}
		else
		{
			$savepath_connection = 'tcp://' . $this->_servers[0]['host'] . ':' . $this->_servers[0]['port'];
		}
		$savepath_options = '?persistent=' . $this->_servers[0]['persist'] . '&weight=' . $this->_servers[0]['weight'] . '&database=' . $this->_servers[0]['db'];
		ini_set('session.save_path', $savepath_connection . $savepath_options);
		ini_set('session.save_handler', 'redis');	
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
		// GAE and HHVM have both had instances where Redis the class was defined but no extension was loaded.  If the class is there, we can assume it works.
		return (class_exists('Redis'));
	}
}
