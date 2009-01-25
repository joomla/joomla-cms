<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

/**
 * Extension Manager Helper
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerHelper
{
	/**
	 * Get HTML string for writable state of a folder
	 *
	 * @param string $folder
	 * @return string
	 */
	function writable( $folder )
	{
		return is_writable( JPATH_ROOT.DS.$folder )
			? '<strong><span class="writable">'.JText::_( 'Writable' ).'</span></strong>'
			: '<strong><span class="unwritable">'.JText::_( 'Unwritable' ).'</span></strong>';
	}
}