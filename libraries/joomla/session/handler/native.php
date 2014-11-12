<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Interface for managing HTTP sessions
 *
 * @since  3.4
 */
class JSessionHandlerNative implements JSessionHandlerInterface
{
	/**
	 * Has the session been started
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	private $started;

	/**
	 * Has the session been closed
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	private $closed;

	/**
	 * Starts the session.
	 *
	 * @return  bool  True if started.
	 *
	 * @since   3.4
	 *
	 * @throws RuntimeException If something goes wrong starting the session.
	 */
	public function start()
	{
		/**
		 * Write and Close handlers are called after destructing objects since PHP 5.0.5.
		 * Thus destructors can use sessions but session handler can't use objects.
		 * So we are moving session closure before destructing objects.
		 *
		 * Replace with session_register_shutdown() when dropping compatibility with PHP 5.3
		 */
		register_shutdown_function('session_write_close');

		session_cache_limiter('none');

		// Ok to try and start the session
		if (!session_start())
		{
			throw new RuntimeException('Failed to start the session');
		}

		return true;
	}

	/**
	 * Checks if the session is started.
	 *
	 * @return  bool  True if started, false otherwise.
	 *
	 * @since   3.4
	 */
	public function isStarted()
	{
		return $this->started;
	}

	/**
	 * Returns the session ID
	 *
	 * @return  string  The session ID or empty.
	 *
	 * @since   3.4
	 */
	public function getId()
	{
		return session_id();
	}

	/**
	 * Sets the session ID
	 *
	 * @param   string  $id  Set the session id
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function setId($id)
	{
		session_id($id);
	}

	/**
	 * Returns the session name
	 *
	 * @return  mixed   The session name.
	 *
	 * @since   3.4
	 */
	public function getName()
	{
		return session_name();
	}

	/**
	 * Sets the session name
	 *
	 * @param   string  $name  Set the name of the session
	 *
	 * @return  void
	 *
	 * @since   3.4
	 *
	 * @throws  LogicException
	 */
	public function setName($name)
	{
		if ($this->isStarted())
		{
			throw new LogicException('Cannot change the name of an active session');
		}

		session_name($name);
	}

	/**
	 * Regenerates id that represents this storage.
	 *
	 * Note regenerate+destroy should not clear the session data in memory
	 * only delete the session data from persistent storage.
	 *
	 * @param   bool  $destroy   Destroy session when regenerating?
	 * @param   int   $lifetime  Sets the cookie lifetime for the session cookie. A null value
	 *                           will leave the system settings unchanged, 0 sets the cookie
	 *                           to expire with browser session. Time is in seconds, and is
	 *                           not a Unix timestamp.
	 *
	 * @return  bool  True if session regenerated, false if error
	 *
	 * @since   3.4
	 */
	public function regenerate($destroy = false, $lifetime = null)
	{
		if (null !== $lifetime)
		{
			ini_set('session.cookie_lifetime', $lifetime);
		}

		$return = session_regenerate_id($destroy);

		// Workaround for https://bugs.php.net/bug.php?id=61470 as suggested by David Grudl
		session_write_close();

		if (isset($_SESSION))
		{
			$backup = $_SESSION;
			session_start();
			$_SESSION = $backup;
		}
		else
		{
			session_start();
		}

		return $return;
	}

	/**
	 * Force the session to be saved and closed.
	 *
	 * This method must invoke session_write_close() unless this interface is
	 * used for a storage object design for unit or functional testing where
	 * a real PHP session would interfere with testing, in which case it
	 * it should actually persist the session data if required.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 *
	 * @throws  RuntimeException If the session is saved without being started, or if the session
	 *                           is already closed.
	 *
	 * @see     session_write_close()
	 */
	public function save()
	{
		session_write_close();

		$this->closed  = true;
		$this->started = false;
	}

	/**
	 * Clear all session data in memory.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function clear()
	{
		// Need to destroy any existing sessions started with session.auto_start
		if (session_id())
		{
			session_unset();
			session_destroy();
		}

		$this->closed  = true;
		$this->started = false;
	}
}
