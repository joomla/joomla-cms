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
 * XCache session storage handler
 *
 * @since  __DEPLOY_VERSION__
 */
class XCacheHandler implements HandlerInterface
{
	/**
	 * Session ID prefix to avoid naming conflicts
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $prefix;

	/**
	 * Constructor
	 *
	 * @param   array  $options  Associative array of options to configure the handler
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(array $options = [])
	{
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
		return true;
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
		if (!xcache_isset($this->prefix . $session_id))
		{
			return true;
		}

		return xcache_unset($this->prefix . $session_id);
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
		// XCache is not supported on CLI
		return extension_loaded('xcache') && php_sapi_name() != 'cli';
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
		// Check if id exists
		if (!xcache_isset($this->prefix . $session_id))
		{
			return '';
		}

		return (string) xcache_get($this->prefix . $session_id);
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
		return xcache_set($this->prefix . $session_id, $session_data, ini_get('session.gc_maxlifetime'));
	}
}
