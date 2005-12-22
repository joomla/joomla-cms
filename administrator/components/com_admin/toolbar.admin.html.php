<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Admin
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Admin
*/
class TOOLBAR_admin {
	function _SYSINFO() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Information' ), 'systeminfo.png' );
		JMenuBar::help( 'screen.system.info' );
		JMenuBar::endTable();
	}

	function _CPANEL() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Control Panel' ), 'cpanel.png' );
		JMenuBar::help( 'screen.cpanel' );
		JMenuBar::endTable();
	}

	function _HELP() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Help' ), 'help_header.png' );
		JMenuBar::spacer();
		JMenuBar::endTable();
	}

	function _PREVIEW() {

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Preview' ) );
		JMenuBar::spacer();
		JMenuBar::endTable();
	}

	function _DEFAULT() {
		JMenuBar::startTable();
		JMenuBar::spacer();
		JMenuBar::endTable();
	}
}
?>