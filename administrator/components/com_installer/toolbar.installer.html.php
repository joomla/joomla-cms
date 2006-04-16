<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* @package Joomla
* @subpackage Installer
*/
class TOOLBAR_installer
{
	function _DEFAULT()	{
		JMenuBar::title( JText::_( 'Extension Manager' ), 'install.png' );
		JMenuBar::help( 'screen.installer' );
	}

	function _DEFAULT2()	{

		$type = JRequest::getVar( 'extension');

		JMenuBar::title( JText::_( 'Extension Manager'), 'install.png' );
		JMenuBar::deleteList( '', 'remove', 'Uninstall' );
		JMenuBar::help( 'screen.installer2' );
	}

	function _NEW()	{
		JMenuBar::save();
		JMenuBar::cancel();
	}
}
?>