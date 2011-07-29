<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

// Register JLoader::load as an autoload class handler.
spl_autoload_register(array('JLoader', 'load'));

/**
 * Static class to handle loading of libraries.
 *
 * @package  Joomla.Platform
 * @since    11.1
 */
abstract class JLoader
{
	/**
	 * Container for already imported library paths.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $_imported = array();

	/**
	 * Container for already imported library paths.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $_classes = array();

	/**
	 * Loads a class from specified directories.
	 *
	 * @param   string   $key   The class name to look for (dot notation).
	 * @param   string   $base  Search this directory for the class.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public static function import($key, $base = null)
	{
		// Only import the library if not already attempted.
		if (!isset(self::$_imported[$key]))
		{
			// Setup some variables.
			$success = false;
			$parts = explode('.', $key);
			$class = array_pop($parts);
			$base = (!empty($base)) ? $base : dirname(__FILE__);
			$path = str_replace('.', DS, $key);

			// Handle special case for helper classes.
			if ($class == 'helper')
			{
				$class = ucfirst(array_pop($parts)) . ucfirst($class);
			}
			// Standard class.
			else
			{
				$class = ucfirst($class);
			}

			// If we are importing a library from the Joomla namespace set the class to autoload.
			if (strpos($path, 'joomla') === 0)
			{

				// Since we are in the Joomla namespace prepend the classname with J.
				$class = 'J' . $class;

				// Only register the class for autoloading if the file exists.
				if (is_file($base . '/' . $path . '.php'))
				{
					self::$_classes[strtolower($class)] = $base . '/' . $path . '.php';
					$success = true;
				}
			}
			/*
			 * If we are not importing a library from the Joomla namespace directly include the
			 * file since we cannot assert the file/folder naming conventions.
			 */
			else
			{

				// If the file exists attempt to include it.
				if (is_file($base . '/' . $path . '.php'))
				{
					$success = (bool) include_once $base . '/' . $path . '.php';
				}
			}

			// Add the import key to the memory cache container.
			self::$_imported[$key] = $success;
		}

		return self::$_imported[$key];
	}

	/**
	 * Method to discover classes of a given type in a given path.
	 *
	 * @param   string   $classPrefix  The class name prefix to use for discovery.
	 * @param   string   $parentPath   Full path to the parent folder for the classes to discover.
	 * @param   boolean  $force        True to overwrite the autoload path value for the class if it already exists.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function discover($classPrefix, $parentPath, $force = true)
	{
		// Ignore the operation if the folder doesn't exist.
		if (is_dir($parentPath))
		{

			// Open the folder.
			$d = dir($parentPath);

			// Iterate through the folder contents to search for input classes.
			while (false !== ($entry = $d->read()))
			{
				// Only load for php files.
				if (file_exists($parentPath . '/' . $entry) && (substr($entry, strrpos($entry, '.') + 1) == 'php'))
				{

					// Get the class name and full path for each file.
					$class = strtolower($classPrefix . preg_replace('#\.[^.]*$#', '', $entry));
					$path = $parentPath . '/' . $entry;

					// Register the class with the autoloader if not already registered or the force flag is set.
					if (empty(self::$_classes[$class]) || $force)
					{
						JLoader::register($class, $path);
					}
				}
			}

			// Close the folder.
			$d->close();
		}
	}

	/**
	 * Method to get the list of registered classes and their respective file paths for the autoloader.
	 *
	 * @return  array  The array of class => path values for the autoloader.
	 *
	 * @since   11.1
	 */
	public static function getClassList()
	{
		return self::$_classes;
	}

	/**
	 * Directly register a class to the autoload list.
	 *
	 * @param   string  $class  The class name to register.
	 * @param   string  $path   Full path to the file that holds the class to register.
	 * @param   bool    $force  True to overwrite the autoload path value for the class if it already exists.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function register($class, $path, $force = true)
	{
		// Sanitize class name.
		$class = strtolower($class);

		// Only attempt to register the class if the name and file exist.
		if (!empty($class) && is_file($path))
		{

			// Register the class with the autoloader if not already registered or the force flag is set.
			if (empty(self::$_classes[$class]) || $force)
			{
				self::$_classes[$class] = $path;
			}
		}
	}

	/**
	 * Load the file for a class.
	 *
	 * @param   string   $class  The class to be loaded.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function load($class)
	{
		// Sanitize class name.
		$class = strtolower($class);

		// If the class already exists do nothing.
		if (class_exists($class))
		{
			return;
		}

		// If the class is registered include the file.
		if (isset(self::$_classes[$class]))
		{
			include_once self::$_classes[$class];
			return true;
		}

		return false;
	}
}

/**
 * Global application exit.
 *
 * This function provides a single exit point for the platform.
 *
 * @param   mixed  $message  Exit code or string. Defaults to zero.
 *
 * @return  void
 *
 * @since   11.1
 */
function jexit($message = 0)
{
	exit($message);
}

/**
 * Intelligent file importer.
 *
 * @param   string   $path  A dot syntax path.
 *
 * @return  boolean  True on success.
 *
 * @since   11.1
 */
function jimport($path)
{
	return JLoader::import($path);
}
