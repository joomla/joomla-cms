<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Extension Manager Helper
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.5
 */
class InstallerHelper
{
	/**
	 * Get HTML string for writable state of a folder
	 *
	 * @param	string $folder
	 *
	 * @return	string
	 */
	static function writable($folder)
	{
		return is_writable(JPATH_ROOT.DS.$folder)
			? '<strong><span class="writable">'.JText::_('Writable').'</span></strong>'
			: '<strong><span class="unwritable">'.JText::_('Unwritable').'</span></strong>';
	}
}