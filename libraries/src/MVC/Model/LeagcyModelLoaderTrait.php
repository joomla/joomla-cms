<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\MVC\Model;

defined('JPATH_PLATFORM') or die;

/**
 * Trait which contains the legacy getInstance functionality
 *
 * @since       4.0.0
 * @deprecated  5.0 Will be removed without replacement
 */
trait LeagcyModelLoaderTrait
{
	/**
	 * Add a directory where \JModelLegacy should search for models. You may
	 * either pass a string or an array of directories.
	 *
	 * @param   mixed   $path    A path or array[sting] of paths to search.
	 * @param   string  $prefix  A prefix for models.
	 *
	 * @return  array  An array with directory elements. If prefix is equal to '', all directories are returned.
	 *
	 * @since       3.0
	 * @deprecated  5.0 See getInstance
	 */
	public static function addIncludePath($path = '', $prefix = '')
	{
		static $paths;

		if (!isset($paths))
		{
			$paths = array();
		}

		if (!isset($paths[$prefix]))
		{
			$paths[$prefix] = array();
		}

		if (!isset($paths['']))
		{
			$paths[''] = array();
		}

		if (!empty($path))
		{
			jimport('joomla.filesystem.path');

			foreach ((array) $path as $includePath)
			{
				if (!in_array($includePath, $paths[$prefix]))
				{
					array_unshift($paths[$prefix], \JPath::clean($includePath));
				}

				if (!in_array($includePath, $paths['']))
				{
					array_unshift($paths[''], \JPath::clean($includePath));
				}
			}
		}

		return $paths[$prefix];
	}


	/**
	 * Create the filename for a resource
	 *
	 * @param   string  $type   The resource type to create the filename for.
	 * @param   array   $parts  An associative array of filename information.
	 *
	 * @return  string  The filename
	 *
	 * @since       3.0
	 * @deprecated  5.0 See getInstance
	 */
	protected static function _createFileName($type, $parts = array())
	{
		$filename = '';

		switch ($type)
		{
			case 'model':
				$filename = strtolower($parts['name']) . '.php';
				break;
		}

		return $filename;
	}

	/**
	 * Returns a Model object, always creating it
	 *
	 * @param   string  $type    The model type to instantiate
	 * @param   string  $prefix  Prefix for the model class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  self|boolean   A \JModelLegacy instance or false on failure
	 *
	 * @since       3.0
	 * @deprecated  5.0 Boot the component you want the model from and fetch it through the MVC factory
	 */
	public static function getInstance($type, $prefix = '', $config = array())
	{
		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$modelClass = $prefix . ucfirst($type);

		if (!class_exists($modelClass))
		{
			jimport('joomla.filesystem.path');
			$path = \JPath::find(self::addIncludePath(null, $prefix), self::_createFileName('model', array('name' => $type)));

			if (!$path)
			{
				$path = \JPath::find(self::addIncludePath(null, ''), self::_createFileName('model', array('name' => $type)));
			}

			if (!$path)
			{
				return false;
			}

			require_once $path;

			if (!class_exists($modelClass))
			{
				\JLog::add(\JText::sprintf('JLIB_APPLICATION_ERROR_MODELCLASS_NOT_FOUND', $modelClass), \JLog::WARNING, 'jerror');

				return false;
			}
		}

		return new $modelClass($config);
	}

	/**
	 * Adds to the stack of model table paths in LIFO order.
	 *
	 * @param   mixed  $path  The directory as a string or directories as an array to add.
	 *
	 * @return  void
	 *
	 * @since       3.0
	 * @deprecated  5.0 See getInstance
	 */
	public static function addTablePath($path)
	{
		\JTable::addIncludePath($path);
	}
}
