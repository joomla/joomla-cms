<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_submenu
 *
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 * @since       1.6
 */
abstract class modSubmenuHelper
{
	/**
	 * Get the member items of the submenu.
	 *
	 * @return	mixed	An arry of menu items, or false on error.
	 */
	public static function getItems()
	{
		// Initialise variables.
		$menu = JToolbar::getInstance('submenu');

		$list = $menu->getItems();

		if (!is_array($list) || !count($list)) {
			return false;
		}

		return $list;
	}
}
