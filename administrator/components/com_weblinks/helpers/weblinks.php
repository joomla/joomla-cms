<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Weblinks helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @since		1.6
 */
class WeblinksHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('Weblinks_Submenu_Weblinks'),
			'index.php?option=com_weblinks&view=weblinks',
			$vName == 'weblinks'
		);
		JSubMenuHelper::addEntry(
			JText::_('Weblinks_Submenu_Categories'),
			'index.php?option=com_categories&extension=com_weblinks',
			$vName == 'categories'
		);
	}
}