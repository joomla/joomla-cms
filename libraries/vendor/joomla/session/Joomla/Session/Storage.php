<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session;

use Joomla\Filter\InputFilter;

/**
 * Custom session storage handler for PHP
 *
 * @see    http://www.php.net/manual/en/function.session-set-save-handler.php
 * @todo   When dropping compatibility with PHP 5.3 use the SessionHandlerInterface and the SessionHandler class
 * @since  1.0
 * @deprecated  The joomla/session package is deprecated
 */
abstract class Storage
{
	/**
	 * @var    array  JSessionStorage instances container.
	 * @since  1.0
	 */
	protected static $instances = array();

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   1.0
	 */
	public function __construct($options = array())
	{
		$this->register($options);
	}

	/**
	 * Returns a session storage handler object, only creating it if it doesn't already exist.
	 *
	 * @param   string  $name     The session store to instantiate
	 * @param   array   $options  Array of options
	 *
	 * @return  Storage
	 *
	 * @since   1.0
	 */
	public static function getInstance($name = 'none', $options = array())
	{
		$filter = new InputFilter;
		$name = strtolower($filter->clean($name, 'word'));

		if (empty(self::$instances[$name]))
		{
			$class = '\\Joomla\\Session\\Storage\\' . ucfirst($name);

			if (!class_exists($class))
			{
				$path = __DIR__ . '/storage/' . $name . '.php';

				if (file_exists($path))
				{
					require_once $path;
				}
				else
				{
					// No attempt to die gracefully here, as it tries to close the non-existing session
					exit('Unable to load session storage class: ' . $name);
				}
			}

			self::$instances[$name] = new $class($options);
		}

		return self::$instances[$name];
	}

	/**
	 * Register the functions of this class with PHP's session handler
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function register()
	{
		// Use this object as the session handler
		session_set_save_handler(
			array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'),
			array($this, 'destroy'), array($this, 'gc')
		);
	}

	/**
	 * Open the SessionHandler backend.
	 *
	 * @param   string  $save_path     The path to the session object.
	 * @param   string  $session_name  The name of the session.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public function open($save_path, $session_name)
	{
		return true;
	}

	/**
	 * Close the SessionHandler backend.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public function close()
	{
		return true;
	}

	/**
	 * Read the data for a particular session identifier from the
	 * SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  string  The session data.
	 *
	 * @since   1.0
	 */
	public function read($id)
	{
		return;
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @param   string  $id            The session identifier.
	 * @param   string  $session_data  The session data.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public function write($id, $session_data)
	{
		return true;
	}

	/**
	 * Destroy the data for a particular session identifier in the
	 * SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public function destroy($id)
	{
		return true;
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 * @param   integer  $maxlifetime  The maximum age of a session.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public function gc($maxlifetime = null)
	{
		return true;
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return true;
	}
}
