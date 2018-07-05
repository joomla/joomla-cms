<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session;

/**
 * Interface defining a Joomla! session storage object
 *
 * @since  __DEPLOY_VERSION__
 */
interface StorageInterface
{
	/**
	 * Get the session name
	 *
	 * @return  string  The session name
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getName(): string;

	/**
	 * Set the session name
	 *
	 * @param   string  $name  The session name
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setName(string $name);

	/**
	 * Get the session ID
	 *
	 * @return  string  The session ID
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getId(): string;

	/**
	 * Set the session ID
	 *
	 * @param   string  $id  The session ID
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setId(string $id);

	/**
	 * Check if the session is active
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isActive(): bool;

	/**
	 * Check if the session is started
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isStarted(): bool;

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
	public function get(string $name, $default);

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
	public function set(string $name, $value);

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  boolean  True if the variable exists
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function has(string $name): bool;

	/**
	 * Unset a variable from the session store
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  mixed   The value from session or NULL if not set
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function remove(string $name);

	/**
	 * Clears all variables from the session store
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clear();

	/**
	 * Retrieves all variables from the session store
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function all(): array;

	/**
	 * Start a session
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function start();

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
	public function regenerate(bool $destroy = false): bool;

	/**
	 * Writes session data and ends session
	 *
	 * @return  void
	 *
	 * @see     session_write_close()
	 * @since   __DEPLOY_VERSION__
	 */
	public function close();

	/**
	 * Perform session data garbage collection
	 *
	 * @return  integer|boolean  Number of deleted sessions on success or boolean false on failure or if the function is unsupported
	 *
	 * @see     session_gc()
	 * @since   __DEPLOY_VERSION__
	 */
	public function gc();

	/**
	 * Aborts the current session
	 *
	 * @return  boolean
	 *
	 * @see     session_abort()
	 * @since   __DEPLOY_VERSION__
	 */
	public function abort();
}
