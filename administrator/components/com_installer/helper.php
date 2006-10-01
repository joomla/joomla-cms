<?php
/**
 * @version $Id: admin.menus.php 3504 2006-05-15 05:25:43Z eddieajau $
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * Extension Manager Helper
 * 
 * @static
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ExtensionManagerHelper
{
	/**
	 * Get HTML string for writable state of a folder
	 *
	 * @param string $folder
	 * @return string
	 */
	function Writable( $folder )
	{
		return is_writable( JPATH_ROOT.DS.$folder ) ? "<strong><span class=\"writable\">".JText::_( 'Writable' )."</span></strong>" : "<strong><span class=\"unwritable\">".JText::_( 'Unwritable' )."</span></strong>";
	}
}
?>