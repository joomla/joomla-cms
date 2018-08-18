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
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $active = false;

	/**
	 * Internal flag identifying whether the session has been closed
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $closed = false;

	/**
	 * Internal data store
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $data = [];

	/**
	 * Session ID
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $id = '';

	/**
	 * Session Name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $name = 'MockSession';

	/**
	 * Internal flag identifying whether the session has been started
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
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
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function close()
	{
		$this->closed  = true;
		$this->started = false;
	}

	/**
	 * Perform session data garbage collection
	 *
	 * @return  integer|boolean  Number of deleted sessions on success or boolean false on failure or if the function is unsupported
	 *
	 * @see     session_gc()
	 * @since   __DEPLOY_VERSION__
	 */
	public function gc()
	{
		return 0;
	}

	/**
	 * Aborts the current session
	 *
	 * @return  boolean
	 *
	 * @see     session_abort()
	 * @since   __DEPLOY_VERSION__
	 */
	public function abort()
	{
		$this->closed  = true;
		$this->started = false;

		return true;
	}

	/**
	 * Generates a session ID
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
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
	 *
	 * @since   __DEPLOY_VERSION__
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
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * Get the session name
	 *
	 * @return  string  The session name
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
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
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isActive(): bool
	{
		return $this->active = $this->started;
	}

	/**
	 * Check if the session is started
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
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
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function regenerate(bool $destroy = false): bool
	{
		if (!$this->isActive())
		{
			return false;
		}

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
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
	 *
	 * @since   __DEPLOY_VERSION__
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
