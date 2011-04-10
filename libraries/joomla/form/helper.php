<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Form
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');

/**
 * JForm's helper class.
 * Provides a storage for filesystem's paths where JForm's entities reside and methods for creating those entities.
 * Also stores objects with entities' prototypes for further reusing.
 *
 * @package		Joomla.Platform
 * @subpackage	Form
 * @since		11.1
 */
class JFormHelper
{
	/**
	 * Array with paths where entities(field, rule, form) can be found.
	 *
	 * Array's structure:
	 * <code>
	 * paths:
	 * 	{ENTITY_NAME}:
	 *		- /path/1
	 *		- /path/2
	 * </code>
	 *
	 * @var		array
	 * @since	11.1
	 *
	 */
	protected static $paths;

	/**
	 * Static array of JForm's entity objects for re-use.
	 * All field's and rule's prototypes are here.
	 *
	 * Array's structure:
	 * <code>
	 * entities:
	 * 	{ENTITY_NAME}:
	 *			{KEY}: {OBJECT}
	 * </code>
	 *
	 * @var		array
	 * @since	11.1
	 */
	protected static $entities = array();

	/**
	 * Method to load a form field object given a type.
	 *
	 * @param	string	$type	The field type.
	 * @param	boolean	$new	Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return	mixed	JFormField object on success, false otherwise.
	 * @since	11.1
	 */
	public static function loadFieldType($type, $new = true)
	{
		return self::loadType('field', $type, $new);
	}

	/**
	 * Method to load a form rule object given a type.
	 *
	 * @param	string	$type	The rule type.
	 * @param	boolean	$new	Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return	mixed	JFormRule object on success, false otherwise.
	 * @since	11.1
	 */
	public static function loadRuleType($type, $new = true)
	{
		return self::loadType('rule', $type, $new);
	}

	/**
	 * Method to load a form entity object given a type.
	 * Each type is loaded only once and then used as a prototype for other objects of same type.
	 * Please, use this method only with those entities which support types (forms aren't support them).
	 *
	 * @param	string	$type	The entity type.
	 * @param	boolean	$new	Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return	mixed	Entity object on success, false otherwise.
	 * @since	11.1
	 */
	protected static function loadType($entity, $type, $new = true)
	{
		// Reference to an array with current entity's type instances
		$types = &self::$entities[$entity];

		// Initialize variables.
		$key	= md5($type);
		$class	= '';

		// Return an entity object if it already exists and we don't need a new one.
		if (isset($types[$key]) && $new === false) {
			return $types[$key];
		}

		if ( ($class = self::loadClass($entity, $type)) !== false) {
			// Instantiate a new type object.
			$types[$key] = new $class();
			return $types[$key];
		}
		else {
			return false;
		}
	}

	/**
	 * Attempt to import the JFormField class file if it isn't already imported.
	 * You can use this method outside of JForm for loading a field for inheritance or composition.
	 *
	 * @param	string	Type of a field whose class should be loaded.
	 * @return	mixed	Class name on success or false otherwise.
	 * @since	11.1
	 */
	public static function loadFieldClass($type)
	{
		return self::loadClass('field', $type);
	}

	/**
	 * Attempt to import the JFormRule class file if it isn't already imported.
	 * You can use this method outside of JForm for loading a rule for inheritance or composition.
	 *
	 * @param	string	Type of a rule whose class should be loaded.
	 * @return	mixed	Class name on success or false otherwise.
	 * @since	11.1
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
	 * @param	string	One of the form entities (field or rule).
	 * @param	string	Type of an entity.
	 *
	 * @return	mixed	Class name on success or false otherwise.
	 */
	protected static function loadClass($entity, $type)
	{
		$class = 'JForm'.ucfirst($entity).ucfirst($type);
		if (class_exists($class)) return $class;

		// Get the field search path array.
		$paths = JFormHelper::addPath($entity);

		// If the type is complex, add the base type to the paths.
		if ($pos = strpos($type, '_')) {

			// Add the complex type prefix to the paths.
			for ($i = 0, $n = count($paths); $i < $n; $i++) {
				// Derive the new path.
				$path = $paths[$i].'/'.strtolower(substr($type, 0, $pos));

				// If the path does not exist, add it.
				if (!in_array($path, $paths)) {
					array_unshift($paths, $path);
				}
			}
			// Break off the end of the complex type.
			$type = substr($type, $pos+1);
		}

		// Try to find the class file.
		if ($file = JPath::find($paths, strtolower($type).'.php')) {
			require_once $file;
		}

		// Check for all if the class exists.
		return class_exists($class) ? $class : false;
	}

	/**
	 * Method to add a path to the list of field include paths.
	 *
	 * @param	mixed	$new	A path or array of paths to add.
	 *
	 * @return	array	The list of paths that have been added.
	 * @since	11.1
	 */
	public static function addFieldPath($new = null)
	{
		return self::addPath('field', $new);
	}

	/**
	 * Method to add a path to the list of form include paths.
	 *
	 * @param	mixed	$new	A path or array of paths to add.
	 *
	 * @return	array	The list of paths that have been added.
	 * @since	11.1
	 */
	public static function addFormPath($new = null)
	{
		return self::addPath('form', $new);
	}

	/**
	 * Method to add a path to the list of rule include paths.
	 *
	 * @param	mixed	$new	A path or array of paths to add.
	 *
	 * @return	array	The list of paths that have been added.
	 * @since	11.1
	 */
	public static function addRulePath($new = null)
	{
		return self::addPath('rule', $new);
	}

	/**
	 * Method to add a path to the list of include paths for one of the form's entities.
	 * Currently supported entities: field, rule and form. You are free to support your own in a subclass.
	 *
	 * @param	string	Form's entity name for which paths will be added.
	 * @param	mixed	A path or array of paths to add.
	 *
	 * @return	array	The list of paths that have been added.
	 * @since	11.1
	 */
	protected static function addPath($entity, $new = null)
	{
		// Reference to an array with paths for current entity
		$paths = &self::$paths[$entity];

		// Add the default entity's search path if not set.
		if (empty($paths)) {
			// While we support limited number of entities (form, field and rule)
			// we can do this simple pluralisation:
			$entity_plural = $entity . 's';
			// But when someday we would want to support more entities, then we should consider adding
			// an inflector class to "libraries/joomla/utilities" and use it here (or somebody can use a real inflector in his subclass).
			// see also: pluralization snippet by Paul Osman in JControllerForm's constructor.
			$paths[] = dirname(__FILE__). DS . $entity_plural;
		}

		// Force the new path(s) to an array.
		settype($new, 'array');

		// Add the new paths to the stack if not already there.
		foreach ($new as $path) {
			if (!in_array($path, $paths)) {
				array_unshift($paths, trim($path));
			}
		}

		return $paths;
	}
}