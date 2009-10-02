<?php
/**
 * @version		$Id: newsfeeds.php
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Weblinks helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeedss
 * @since		1.6
 */
class NewsfeedsHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('Newsfeeds_Submenu_Newsfeeds'),
			'index.php?option=com_newsfeeds&view=newsfeeds',
			$vName == 'newsfeeds'
		);
		JSubMenuHelper::addEntry(
			JText::_('Newsfeeds_Submenu_Categories'),
			'index.php?option=com_categories&extension=com_newsfeeds',
			$vName == 'categories'
		);
	}
}