<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

JLoader::import('joomla.form.helper');

/**
 * FOFForm's helper class.
 * Provides a storage for filesystem's paths where FOFForm's entities reside and
 * methods for creating those entities. Also stores objects with entities'
 * prototypes for further reusing.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormHelper extends JFormHelper
{
	/**
	 * Method to load a form field object given a type.
	 *
	 * @param   string   $type  The field type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  mixed  JFormField object on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public static function loadFieldType($type, $new = true)
	{
		return self::loadType('field', $type, $new);
	}

	/**
	 * Method to load a form field object given a type.
	 *
	 * @param   string   $type  The field type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  mixed  JFormField object on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public static function loadHeaderType($type, $new = true)
	{
		return self::loadType('header', $type, $new);
	}

	/**
	 * Method to load a form entity object given a type.
	 * Each type is loaded only once and then used as a prototype for other objects of same type.
	 * Please, use this method only with those entities which support types (forms don't support them).
	 *
	 * @param   string   $entity  The entity.
	 * @param   string   $type    The entity type.
	 * @param   boolean  $new     Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  mixed  Entity object on success, false otherwise.
	 *
	 * @since   11.1
	 */
	protected static function loadType($entity, $type, $new = true)
	{
		// Reference to an array with current entity's type instances
		$types = &self::$entities[$entity];

		$key = md5($type);

		// Return an entity object if it already exists and we don't need a new one.
		if (isset($types[$key]) && $new === false)
		{
			return $types[$key];
		}

		$class = self::loadClass($entity, $type);

		if ($class !== false)
		{
			// Instantiate a new type object.
			$types[$key] = new $class;

			return $types[$key];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Attempt to import the JFormField class file if it isn't already imported.
	 * You can use this method outside of JForm for loading a field for inheritance or composition.
	 *
	 * @param   string  $type  Type of a field whose class should be loaded.
	 *
	 * @return  mixed  Class name on success or false otherwise.
	 *
	 * @since   11.1
	 */
	public static function loadFieldClass($type)
	{
		return self::loadClass('field', $type);
	}

	/**
	 * Attempt to import the FOFFormHeader class file if it isn't already imported.
	 * You can use this method outside of JForm for loading a field for inheritance or composition.
	 *
	 * @param   string  $type  Type of a field whose class should be loaded.
	 *
	 * @return  mixed  Class name on success or false otherwise.
	 *
	 * @since   11.1
	 */
	public static function loadHeaderClass($type)
	{
		return self::loadClass('header', $type);
	}

	/**
	 * Load a class for one of the form's entities of a particular type.
	 * Currently, it makes sense to use this method for the "field" and "rule" entities
	 * (but you can support more entities in your subclass).
	 *
	 * @param   string  $entity  One of the form entities (field or rule).
	 * @param   string  $type    Type of an entity.
	 *
	 * @return  mixed  Class name on success or false otherwise.
	 *
	 * @since   2.0
	 */
	public static function loadClass($entity, $type)
	{
		if (strpos($type, '.'))
		{
			list($prefix, $type) = explode('.', $type);
			$altPrefix = $prefix;
		}
		else
		{
			$prefix = 'FOF';
			$altPrefix = 'J';
		}

		$class = JString::ucfirst($prefix, '_') . 'Form' . JString::ucfirst($entity, '_') . JString::ucfirst($type, '_');
		$altClass = JString::ucfirst($altPrefix, '_') . 'Form' . JString::ucfirst($entity, '_') . JString::ucfirst($type, '_');

		if (class_exists($class))
		{
			return $class;
		}
		elseif (class_exists($altClass))
		{
			return $altClass;
		}

		// Get the field search path array.
		$paths = self::addPath($entity);

		// If the type is complex, add the base type to the paths.
		if ($pos = strpos($type, '_'))
		{
			// Add the complex type prefix to the paths.
			for ($i = 0, $n = count($paths); $i < $n; $i++)
			{
				// Derive the new path.
				$path = $paths[$i] . '/' . strtolower(substr($type, 0, $pos));

				// If the path does not exist, add it.
				if (!in_array($path, $paths))
				{
					$paths[] = $path;
				}
			}

			// Break off the end of the complex type.
			$type = substr($type, $pos + 1);
		}

		// Try to find the class file.
		$type       = strtolower($type) . '.php';
        $filesystem = FOFPlatform::getInstance()->getIntegrationObject('filesystem');

		foreach ($paths as $path)
		{
			if ($file = $filesystem->pathFind($path, $type))
			{
				require_once $file;

				if (class_exists($class))
				{
					break;
				}
				elseif (class_exists($altClass))
				{
					break;
				}
			}
		}

		// Check for all if the class exists.
		if (class_exists($class))
		{
			return $class;
		}
		elseif (class_exists($altClass))
		{
			return $altClass;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to add a path to the list of header include paths.
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 */
	public static function addHeaderPath($new = null)
	{
		return self::addPath('header', $new);
	}
}
