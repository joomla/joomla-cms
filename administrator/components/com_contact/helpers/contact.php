<?php
/**
 * @version
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Contact component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class ContactHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('Contact_Submenu_Contacts'),
			'index.php?option=com_contact&view=contacts',
			$vName == 'contacts'
		);
		JSubMenuHelper::addEntry(
			JText::_('Contact_Submenu_Categories'),
			'index.php?option=com_categories&extension=com_contact',
			$vName == 'categories'
		);
	}
}
