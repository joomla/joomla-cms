<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Handler;

use Joomla\Session\HandlerInterface;

/**
 * Memcache session storage handler
 *
 * @since  __DEPLOY_VERSION__
 */
class MemcacheHandler implements HandlerInterface
{
	/**
	 * Memcache driver
	 *
	 * @var    \Memcache
	 * @since  __DEPLOY_VERSION__
	 */
	private $memcache;

	/**
	 * Session ID prefix to avoid naming conflicts
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $prefix;

	/**
	 * Time to live in seconds
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	private $ttl;

	/**
	 * Constructor
	 *
	 * @param   \Memcache  $memcache  A Memcache instance
	 * @param   array      $options   Associative array of options to configure the handler
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(\Memcache $memcache, array $options = [])
	{
		$this->memcache = $memcache;

		// Set the default time-to-live based on the Session object's default configuration
		$this->ttl = isset($options['ttl']) ? (int) $options['ttl'] : 900;

		// Namespace our session IDs to avoid potential conflicts
		$this->prefix = isset($options['prefix']) ? $options['prefix'] : 'jfw';
	}

	/**
	 * Close the session
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function close()
	{
		return $this->memcache->close();
	}

	/**
	 * Destroy a session
	 *
	 * @param   integer  $session_id  The session ID being destroyed
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function destroy($session_id)
	{
		return $this->memcache->delete($this->prefix . $session_id);
	}

	/**
	 * Cleanup old sessions
	 *
	 * @param   integer  $maxlifetime  Sessions that have not updated for the last maxlifetime seconds will be removed
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function gc($maxlifetime)
	{
		// Memcache manages garbage collection on its own
		return true;
	}

	/**
	 * Test to see if the HandlerInterface is available
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		return extension_loaded('memcache') && class_exists('Memcache');
	}

	/**
	 * Initialize session
	 *
	 * @param   string  $save_path   The path where to store/retrieve the session
	 * @param   string  $session_id  The session id
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function open($save_path, $session_id)
	{
		return true;
	}

	/**
	 * Read session data
	 *
	 * @param   string  $session_id  The session id to read data for
	 *
	 * @return  string  The session data
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function read($session_id)
	{
		return $this->memcache->get($this->prefix . $session_id) ?: '';
	}

	/**
	 * Write session data
	 *
	 * @param   string  $session_id    The session id
	 * @param   string  $session_data  The encoded session data
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function write($session_id, $session_data)
	{
		return $this->memcache->set($this->prefix . $session_id, $session_data, 0, time() + $this->ttl);
	}
}
