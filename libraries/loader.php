<?php
/**
 * @version $Id$
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

spl_autoload_register(array('JLoader','load'));

/**
 * @package		Joomla.Framework
 */
abstract class JLoader
{
	private static $paths = array();

	private static $classes = array();

	/**
	 * Loads a class from specified directories.
	 *
	 * @param string	The class name to look for (dot notation).
	 * @param string	Search this directory for the class.
	 * @param string	String used as a prefix to denote the full path of the file (dot notation).
	 * @since 1.5
	 */
	public static function import($filePath, $base = null, $key = 'libraries.')
	{
		$keyPath = $key ? $key . $filePath : $filePath;

		if (!isset(JLoader::$paths[$keyPath]))
		{
			if (!$base) {
				$base = dirname(__FILE__);
			}

			$parts = explode('.', $filePath);

			$className = array_pop($parts);
			switch ($className)
			{
				case 'helper' :
					$className = ucfirst(array_pop($parts)).ucfirst($className);
					break;

				default :
					$className = ucfirst($className);
					break;
			}

			$path = str_replace('.', DS, $filePath);

			if (strpos($filePath, 'joomla.') === 0)
			{
				// If we are loading a joomla class prepend the classname with a capital J.
				$className = 'J'.$className;
				$classes = JLoader::register($className, $base.'/'.$path.'.php');
				$rs = isset($classes[strtolower($className)]);
			}
			else
			{
				// If it is not in the joomla namespace then we have no idea if
				// it uses our pattern for class names/files so just include
				// if the file exists or set it to false if not

				$filename = $base.'/'.$path.'.php';
				if (is_file($filename))
				{
					$rs   = (bool) include $filename;
				}
				else
				{
					// if the file doesn't exist fail
					$rs   = false;

					// note: JLoader::register does an is_file check itself so we don't need it above, we do it here because we
					// try to load the file directly and it may not exist which could cause php to throw up nasty warning messages
					// at us so we set it to false here and hope that if the programmer is good enough they'll check the return value
					// instead of hoping it'll work. remmeber include only fires a warning, so $rs was going to be false with a nasty
					// warning message
				}
			}

			JLoader::$paths[$keyPath] = $rs;
		}

		return JLoader::$paths[$keyPath];
	}

	/**
	 * Add a class to autoload.
	 *
	 * @param	string			The class name
	 * @param	string			Full path to the file that holds the class
	 * @return	array|boolean	Array of classes
	 * @since	1.5
	 */
	public static function &register($class = null, $file = null)
	{
		if ($class && is_file($file))
		{
			// Force to lower case.
			$class = strtolower($class);
			JLoader::$classes[$class] = $file;
		}

		return JLoader::$classes;
	}

	/**
	 * Load the file for a class
	 *
	 * @param   string	The class that will be loaded
	 * @return  boolean True on success
	 * @since   1.5
	 */
	public static function load($class)
	{
		$class = strtolower($class); //force to lower case

		if (class_exists($class)) {
			return true;
		}

		if (array_key_exists($class, JLoader::$classes))
		{
			include JLoader::$classes[$class];
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
 * @param	mixed	Exit code or string. Defaults to zero.
 * @since	1.5
 */
function jexit($message = 0)
{
	exit($message);
}

/**
 * Intelligent file importer
 *
 * @param	string	A dot syntax path.
 * @since	1.5
 */
function jimport($path)
{
	return JLoader::import($path);
}
