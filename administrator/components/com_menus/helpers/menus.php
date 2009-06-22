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
}