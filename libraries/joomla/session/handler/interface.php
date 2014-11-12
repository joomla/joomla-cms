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
 * @package     Joomla.Platform
 * @subpackage  Session
 * @since       3.4
 */
interface JSessionHandlerInterface
{
	/**
	 * Starts the session.
	 *
	 * @return  bool  True if started.
	 *
	 * @since   3.4
	 *
	 * @throws RuntimeException If something goes wrong starting the session.
	 */
	public function start();

	/**
	 * Checks if the session is started.
	 *
	 * @return  bool  True if started, false otherwise.
	 *
	 * @since   3.4
	 */
	public function isStarted();

	/**
	 * Returns the session ID
	 *
	 * @return  string  The session ID or empty.
	 *
	 * @since   3.4
	 */
	public function getId();

	/**
	 * Sets the session ID
	 *
	 * @param   string  $id  Set the session id
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function setId($id);

	/**
	 * Returns the session name
	 *
	 * @return  mixed   The session name.
	 *
	 * @since   3.4
	 */
	public function getName();

	/**
	 * Sets the session name
	 *
	 * @param   string  $name  Set the name of the session
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function setName($name);

	/**
	 * Regenerates id that represents this storage.
	 *
	 * This method must invoke session_regenerate_id($destroy) unless
	 * this interface is used for a storage object designed for unit
	 * or functional testing where a real PHP session would interfere
	 * with testing.
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
	 *
	 * @throws  RuntimeException  If an error occurs while regenerating this storage
	 */
	public function regenerate($destroy = false, $lifetime = null);

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
	 * @throws RuntimeException If the session is saved without being started, or if the session
	 *                           is already closed.
	 */
	public function save();

	/**
	 * Clear all session data in memory.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function clear();
}
