<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Languages component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.6
 */
class LanguagesHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('Langs_Submenu_Installed'),
			'index.php?option=com_languages&view=installed',
			$vName == 'installed'
		);
		JSubMenuHelper::addEntry(
			JText::_('Langs_Submenu_Content'),
			'index.php?option=com_languages&view=languages',
			$vName == 'languages'
		);
	}
}
