<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

use Joomla\Filesystem\Path;
use Joomla\String\Normalise;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form's helper class.
 * Provides a storage for filesystem's paths where Form's entities reside and methods for creating those entities.
 * Also stores objects with entities' prototypes for further reusing.
 *
 * @since  1.7.0
 */
class FormHelper
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
     * @var    array[]
     * @since  1.7.0
     */
    protected static $paths;

    /**
     * The class namespaces.
     *
     * @var   array[]
     * @since 3.8.0
     */
    protected static $prefixes = ['field' => [], 'form' => [], 'rule' => [], 'filter' => []];

    /**
     * Static array of Form's entity objects for re-use.
     * Prototypes for all fields and rules are here.
     *
     * Array's structure:
     * entities:
     * {ENTITY_NAME}:
     * {KEY}: {OBJECT}
     *
     * @var    array[]
     * @since  1.7.0
     */
    protected static $entities = ['field' => [], 'form' => [], 'rule' => [], 'filter' => []];

    /**
     * Method to load a form field object given a type.
     *
     * @param   string   $type  The field type.
     * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
     *
     * @return  FormField|boolean  FormField object on success, false otherwise.
     *
     * @since   1.7.0
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
     * @return  FormRule|boolean  FormRule object on success, false otherwise.
     *
     * @since   1.7.0
     */
    public static function loadRuleType($type, $new = true)
    {
        return self::loadType('rule', $type, $new);
    }

    /**
     * Method to load a form filter object given a type.
     *
     * @param   string   $type  The rule type.
     * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
     *
     * @return  FormFilterInterface|boolean  FormRule object on success, false otherwise.
     *
     * @since   4.0.0
     */
    public static function loadFilterType($type, $new = true)
    {
        return self::loadType('filter', $type, $new);
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
     * @since   1.7.0
     */
    protected static function loadType($entity, $type, $new = true)
    {
        // Reference to an array with current entity's type instances
        $types = &self::$entities[$entity];

        $key = md5($type);

        // Return an entity object if it already exists and we don't need a new one.
        if (isset($types[$key]) && $new === false) {
            return $types[$key];
        }

        $class = self::loadClass($entity, $type);

        if ($class === false) {
            return false;
        }

        // Instantiate a new type object.
        $types[$key] = new $class();

        return $types[$key];
    }

    /**
     * Attempt to import the FormField class file if it isn't already imported.
     * You can use this method outside of Form for loading a field for inheritance or composition.
     *
     * @param   string  $type  Type of a field whose class should be loaded.
     *
     * @return  string|boolean  Class name on success or false otherwise.
     *
     * @since   1.7.0
     */
    public static function loadFieldClass($type)
    {
        return self::loadClass('field', $type);
    }

    /**
     * Attempt to import the FormRule class file if it isn't already imported.
     * You can use this method outside of Form for loading a rule for inheritance or composition.
     *
     * @param   string  $type  Type of a rule whose class should be loaded.
     *
     * @return  string|boolean  Class name on success or false otherwise.
     *
     * @since   1.7.0
     */
    public static function loadRuleClass($type)
    {
        return self::loadClass('rule', $type);
    }

    /**
     * Attempt to import the FormFilter class file if it isn't already imported.
     * You can use this method outside of Form for loading a filter for inheritance or composition.
     *
     * @param   string  $type  Type of a filter whose class should be loaded.
     *
     * @return  string|boolean  Class name on success or false otherwise.
     *
     * @since   4.0.0
     */
    public static function loadFilterClass($type)
    {
        return self::loadClass('filter', $type);
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
     * @since   1.7.0
     */
    protected static function loadClass($entity, $type)
    {
        // Check if there is a class in the registered namespaces
        foreach (self::addPrefix($entity) as $prefix) {
            // Treat underscores as namespace
            $name = Normalise::toSpaceSeparated($type);
            $name = str_ireplace(' ', '\\', ucwords($name));

            $subPrefix = '';

            if (strpos($name, '.')) {
                [$subPrefix, $name] = explode('.', $name);
                $subPrefix          = ucfirst($subPrefix) . '\\';
            }

            // Compile the classname
            $class = rtrim($prefix, '\\') . '\\' . $subPrefix . ucfirst($name) . ucfirst($entity);

            // Check if the class exists
            if (class_exists($class)) {
                return $class;
            }
        }

        $prefix = 'J';

        if (strpos($type, '.')) {
            [$prefix, $type] = explode('.', $type);
        }

        $class = StringHelper::ucfirst($prefix, '_') . 'Form' . StringHelper::ucfirst($entity, '_') . StringHelper::ucfirst($type, '_');

        if (class_exists($class)) {
            return $class;
        }

        // Get the field search path array.
        $paths = self::addPath($entity);

        // If the type is complex, add the base type to the paths.
        if ($pos = strpos($type, '_')) {
            // Add the complex type prefix to the paths.
            foreach ($paths as $value) {
                // Derive the new path.
                $path = $value . '/' . strtolower(substr($type, 0, $pos));

                // If the path does not exist, add it.
                if (!\in_array($path, $paths)) {
                    $paths[] = $path;
                }
            }

            // Break off the end of the complex type.
            $type = substr($type, $pos + 1);
        }

        // Try to find the class file.
        $type = strtolower($type) . '.php';

        foreach ($paths as $path) {
            $file = Path::find($path, $type);

            if (!$file) {
                continue;
            }

            require_once $file;

            if (class_exists($class)) {
                break;
            }
        }

        // Check for all if the class exists.
        return class_exists($class) ? $class : false;
    }

    /**
     * Method to add a path to the list of field include paths.
     *
     * @param   string|string[]  $new  A path or array of paths to add.
     *
     * @return  string[]  The list of paths that have been added.
     *
     * @since   1.7.0
     */
    public static function addFieldPath($new = null)
    {
        return self::addPath('field', $new);
    }

    /**
     * Method to add a path to the list of form include paths.
     *
     * @param   string|string[]  $new  A path or array of paths to add.
     *
     * @return  string[]  The list of paths that have been added.
     *
     * @since   1.7.0
     */
    public static function addFormPath($new = null)
    {
        return self::addPath('form', $new);
    }

    /**
     * Method to add a path to the list of rule include paths.
     *
     * @param   string|string[]  $new  A path or array of paths to add.
     *
     * @return  string[]  The list of paths that have been added.
     *
     * @since   1.7.0
     */
    public static function addRulePath($new = null)
    {
        return self::addPath('rule', $new);
    }

    /**
     * Method to add a path to the list of filter include paths.
     *
     * @param   string|string[]  $new  A path or array of paths to add.
     *
     * @return  string[]  The list of paths that have been added.
     *
     * @since   4.0.0
     */
    public static function addFilterPath($new = null)
    {
        return self::addPath('filter', $new);
    }

    /**
     * Method to add a path to the list of include paths for one of the form's entities.
     * Currently supported entities: field, rule and form. You are free to support your own in a subclass.
     *
     * @param   string            $entity  Form's entity name for which paths will be added.
     * @param   string|string[]   $new     A path or array of paths to add.
     *
     * @return  string[]  The list of paths that have been added.
     *
     * @since   1.7.0
     */
    protected static function addPath($entity, $new = null)
    {
        if (!isset(self::$paths[$entity])) {
            self::$paths[$entity] = [];
        }

        // Reference to an array with paths for current entity
        $paths = &self::$paths[$entity];

        // Force the new path(s) to an array.
        settype($new, 'array');

        // Add the new paths to the stack if not already there.
        foreach ($new as $path) {
            $path = trim($path);

            if (!\in_array($path, $paths)) {
                array_unshift($paths, $path);
            }
        }

        return $paths;
    }

    /**
     * Method to add a namespace prefix to the list of field lookups.
     *
     * @param   string|string[]  $new  A namespaces or array of namespaces to add.
     *
     * @return  string[]  The list of namespaces that have been added.
     *
     * @since   3.8.0
     */
    public static function addFieldPrefix($new = null)
    {
        return self::addPrefix('field', $new);
    }

    /**
     * Method to add a namespace to the list of form lookups.
     *
     * @param   string|string[]  $new  A namespace or array of namespaces to add.
     *
     * @return  string[]  The list of namespaces that have been added.
     *
     * @since   3.8.0
     */
    public static function addFormPrefix($new = null)
    {
        return self::addPrefix('form', $new);
    }

    /**
     * Method to add a namespace to the list of rule lookups.
     *
     * @param   string|string[]  $new  A namespace or array of namespaces to add.
     *
     * @return  string[]  The list of namespaces that have been added.
     *
     * @since   3.8.0
     */
    public static function addRulePrefix($new = null)
    {
        return self::addPrefix('rule', $new);
    }

    /**
     * Method to add a namespace to the list of filter lookups.
     *
     * @param   string|string[]  $new  A namespace or array of namespaces to add.
     *
     * @return  string[]  The list of namespaces that have been added.
     *
     * @since   4.0.0
     */
    public static function addFilterPrefix($new = null)
    {
        return self::addPrefix('filter', $new);
    }

    /**
     * Method to add a namespace to the list of namespaces for one of the form's entities.
     * Currently supported entities: field, rule and form. You are free to support your own in a subclass.
     *
     * @param   string           $entity  Form's entity name for which paths will be added.
     * @param   string|string[]  $new     A namespace or array of namespaces to add.
     *
     * @return  string[]  The list of namespaces that have been added.
     *
     * @since   3.8.0
     */
    protected static function addPrefix($entity, $new = null)
    {
        // Reference to an array with namespaces for current entity
        $prefixes = &self::$prefixes[$entity];

        // Add the default entity's search namespace if not set.
        if (empty($prefixes)) {
            $prefixes[] = __NAMESPACE__ . '\\' . ucfirst($entity);
        }

        // Force the new namespace(s) to an array.
        settype($new, 'array');

        // Add the new paths to the stack if not already there.
        foreach ($new as $prefix) {
            $prefix = trim($prefix);

            if (\in_array($prefix, $prefixes)) {
                continue;
            }

            array_unshift($prefixes, $prefix);
        }

        return $prefixes;
    }

    /**
     * Parse the show on conditions
     *
     * @param   string  $showOn       Show on conditions.
     * @param   string  $formControl  Form name.
     * @param   string  $group        The dot-separated form group path.
     *
     * @return  array[]   Array with show on conditions.
     *
     * @since   3.7.0
     */
    public static function parseShowOnConditions($showOn, $formControl = null, $group = null)
    {
        // Process the showon data.
        if (!$showOn) {
            return [];
        }

        $formPath = $formControl ?: '';

        if ($group) {
            $groups = explode('.', $group);

            // An empty formControl leads to invalid shown property
            // Use the 1st part of the group instead to avoid.
            if (empty($formPath) && isset($groups[0])) {
                $formPath = $groups[0];
                array_shift($groups);
            }

            foreach ($groups as $group) {
                $formPath .= '[' . $group . ']';
            }
        }

        $showOnData  = [];
        $showOnParts = preg_split('#(\[AND\]|\[OR\])#', $showOn, -1, PREG_SPLIT_DELIM_CAPTURE);
        $op          = '';

        foreach ($showOnParts as $showOnPart) {
            if (($showOnPart === '[AND]') || $showOnPart === '[OR]') {
                $op = trim($showOnPart, '[]');
                continue;
            }

            $compareEqual     = !str_contains($showOnPart, '!:');
            $showOnPartBlocks = explode(($compareEqual ? ':' : '!:'), $showOnPart, 2);

            $dotPos = strpos($showOnPartBlocks[0], '.');

            if ($dotPos === false) {
                $field = $formPath ? $formPath . '[' . $showOnPartBlocks[0] . ']' : $showOnPartBlocks[0];
            } else {
                if ($dotPos === 0) {
                    $fieldName = substr($showOnPartBlocks[0], 1);
                    $field     = $formControl ? $formControl . '[' . $fieldName . ']' : $fieldName;
                } else {
                    if ($formControl) {
                        $field = $formControl . ('[' . str_replace('.', '][', $showOnPartBlocks[0]) . ']');
                    } else {
                        $groupParts = explode('.', $showOnPartBlocks[0]);
                        $field      = array_shift($groupParts) . '[' . implode('][', $groupParts) . ']';
                    }
                }
            }

            $showOnData[] = [
                'field'  => $field,
                'values' => explode(',', $showOnPartBlocks[1]),
                'sign'   => $compareEqual === true ? '=' : '!=',
                'op'     => $op,
            ];

            if ($op !== '') {
                $op = '';
            }
        }

        return $showOnData;
    }
}
