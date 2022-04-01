<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Memcached session storage handler for PHP
 *
 * @since       1.7.0
 * @deprecated  4.0  The CMS' Session classes will be replaced with the `joomla/session` package
 */
class JSessionStorageMemcached extends JSessionStorage
{
	/**
	 * @var array Container for memcache server conf arrays
	 */
	private $_servers = array();

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   1.7.0
	 * @throws  RuntimeException
	 */
	public function __construct($options = array())
	{
		if (!self::isSupported())
		{
			throw new RuntimeException('Memcached Extension is not available', 404);
		}

		$config = JFactory::getConfig();

		// This will be an array of loveliness
		// @todo: multiple servers
		$this->_servers = array(
			array(
				'host' => $config->get('session_memcached_server_host', 'localhost'),
				'port' => $config->get('session_memcached_server_port', 11211),
			),
		);

		parent::__construct($options);
	}

	/**
	 * Register the functions of this class with PHP's session handler
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 */
	public function register()
	{
		if (!empty($this->_servers) && isset($this->_servers[0]))
		{
			$serverConf = current($this->_servers);

			if (!headers_sent())
			{
				$path = $serverConf['host'];

				if ($serverConf['port'] > 0)
				{
					$path .= ':' . $serverConf['port'];
				}

				ini_set('session.save_path', $path);
				ini_set('session.save_handler', 'memcached');
			}
		}
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 *
	 * @since   3.0.0
	 */
	public static function isSupported()
	{
		return extension_loaded('memcached') && class_exists('Memcached');
	}
}
