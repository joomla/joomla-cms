<?php
/**
 * @version $Id$
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

/**
 * @package		Joomla.Framework
 */
class JLoader
{
	 /**
	 * Loads a class from specified directories.
	 *
	 * @param string $name	The class name to look for (dot notation).
	 * @param string $base	Search this directory for the class.
	 * @param string $key	String used as a prefix to denote the full path of the file (dot notation).
	 * @return void
	 * @since 1.5
	 */
	function import($filePath, $base = null, $key = 'libraries.')
	{
		static $paths;

		if (!isset($paths)) {
			$paths = array();
		}

		$keyPath = $key ? $key . $filePath : $filePath;

		if (!isset($paths[$keyPath]))
		{
			if (! $base) {
				$base =  dirname(__FILE__);
			}

			$parts = explode('.', $filePath);

			$classname = array_pop($parts);
			switch($classname)
			{
				case 'helper' :
					$classname = ucfirst(array_pop($parts)).ucfirst($classname);
					break;

				default :
					$classname = ucfirst($classname);
					break;
			}

			$path  = str_replace('.', DS, $filePath);

			if (strpos($filePath, 'joomla') === 0)
			{
				/*
				 * If we are loading a joomla class prepend the classname with a
				 * capital J.
				 */
				$classname	= 'J'.$classname;
				$classes	= JLoader::register($classname, $base.DS.$path.'.php');
				$rs			= isset($classes[strtolower($classname)]);
			}
			else
			{
				/*
				 * If it is not in the joomla namespace then we have no idea if
				 * it uses our pattern for class names/files so just include
				 * if the file exists or set it to false if not
				 */
				$filename = $base.DS.$path.'.php';
				if(is_file($filename)) {
					$rs   = include($filename);
				} else {
					$rs   = false; // if the file doesn't exist fail
					// note: JLoader::register does an is_file check itself
					// se we don't need it above, we do it here because we
					// try to load the file directly and it may not exist
					// which could cause php to throw up nasty warning messages
					// at us so we set it to false here and hope that if the
					// programmer is good enough they'll check the return value
					// instead of hoping it'll work. remmeber include only fires
					// a warning, so $rs was going to be false with a nasty
					// warning message
			}
			}

			$paths[$keyPath] = $rs;
		}

		return $paths[$keyPath];
	}

	/**
	 * Add a class to autoload
	 *
	 * @param	string $classname	The class name
	 * @param	string $file		Full path to the file that holds the class
	 * @return	array|boolean  		Array of classes
	 * @since 	1.5
	 */
	function & register ($class = null, $file = null)
	{
		static $classes;

		if (!isset($classes)) {
			$classes    = array();
		}

		if ($class && is_file($file))
		{
			// Force to lower case.
			$class = strtolower($class);
			$classes[$class] = $file;

			// In php4 we load the class immediately.
			if ((version_compare(phpversion(), '5.0') < 0)) {
				JLoader::load($class);
			}

		}

		return $classes;
	}


	/**
	 * Load the file for a class
	 *
	 * @access  public
	 * @param   string  $class  The class that will be loaded
	 * @return  boolean True on success
	 * @since   1.5
	 */
	function load($class)
	{
		$class = strtolower($class); //force to lower case

		if (class_exists($class)) {
			  return;
		}

		$classes = JLoader::register();
		if (array_key_exists(strtolower($class), $classes)) {
			include($classes[$class]);
			return true;
		}
		return false;
	}
}


/**
 * When calling a class that hasn't been defined, __autoload will attempt to
 * include the correct file for that class.
 *
 * This function get's called by PHP. Never call this function yourself.
 *
 * @param 	string 	$class
 * @access 	public
 * @return  boolean
 * @since   1.5
 */
function __autoload($class)
{
	if (JLoader::load($class)) {
		return true;
	}
	return false;
}

/**
 * Global application exit.
 *
 * This function provides a single exit point for the framework.
 *
 * @param mixed Exit code or string. Defaults to zero.
 */
function jexit($message = 0) {
    exit($message);
}

/**
 * Intelligent file importer
 *
 * @access public
 * @param string $path A dot syntax path
 * @since 1.5
 */
function jimport($path) {
	return JLoader::import($path);
}
