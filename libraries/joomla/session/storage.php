<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Custom session storage handler for PHP
 *
 * @see    https://secure.php.net/manual/en/function.session-set-save-handler.php
 * @todo   When dropping compatibility with PHP 5.3 use the SessionHandlerInterface and the SessionHandler class
 * @since  11.1
 */
abstract class JSessionStorage
{
	/**
	 * @var    JSessionStorage[]  JSessionStorage instances container.
	 * @since  11.3
	 */
	protected static $instances = array();

	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters.
	 *
	 * @since   11.1
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
	 * @return  JSessionStorage
	 *
	 * @since   11.1
	 * @throws  JSessionExceptionUnsupported
	 */
	public static function getInstance($name = 'none', $options = array())
	{
		$name = strtolower(JFilterInput::getInstance()->clean($name, 'word'));

		if (empty(self::$instances[$name]))
		{
			/** @var JSessionStorage $class */
			$class = 'JSessionStorage' . ucfirst($name);

			if (!class_exists($class))
			{
				$path = __DIR__ . '/storage/' . $name . '.php';

				if (!file_exists($path))
				{
					throw new JSessionExceptionUnsupported('Unable to load session storage class: ' . $name);
				}

				JLoader::register($class, $path);

				// The class should now be loaded
				if (!class_exists($class))
				{
					throw new JSessionExceptionUnsupported('Unable to load session storage class: ' . $name);
				}
			}

			// Validate the session storage is supported on this platform
			if (!$class::isSupported())
			{
				throw new JSessionExceptionUnsupported(sprintf('The %s Session Storage is not supported on this platform.', $name));
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
	 * @since   11.1
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
	 * @since   11.1
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
	 * @since   11.1
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
	 * @since   11.1
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
	 * @since   11.1
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
	 * @since   11.1
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
	 * @since   11.1
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
	 * @since   12.1
	 */
	public static function isSupported()
	{
		return true;
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   11.1
	 * @deprecated  12.3 (Platform) & 4.0 (CMS) - Use JSessionStorage::isSupported() instead.
	 */
	public static function test()
	{
		JLog::add('JSessionStorage::test() is deprecated. Use JSessionStorage::isSupported() instead.', JLog::WARNING, 'deprecated');

		return static::isSupported();
	}
}
