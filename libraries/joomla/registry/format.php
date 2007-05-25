<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Registry
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Abstract Format for JRegistry
 *
 * @abstract
 * @author 		Samuel Moffatt <pasamio@gmail.com>
 * @package 	Joomla.Framework
 * @subpackage	Registry
 * @since		1.5
 */
class JRegistryFormat extends JObject
{
	/**
	 * Returns a reference to a Format object, only creating it
	 * if it doesn't already exist.
	 *
	 * @static
	 * @param	string	$format	The format to load
	 * @return	object	Registry format handler
	 * @since	1.5
	 */
	function &getInstance($format)
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$format])) {
			$adapter = 'JRegistryFormat'.$format;
			$lformat = strtolower(JInputFilter::clean($format, 'word'));

			if (file_exists(JPATH_LIBRARIES.DS.'joomla'.DS.'registry'.DS.'format'.DS.$lformat.'.php')) {
				jimport('joomla.registry.format.'.$lformat);
			} else {
				JError::raiseError(500,JText::_('Unable to load format'));
			}

			$instances[$format] = new $adapter ();
		}
		return $instances[$format];
	}

	/**
	 * Converts an XML formatted string into an object
	 *
	 * @abstract
	 * @access	public
	 * @param	string	$data	Formatted string
	 * @return	object	Data Object
	 * @since	1.5
	 */
	function stringToObject( $data, $namespace='' ) {
		return true;
	}

	/**
	 * Converts an object into a formatted string
	 *
	 * @abstract
	 * @access	public
	 * @param	object	$object	Data Source Object
	 * @return	string	Formatted string
	 * @since	1.5
	 */
	function objectToString( &$object ) {

	}
}