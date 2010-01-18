<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Registry
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Abstract Format for JRegistry
 *
 * @abstract
 * @package 	Joomla.Framework
 * @subpackage	Registry
 * @since		1.5
 */
abstract class JRegistryFormat extends JObject
{
	/**
	 * Returns a Format object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param	string	$format	The format to load
	 * @return	object	Registry format handler
	 * @since	1.5
	 */
	public static function getInstance($format)
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		$format = strtolower(JFilterInput::getInstance()->clean($format, 'word'));
		if (empty ($instances[$format]))
		{
			$class = 'JRegistryFormat'.$format;
			if (!class_exists($class))
			{
				$path    = dirname(__FILE__).DS.'format'.DS.$format.'.php';
				if (file_exists($path)) {
					require_once $path;
				} else {
					JError::raiseError(500,JText::_('Unable to load format class'));
				}
			}

			$instances[$format] = new $class ();
		}
		return $instances[$format];
	}

	/**
	 * Converts an XML formatted string into an object
	 *
	 * @param	string	$data	Formatted string
	 * @return	object	Data Object
	 * @since	1.5
	 */
	abstract public function stringToObject($data, $namespace='');

	/**
	 * Converts an object into a formatted string
	 *
	 * @param	object	$object	Data Source Object
	 * @return	string	Formatted string
	 * @since	1.5
	 */
	abstract public function objectToString(&$object, $params);
}