<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Redis session storage handler for PHP
 *
 * @link   https://secure.php.net/manual/en/function.session-set-save-handler.php
 * @since  3.8.0
 */
class JSessionStorageRedis extends JSessionStorage
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   3.8.0
	 */
	public function __construct($options = array())
	{
		if (!self::isSupported())
		{
			throw new RuntimeException('Redis Extension is not available', 404);
		}

		$config = JFactory::getConfig();

		$this->_server = array(
			'host' => $config->get('session_redis_server_host', 'localhost'),
			'port' => $config->get('session_redis_server_port', 6379),
		);

		parent::__construct($options);
	}

	/**
	 * Register the functions of this class with PHP's session handler
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function register()
	{
		if (!empty($this->_server) && isset($this->_server['host']) && isset($this->_server['port']))
		{
			if (!headers_sent())
			{
				ini_set('session.save_path', "{$this->_server['host']}:{$this->_server['port']}");
				ini_set('session.save_handler', 'redis');
			}

			// This is required if the configuration.php gzip is turned on
			ini_set('zlib.output_compression', 'Off');
		}
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   3.8.0
	 */
	public static function isSupported()
	{
		return extension_loaded('redis') && class_exists('Redis');
	}
}
