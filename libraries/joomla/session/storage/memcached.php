<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Memcached session storage handler for PHP
 *
 * -- Inspired in both design and implementation by the Horde memcached handler --
 *
 * @package     Joomla.Platform
 * @subpackage  Session
 * @see         http://www.php.net/manual/en/function.session-set-save-handler.php
 * @since       11.1
 */
class JSessionStorageMemcached extends JSessionStorage
{
	/**
	 * Resource for the current memcached connection.
	 *
	 * @var    resource
	 * @since  11.1
	 */
	private $_db;

	/**
	 * Use compression?
	 *
	 * @var    int
	 * @since  11.1
	 */
	private $_compress = null;

	/**
	 * Use persistent connections
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	private $_persistent = false;

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		if (!$this->test())
		{
			return JError::raiseError(404, JText::_('JLIB_SESSION_MEMCACHE_EXTENSION_NOT_AVAILABLE'));
		}

		parent::__construct($options);

		$config = JFactory::getConfig();

		$this->_compress	= $config->get('memcache_compress', false)?Memcached::OPT_COMPRESSION:false;
		$this->_persistent	= $config->get('memcache_persist', true);

		// This will be an array of loveliness
		// @todo: multiple servers
		$this->_servers = array(
			array(
				'host' => $config->get('memcache_server_host', 'localhost'),
				'port' => $config->get('memcache_server_port', 11211)
			)
		);
	}

	/**
	 * Open the SessionHandler backend.
	 *
	 * @param   string  $save_path     The path to the session object.
	 * @param   string  $session_name  The name of the session.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public function open($save_path, $session_name)
	{
		$this->_db = new Memcached;
		for ($i = 0, $n = count($this->_servers); $i < $n; $i++)
		{
			$server = $this->_servers[$i];
			$this->_db->addServer($server['host'], $server['port'], $this->_persistent);
		}
		return true;
	}

	/**
	 * Close the SessionHandler backend.
	 *
	 * @return boolean  True on success, false otherwise.
	 */
	public function close()
	{
		// $this->_db->close();
		return true;
	}

	/**
	 * Read the data for a particular session identifier from the SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  string  The session data.
	 *
	 * @since   11.1
	 */
	public function read($id)
	{
		$sess_id = 'sess_' . $id;
		$this->_setExpire($sess_id);
		return $this->_db->get($sess_id);
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @param   string  $id            The session identifier.
	 * @param   string  $session_data  The session data.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public function write($id, $session_data)
	{
		$sess_id = 'sess_' . $id;
		if ($this->_db->get($sess_id . '_expire'))
		{
			$this->_db->replace($sess_id . '_expire', time());
		}
		else
		{
			$this->_db->set($sess_id . '_expire', time());
		}
		if ($this->_db->get($sess_id))
		{
			$this->_db->replace($sess_id, $session_data);
		}
		else
		{
			$this->_db->set($sess_id, $session_data);
		}
		return;
	}

	/**
	 * Destroy the data for a particular session identifier in the SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public function destroy($id)
	{
		$sess_id = 'sess_' . $id;
		$this->_db->delete($sess_id . '_expire');
		return $this->_db->delete($sess_id);
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 * -- Not Applicable in memcached --
	 *
	 * @param   integer  $maxlifetime  The maximum age of a session.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public function gc($maxlifetime = null)
	{
		return true;
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 */
	static public function test()
	{
		return (extension_loaded('memcached') && class_exists('Memcached'));
	}

	/**
	 * Set expire time on each call since memcached sets it on cache creation.
	 *
	 * @param   string  $key  Cache key to expire.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function _setExpire($key)
	{
		$lifetime = ini_get("session.gc_maxlifetime");
		$expire = $this->_db->get($key . '_expire');

		// Set prune period
		if ($expire + $lifetime < time())
		{
			$this->_db->delete($key);
			$this->_db->delete($key . '_expire');
		}
		else
		{
			$this->_db->replace($key . '_expire', time());
		}
	}
}
