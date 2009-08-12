<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
	public static function addSubmenu($client_id)
	{
		JSubMenuHelper::addEntry(
			JText::_('Languages_Site'),
			'#" onclick="javascript:document.adminForm.client.value=\'0\';submitbutton(\'\');',
			$client_id == 0);
		JSubMenuHelper::addEntry(
			JText::_('Languages_Administrator'),
			'#" onclick="javascript:document.adminForm.client.value=\'1\';submitbutton(\'\');',
			$client_id == 1);
	}
}
