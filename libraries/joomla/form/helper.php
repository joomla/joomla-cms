<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\String\StringHelper;

jimport('joomla.filesystem.path');

/**
 * JForm's helper class.
 * Provides a storage for filesystem's paths where JForm's entities reside and methods for creating those entities.
 * Also stores objects with entities' prototypes for further reusing.
 *
 * @since  11.1
 */
class JFormHelper
{
	/**
	 * Array with paths where entities(field, rule, form) can be found.
	 *
	 * Array's structure:
	 *
	 * paths:
	 * {ENTITY_NAME}:
	 * - /path/1
	 * - /path/2
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $paths;

	/**
	 * Static array of JForm's entity objects for re-use.
	 * Prototypes for all fields and rules are here.
	 *
	 * Array's structure:
	 * entities:
	 * {ENTITY_NAME}:
	 * {KEY}: {OBJECT}
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $entities = array();

	/**
	 * Method to load a form field object given a type.
	 *
	 * @param   string   $type  The field type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  JFormField|boolean  JFormField object on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public static function loadFieldType($type, $new = true)
	{
		return self::loadType('field', $type, $new);
	}

	/**
	 * Method to load a form rule object given a type.
	 *
	 * @param   string   $type  The rule type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  JFormRule|boolean  JFormRule object on success, false otherwise.
	 *
	 * @since   11.1
	 */
	public static function loadRuleType($type, $new = true)
	{
		return self::loadType('rule', $type, $new);
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

		if ($class === false)
		{
			return false;
		}

		// Instantiate a new type object.
		$types[$key] = new $class;

		return $types[$key];
	}

	/**
	 * Attempt to import the JFormField class file if it isn't already imported.
	 * You can use this method outside of JForm for loading a field for inheritance or composition.
	 *
	 * @param   string  $type  Type of a field whose class should be loaded.
	 *
	 * @return  string|boolean  Class name on success or false otherwise.
	 *
	 * @since   11.1
	 */
	public static function loadFieldClass($type)
	{
		return self::loadClass('field', $type);
	}

	/**
	 * Attempt to import the JFormRule class file if it isn't already imported.
	 * You can use this method outside of JForm for loading a rule for inheritance or composition.
	 *
	 * @param   string  $type  Type of a rule whose class should be loaded.
	 *
	 * @return  string|boolean  Class name on success or false otherwise.
	 *
	 * @since   11.1
	 */
	public static function loadRuleClass($type)
	{
		return self::loadClass('rule', $type);
	}

	/**
	 * Load a class for one of the form's entities of a particular type.
	 * Currently, it makes sense to use this method for the "field" and "rule" entities
	 * (but you can support more entities in your subclass).
	 *
	 * @param   string  $entity  One of the form entities (field or rule).
	 * @param   string  $type    Type of an entity.
	 *
	 * @return  string|boolean  Class name on success or false otherwise.
	 *
	 * @since   11.1
	 */
	protected static function loadClass($entity, $type)
	{
		$prefix = 'J';

		if (strpos($type, '.'))
		{
			list($prefix, $type) = explode('.', $type);
		}

		$class = StringHelper::ucfirst($prefix, '_') . 'Form' . StringHelper::ucfirst($entity, '_') . StringHelper::ucfirst($type, '_');

		if (class_exists($class))
		{
			return $class;
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
		$type = strtolower($type) . '.php';

		foreach ($paths as $path)
		{
			$file = JPath::find($path, $type);

			if (!$file)
			{
				continue;
			}

			JLoader::register($class, $file);

			if (class_exists($class))
			{
				break;
			}
		}

		// Check for all if the class exists.
		return class_exists($class) ? $class : false;
	}

	/**
	 * Method to add a path to the list of field include paths.
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @since   11.1
	 */
	public static function addFieldPath($new = null)
	{
		return self::addPath('field', $new);
	}

	/**
	 * Method to add a path to the list of form include paths.
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @since   11.1
	 */
	public static function addFormPath($new = null)
	{
		return self::addPath('form', $new);
	}

	/**
	 * Method to add a path to the list of rule include paths.
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @since   11.1
	 */
	public static function addRulePath($new = null)
	{
		return self::addPath('rule', $new);
	}

	/**
	 * Method to add a path to the list of include paths for one of the form's entities.
	 * Currently supported entities: field, rule and form. You are free to support your own in a subclass.
	 *
	 * @param   string  $entity  Form's entity name for which paths will be added.
	 * @param   mixed   $new     A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @since   11.1
	 */
	protected static function addPath($entity, $new = null)
	{
		// Reference to an array with paths for current entity
		$paths = &self::$paths[$entity];

		// Add the default entity's search path if not set.
		if (empty($paths))
		{
			// While we support limited number of entities (form, field and rule)
			// we can do this simple pluralisation:
			$entity_plural = $entity . 's';

			/*
			 * But when someday we would want to support more entities, then we should consider adding
			 * an inflector class to "libraries/joomla/utilities" and use it here (or somebody can use a real inflector in his subclass).
			 * See also: pluralization snippet by Paul Osman in JControllerForm's constructor.
			 */
			$paths[] = __DIR__ . '/' . $entity_plural;
		}

		// Force the new path(s) to an array.
		settype($new, 'array');

		// Add the new paths to the stack if not already there.
		foreach ($new as $path)
		{
			if (!in_array($path, $paths))
			{
				array_unshift($paths, trim($path));
			}

			if (!is_dir($path))
			{
				array_unshift($paths, trim($path));
			}
		}

		return $paths;
	}

	/**
	 * Parse the show on conditions
	 *
	 * @param   string  $formControl  Form name.
	 * @param   string  $showOn       Show on conditions.
	 *
	 * @return  array   Array with show on conditions.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function parseShowOnConditions($formControl, $showOn)
	{
		// Process the showon data.
		if (!$showOn)
		{
			return array();
		}

		$showOnData  = array();
		$showOnParts = preg_split('#(\[AND\]|\[OR\])#', $showOn, -1, PREG_SPLIT_DELIM_CAPTURE);
		$op = '';
		foreach ($showOnParts as $showOnPart)
		{
			if (($showOnPart === '[AND]') || $showOnPart === '[OR]')
			{
				$op = trim($showOnPart, '[]');
				continue;
			}

			$compareEqual     = strpos($showOnPart, '!:') === false;
			$showOnPartBlocks = explode(($compareEqual ? ':' : '!:'), $showOnPart, 2);

			$showOnData[] = array(
				'field'  => $formControl ? $formControl . '[' . $showOnPartBlocks[0] . ']' : $showOnPartBlocks[0],
				'values' => explode(',', $showOnPartBlocks[1]),
				'sign'   => $compareEqual === true ? '=' : '!=',
				'op'     => $op,
			);
			if ($op !== '') {
				$op = '';
			}
		}

		return $showOnData;
	}}
