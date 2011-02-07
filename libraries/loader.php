<?php
/**
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 * @package    Joomla.Platform
 */

defined('JPATH_PLATFORM') or die;

// Register JLoader::load as an autoload class handler.
spl_autoload_register(array('JLoader','load'));

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
	private static $imported = array();

	/**
	 * Container for already imported library paths.
	 *
	 * @var    array
	 * @since  11.1
	 */
	private static $classes = array();

	/**
	 * Loads a class from specified directories.
	 *
	 * @param   string  $key   The class name to look for (dot notation).
	 * @param   string  $base  Search this directory for the class.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function import($key, $base = null)
	{
		// Only import the library if not already attempted.
		if (!isset(self::$imported[$key]))
		{
			// Setup some variables.
			$success	= false;
			$parts		= explode('.', $key);
			$class		= array_pop($parts);
			$base		= (!empty($base)) ? $base : dirname(__FILE__);
			$path		= str_replace('.', DS, $key);

			// Handle special case for helper classes.
			if ($class == 'helper') {
				$class = ucfirst(array_pop($parts)).ucfirst($class);
			}
			// Standard class.
			else {
				$class = ucfirst($class);
			}

			// If we are importing a library from the Joomla namespace set the class to autoload.
			if (strpos($path, 'joomla') === 0) {

				// Since we are in the Joomla namespace prepend the classname with J.
				$class	= 'J'.$class;

				// Only register the class for autoloading if the file exists.
				if (is_file($base.DS.$path.'.php')) {
					self::$classes[strtolower($class)] = $base.DS.$path.'.php';
					$success = true;
				}
			}

			/*
			 * If we are not importing a library from the Joomla namespace directly include the
			 * file since we cannot assert the file/folder naming conventions.
			 */
			else {

				// If the file exists attempt to include it.
				if (is_file($base.DS.$path.'.php')) {
					$success = include $base.DS.$path.'.php';
				}
			}

			// Add the import key to the memory cache container.
			self::$imported[$key] = $success;
		}

		return self::$imported[$key];
	}

	/**
	 * Directly register a class to the autoload list.
	 *
	 * @param   string  $class  The class name
	 * @param   string  $path   Full path to the file that holds the class
	 *
	 * @return  bool    True on success.
	 *
	 * @since   11.1
	 */
	public static function register ($class, $path)
	{
		// Only register the class if the class and file exist.
		if (!empty($class) && is_file($path)) {
			self::$classes[strtolower($class)] = $path;
			return true;
		}

		return false;
	}

	/**
	 * Load the file for a class.
	 *
	 * @param   string  $class  The class to be loaded.
	 *
	 * @return  bool    True on success
	 *
	 * @since   11.1
	 */
	public static function load($class)
	{
		// Sanitize class name.
		$class = strtolower($class);

		// If the class already exists do nothing.
		if (class_exists($class)) {
			  return;
		}

		// If the class is registered include the file.
		if (isset(self::$classes[$class])) {
			include self::$classes[$class];
			return true;
		}

		return false;
	}
}

/**
 * Global application exit.
 *
 * This function provides a single exit point for the framework.
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
 * @param   string  $path  A dot syntax path.
 *
 * @return  bool    True on success.
 *
 * @since   11.1
 */
function jimport($path)
{
	return JLoader::import($path);
}
