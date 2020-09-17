<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Utils;

defined('_JEXEC') || die;

use FOF30\Encrypt\Randval;
use JSessionHandlerInterface;
use RuntimeException;

class CliSessionHandler implements JSessionHandlerInterface
{
	private $id;

	private $name = 'clisession';

	public function __construct()
	{
		$this->makeId();
	}

	/**
	 * Starts the session.
	 *
	 * @return  boolean  True if started.
	 *
	 * @throws  RuntimeException If something goes wrong starting the session.
	 * @since   3.4.8
	 */
	public function start()
	{
		return true;
	}

	/**
	 * Checks if the session is started.
	 *
	 * @return  boolean  True if started, false otherwise.
	 *
	 * @since   3.4.8
	 */
	public function isStarted()
	{
		return true;
	}

	/**
	 * Returns the session ID
	 *
	 * @return  string  The session ID
	 *
	 * @since   3.4.8
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Sets the session ID
	 *
	 * @param   string  $id  The session ID
	 *
	 * @return  void
	 *
	 * @since   3.4.8
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Returns the session name
	 *
	 * @return  mixed  The session name.
	 *
	 * @since   3.4.8
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Sets the session name
	 *
	 * @param   string  $name  The name of the session
	 *
	 * @return  void
	 *
	 * @since   3.4.8
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Regenerates ID that represents this storage.
	 *
	 * Note regenerate+destroy should not clear the session data in memory only delete the session data from persistent
	 * storage.
	 *
	 * @param   boolean  $destroy   Destroy session when regenerating?
	 * @param   integer  $lifetime  Sets the cookie lifetime for the session cookie. A null value will leave the system
	 *                              settings unchanged,
	 *                              0 sets the cookie to expire with browser session. Time is in seconds, and is not a
	 *                              Unix timestamp.
	 *
	 * @return  boolean  True if session regenerated, false if error
	 *
	 * @since   3.4.8
	 */
	public function regenerate($destroy = false, $lifetime = null)
	{
		$this->makeId();

		return true;
	}

	/**
	 * Force the session to be saved and closed.
	 *
	 * This method must invoke session_write_close() unless this interface is used for a storage object design for unit
	 * or functional testing where a real PHP session would interfere with testing, in which case it should actually
	 * persist the session data if required.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException  If the session is saved without being started, or if the session is already closed.
	 * @since   3.4.8
	 * @see     session_write_close()
	 */
	public function save()
	{
		// No operation. This is a CLI session, we save nothing.
	}

	/**
	 * Clear all session data in memory.
	 *
	 * @return  void
	 *
	 * @since   3.4.8
	 */
	public function clear()
	{
		$this->makeId();
	}

	private function makeId()
	{
		$phpfunc = new Phpfunc();
		$rand    = new Randval($phpfunc);

		$this->id = md5($rand->generate(32));
	}
}
