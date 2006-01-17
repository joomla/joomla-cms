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
		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Joomla Extension Manager' ), 'install.png' );
		JMenuBar::help( 'screen.installer' );
		JMenuBar::endTable();
	}

	function _DEFAULT2()	{

		$type = mosGetParam($_REQUEST, 'extension');

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Joomla Extension Manager'), 'install.png' );
		JMenuBar::deleteList( '', 'remove', JText::_( 'Uninstall' ) );
		JMenuBar::spacer();
		JMenuBar::help( 'screen.installer2' );
		JMenuBar::endTable();
	}

	function _NEW()	{
		JMenuBar::startTable();
		JMenuBar::save();
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::endTable();
	}
}
?>