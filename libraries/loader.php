<?php
/**
* @version $Id$
* @package		Joomla.Framework
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

if(!defined('DS')) {
	define( 'DS', DIRECTORY_SEPARATOR );
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
	 * @param string $name	The class name to look for ( dot notation ).
	 * @param string $base	Search this directory for the class.
	 * @param string $key	String used as a prefix to denote the full path of the file ( dot notation ).
	 * @return void
	 * @since 1.5
	 */
	public static function import( $filePath, $base = null, $key = 'libraries.' )
	{
		$keyPath = $key ? $key . $filePath : $filePath;

		if (!isset(JLoader::$paths[$keyPath]))
		{
			if (!$base) {
				$base = dirname( __FILE__ );
			}

			$parts = explode( '.', $filePath );

			$classname = array_pop( $parts );
			switch($classname)
			{
				case 'helper' :
					$classname = ucfirst(array_pop( $parts )).ucfirst($classname);
					break;

				default :
					$classname = ucfirst($classname);
					break;
			}

			$path = str_replace( '.', DS, $filePath );

			if (strpos($filePath, 'joomla') === 0)
			{
				/*
				 * If we are loading a joomla class prepend the classname with a
				 * capital J.
				 */
				$classname = 'J'.$classname;
				$classes = JLoader::register($classname, $base.DS.$path.'.php');
				$rs = isset($classes[strtolower($classname)]);
			}
			else
			{
				/*
				 * If it is not in the joomla namespace then we have no idea if
				 * it uses our pattern for class names/files so just include.
				 */
				$rs   = include($base.DS.$path.'.php');
			}

			JLoader::$paths[$keyPath] = $rs;
		}

		return JLoader::$paths[$keyPath];
	}

	/**
	 * Add a class to autoload
	 *
	 * @param	string $classname	The class name
	 * @param	string $file		Full path to the file that holds the class
	 * @return	array|boolean  		Array of classes
	 * @since 	1.5
	 */
	public static function & register ($class = null, $file = null)
	{
		if($class && is_file($file))
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
	 * @access  public
	 * @param   string  $class  The class that will be loaded
	 * @return  boolean True on success
	 * @since   1.5
	 */
	public static function load( $class )
	{
		$class = strtolower($class); //force to lower case

		if (class_exists($class)) {
			  return true;
		}

		if(array_key_exists( strtolower($class), JLoader::$classes)) {
			include(JLoader::$classes[$class]);
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
function jimport( $path ) {
	return JLoader::import($path);
}

