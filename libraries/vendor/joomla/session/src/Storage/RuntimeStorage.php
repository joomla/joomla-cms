<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Storage;

use Joomla\Session\StorageInterface;

/**
 * Session storage object that stores objects in Runtime memory. This is designed for use in CLI Apps, including
 * unit testing applications in PHPUnit.
 *
 * @since  __DEPLOY_VERSION__
 */
class RuntimeStorage implements StorageInterface
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
	public function all(): array
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
	private function generateId(): string
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
	public function get(string $name, $default)
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
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * Get the session name
	 *
	 * @return  string  The session name
	 */
	public function getName(): string
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
	public function has(string $name): bool
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
	public function isActive(): bool
	{
		return $this->active = $this->started;
	}

	/**
	 * Check if the session is started
	 *
	 * @return  boolean
	 */
	public function isStarted(): bool
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
	public function remove(string $name)
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		$old = $this->data[$name] ?? null;

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
	public function regenerate(bool $destroy = false): bool
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
	public function set(string $name, $value = null)
	{
		if (!$this->isStarted())
		{
			$this->start();
		}

		$old = $this->data[$name] ?? null;

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
	public function setId(string $id)
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
	public function setName(string $name)
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
			return;
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
