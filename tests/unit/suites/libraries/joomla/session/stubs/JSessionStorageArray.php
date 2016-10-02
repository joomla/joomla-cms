<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Session\StorageInterface;

/**
 * Interface for managing HTTP sessions
 */
class JSessionStorageArray implements StorageInterface
{
	/**
	 * Flag if the session is active
	 *
	 * @var  boolean
	 */
	private $active = false;

	/**
	 * Internal flag identifying whether the session has been closed
	 *
	 * @var  boolean
	 */
	private $closed = false;

	/**
	 * Internal data store
	 *
	 * @var  array
	 */
	private $data = array();

	/**
	 * Session ID
	 *
	 * @var  string
	 */
	private $id = '';

	/**
	 * Session Name
	 *
	 * @var  string
	 */
	private $name = 'MockSession';

	/**
	 * Internal flag identifying whether the session has been started
	 *
	 * @var  boolean
	 */
	private $started = false;

	/**
	 * Retrieves all variables from the session store
	 *
	 * @return  array
	 */
	public function all()
	{
		return $this->data;
	}

	/**
	 * Clears all variables from the session store
	 *
	 * @return  void
	 */
	public function clear()
	{
		$this->data = array();
	}

	/**
	 * Writes session data and ends session
	 *
	 * @return  void
	 *
	 * @see     session_write_close()
	 */
	public function close()
	{
		$this->closed  = true;
		$this->started = false;
	}

	/**
	 * Generates a session ID
	 *
	 * @return  string
	 */
	private function generateId()
	{
		return hash('sha256', uniqid(mt_rand()));
	}

	/**
	 * Get data from the session store
	 *
	 * @param   string  $name     Name of a variable
	 * @param   mixed   $default  Default value of a variable if not set
	 *
	 * @return  mixed  Value of a variable
	 */
	public function get($name, $default)
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		if (isset($this->data[$name]))
		{
			return $this->data[$name];
		}

		return $default;
	}

	/**
	 * Get the session ID
	 *
	 * @return  string  The session ID
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the session name
	 *
	 * @return  string  The session name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  boolean  True if the variable exists
	 */
	public function has($name)
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		return isset($this->data[$name]);
	}

	/**
	 * Check if the session is active
	 *
	 * @return  boolean
	 */
	public function isActive()
	{
		return $this->active = $this->started;
	}

	/**
	 * Check if the session is started
	 *
	 * @return  boolean
	 */
	public function isStarted()
	{
		return $this->started;
	}

	/**
	 * Unset a variable from the session store
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  mixed  The value from session or NULL if not set
	 */
	public function remove($name)
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		$old = isset($this->data[$name]) ? $this->data[$name] : null;

		unset($this->data[$name]);

		return $old;
	}

	/**
	 * Regenerates the session ID that represents this storage.
	 *
	 * This method must invoke session_regenerate_id($destroy) unless this interface is used for a storage object designed for unit
	 * or functional testing where a real PHP session would interfere with testing.
	 *
	 * @param   boolean  $destroy  Destroy session when regenerating?
	 *
	 * @return  boolean  True on success
	 *
	 * @see     session_regenerate_id()
	 */
	public function regenerate($destroy = false)
	{
		if ($destroy)
		{
			$this->id = $this->generateId();
		}

		return true;
	}

	/**
	 * Set data into the session store
	 *
	 * @param   string  $name   Name of a variable.
	 * @param   mixed   $value  Value of a variable.
	 *
	 * @return  mixed  Old value of a variable.
	 */
	public function set($name, $value = null)
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		$old = isset($this->data[$name]) ? $this->data[$name] : null;

		$this->data[$name] = $value;

		return $old;
	}

	/**
	 * Set the session ID
	 *
	 * @param   string  $id  The session ID
	 *
	 * @return  $this
	 *
	 * @throws  \LogicException
	 */
	public function setId($id)
	{
		if ($this->isActive())
		{
			throw new \LogicException('Cannot change the ID of an active session');
		}

		$this->id = $id;

		return $this;
	}

	/**
	 * Set the session name
	 *
	 * @param   string  $name  The session name
	 *
	 * @return  $this
	 *
	 * @throws  \LogicException
	 */
	public function setName($name)
	{
		if ($this->isActive())
		{
			throw new \LogicException('Cannot change the name of an active session');
		}

		$this->name = $name;

		return $this;
	}

	/**
	 * Start a session
	 *
	 * @return  void
	 */
	public function start()
	{
		if ($this->isStarted())
		{
			return true;
		}

		if ($this->isActive())
		{
			throw new \RuntimeException('Failed to start the session: already started by PHP.');
		}

		if (empty($this->id))
		{
			$this->setId($this->generateId());
		}

		$this->closed  = false;
		$this->started = true;
		$this->isActive();
	}
}
