<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Storage;

use Joomla\Session\Storage;

/**
 * Memcached session storage handler for PHP
 *
 * @since       1.0
 * @deprecated  2.0  The Storage class chain will be removed
 */
class Memcached extends Storage
{
	/**
	 * Container for server data
	 *
	 * @var    array
	 * @since  1.0
	 * @deprecated  2.0
	 */
	protected $_servers = array();

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 * @deprecated  2.0
	 */
	public function __construct($options = array())
	{
		if (!self::isSupported())
		{
			throw new \RuntimeException('Memcached Extension is not available', 404);
		}

		// This will be an array of loveliness
		// @todo: multiple servers
		$this->_servers = array(
			array(
				'host' => isset($options['memcache_server_host']) ? $options['memcache_server_host'] : 'localhost',
				'port' => isset($options['memcache_server_port']) ? $options['memcache_server_port'] : 11211,
			),
		);

		// Only construct parent AFTER host and port are sent, otherwise when register is called this will fail.
		parent::__construct($options);
	}

	/**
	 * Register the functions of this class with PHP's session handler
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @deprecated  2.0
	 */
	public function register()
	{
		if (!headers_sent())
		{
			ini_set('session.save_path', $this->_servers[0]['host'] . ':' . $this->_servers[0]['port']);
			ini_set('session.save_handler', 'memcached');
		}
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 * @deprecated  2.0
	 */
	public static function isSupported()
	{
		/*
		 * GAE and HHVM have both had instances where Memcached the class was defined but no extension was loaded.
		 * If the class is there, we can assume it works.
		 */
		return class_exists('Memcached');
	}
}
