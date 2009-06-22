<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Content component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class ContentHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('Content_Submenu_Articles'),
			'index.php?option=com_content&view=articles',
			$vName == 'articles'
		);
		JSubMenuHelper::addEntry(
			JText::_('Content_Submenu_Categories'),
			'index.php?option=com_categories&extension=com_content',
			$vName == 'categories');
		JSubMenuHelper::addEntry(
			JText::_('Content_Submenu_Featured'),
			'index.php?option=com_content&view=featured',
			$vName == 'featured'
		);
	}
}