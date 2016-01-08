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
class JSessionHandlerArray implements JSessionHandlerInterface
{
	/**
	 * The id of the handler
	 *
	 * @var  string
	 */
	protected $id = '';

	/**
	 * The name of the handler
	 *
	 * @var  string
	 */
	protected $name;

	/**
	 * Has the session heen started
	 *
	 * @var  bool
	 */
	protected $started = false;

	/**
	 * Has the session been closed
	 *
	 * @var  bool
	 */
	protected $closed = false;

	/**
	 * @var  array
	 */
	protected $data = array();

	/**
	 * Constructor.
	 *
	 * @param string      $name    Session name
	 */
	public function __construct($name = 'MOCKSESSID')
	{
		$this->name = $name;
	}

	/**
	 * Sets the session data.
	 *
	 * @param array $array
	 */
	public function setSessionData(array $array)
	{
		$this->data = $array;
	}

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
		if ($this->started && !$this->closed) {
			return true;
		}

		if (empty($this->id)) {
			$this->setId($this->generateId());
		}

		return true;
	}

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
	public function regenerate($destroy = false, $lifetime = null)
	{
		if (!$this->started)
		{
			$this->start();
		}

		$this->id = $this->generateId();

		return true;
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
		return $this->id;
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
		if ($this->started) {
			throw new LogicException('Cannot set session ID after the session has started.');
		}

		// Set the PHP Session ID here too, it just works
		session_id($id);

		$this->id = $id;
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
		return $this->name;
	}

	/**
	 * Sets the session name
	 *
	 * @param   string  $name  Set the name of the session
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 * @throws RuntimeException If the session is saved without being started, or if the session
	 *                           is already closed.
	 */
	public function save()
	{
		if (!$this->started || $this->closed) {
			throw new \RuntimeException("Trying to save a session that was not started yet or was already closed");
		}
		// nothing to do since we don't persist the session data
		$this->closed = false;
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
		// clear out the session
		$this->data = array();
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
	 * Generates a session ID.
	 *
	 * This doesn't need to be particularly cryptographically secure since this is just
	 * a mock.
	 *
	 * @return string
	 */
	protected function generateId()
	{
		return hash('sha256', uniqid(mt_rand()));
	}
}
