<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Interface for managing HTTP sessions
 *
 * @since       3.5
 * @deprecated  4.0  The CMS' Session classes will be replaced with the `joomla/session` package
 */
interface JSessionHandlerInterface
{
	/**
	 * Starts the session.
	 *
	 * @return  boolean  True if started.
	 *
	 * @since   3.5
	 * @throws  RuntimeException If something goes wrong starting the session.
	 */
	public function start();

	/**
	 * Checks if the session is started.
	 *
	 * @return  boolean  True if started, false otherwise.
	 *
	 * @since   3.5
	 */
	public function isStarted();

	/**
	 * Returns the session ID
	 *
	 * @return  string  The session ID
	 *
	 * @since   3.5
	 */
	public function getId();

	/**
	 * Sets the session ID
	 *
	 * @param   string  $id  The session ID
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function setId($id);

	/**
	 * Returns the session name
	 *
	 * @return  mixed  The session name.
	 *
	 * @since   3.5
	 */
	public function getName();

	/**
	 * Sets the session name
	 *
	 * @param   string  $name  The name of the session
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function setName($name);

	/**
	 * Regenerates ID that represents this storage.
	 *
	 * Note regenerate+destroy should not clear the session data in memory only delete the session data from persistent storage.
	 *
	 * @param   boolean  $destroy   Destroy session when regenerating?
	 * @param   integer  $lifetime  Sets the cookie lifetime for the session cookie. A null value will leave the system settings unchanged,
	 *                              0 sets the cookie to expire with browser session. Time is in seconds, and is not a Unix timestamp.
	 *
	 * @return  boolean  True if session regenerated, false if error
	 *
	 * @since   3.5
	 */
	public function regenerate($destroy = false, $lifetime = null);

	/**
	 * Force the session to be saved and closed.
	 *
	 * This method must invoke session_write_close() unless this interface is used for a storage object design for unit or functional testing where
	 * a real PHP session would interfere with testing, in which case it should actually persist the session data if required.
	 *
	 * @return  void
	 *
	 * @see     session_write_close()
	 * @since   3.5
	 * @throws  RuntimeException  If the session is saved without being started, or if the session is already closed.
	 */
	public function save();

	/**
	 * Clear all session data in memory.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function clear();
}
