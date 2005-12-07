<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
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
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Install new Extension' ), 'install.png' );
		mosMenuBar::help( 'screen.installer' );
		mosMenuBar::endTable();
	}

	function _DEFAULT2()	{

		$type = mosGetParam($_REQUEST, 'element');

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Installed '.$type.'s'), 'install.png' );
		mosMenuBar::deleteList( '', 'remove', JText::_( 'Uninstall' ) );
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.installer2' );
		mosMenuBar::endTable();
	}

	function _NEW()	{
		mosMenuBar::startTable();
		mosMenuBar::save();
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}
}
?>