<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Menus component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class MenusHelper
{
	/**
	 * Defines the valid request variables for the reverse lookup.
	 */
	protected static $_filter = array('option', 'view', 'layout');

	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('Menus_Submenu_Items'),
			'index.php?option=com_menus&view=items',
			$vName == 'items'
		);
		JSubMenuHelper::addEntry(
			JText::_('Menus_Submenu_Menus'),
			'index.php?option=com_menus&view=menus',
			$vName == 'menus'
		);
	}

	/**
	 * Gets a standard form of a link for lookups.
	 *
	 * @param	mixed	A link string or array of request variables.
	 *
	 * @return	mixed	A link in standard option-view-layout form, or false if the supplied response is invalid.
	 */
	public static function getLinkKey($request)
	{
		if (empty($request)) {
			return false;
		}

		// Check if the link is in the form of index.php?...
		if (is_string($request))
		{
			$args = array();
			if (strpos($request, 'index.php') === 0) {
				parse_str(parse_url(htmlspecialchars_decode($request), PHP_URL_QUERY), $args);
			}
			else {
				parse_str($request, $args);
			}
			$request = $args;
		}

		// Only take the option, view and layout parts.
		foreach ($request as $name => $value)
		{
			if (!in_array($name, self::$_filter))
			{
				// Remove the variables we want to ignore.
				unset($request[$name]);
			}
		}

		ksort($request);

		return 'index.php?'.http_build_query($request);
	}
	
	/**
	 * Get the menu list for create a menu module
	 *
	 * @access 		public
	 * @return		array  	The menu array list
	 * @since		1.6
	 */
	public static function getMenuTypes() 
	{
		$db = JFactory::getDbo();
		$db->setQuery('SELECT a.menutype FROM #__menu_types AS a');
		return $db->loadResultArray();
	}

}