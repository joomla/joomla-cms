<?php
/**
* @version $Id: component.php 1598 2005-12-31 14:40:48Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * @package  Joomla
 */
class JComponentHelper
{
	/**
	 * Get the component info
	 * @param string The component option
	 * @return object A JComponent object
	 */
	function &getInfo( $option )
	{
		static $instances;

		if (!isset( $instances[$option] ))
		{
			global $mainframe;

			jimport( 'joomla.database.table.component' );

			$database = &$mainframe->getDBO();

			$row = new JTableComponent( $database );
			$row->loadByOption( $option );

			if (!is_object($row))
			{
				$row = new stdClass();
				$row->enabled	= false;
				$row->params	= null;
			}
			$instances[$option] = &$row;
		}
		return $instances[$option];
	}

	/**
	 * Checks if the component is enabled
	 * @param string The component option
	 * @return boolean
	 */
	function isEnabled( $option )
	{
		// TODO: In future versions this should be ACL controlled
		$enabledList = array(
			'com_login',
			'com_content',
			'com_media',
			'com_frontpage',
			'com_user',
			'com_wrapper',
			'com_registration'
		);
		$component = &JComponentHelper::getInfo( $option );
		return ($component->enabled | in_array($option, $enabledList));
	}

	/**
	 * Gets the parameter object for the component
	 * @param string The component option
	 * @return object A JParameter object
	 */
	function &getParams( $option )
	{
		static $instances;
		if (!isset( $instances[$option] ))
		{
			$component = &JComponentHelper::getInfo( $option );
			$instances[$option] = new JParameter($row->params);
		}
		return $instances[$option];
	}
}