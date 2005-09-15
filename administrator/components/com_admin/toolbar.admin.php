<?php
/**
* @version $Id: toolbar.admin.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Admin
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for admin Manager
 * @package Mambo
 * @subpackage admin
 */
class adminToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function adminToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );
	}

	function view() {
	    if ( $GLOBALS['task'] ) {
			adminToolbar::help();
		} else {
			adminToolbar::cpanel();
		}
	}

	function sysinfo( ){
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'System Information' ), 'systeminfo.png', 'index2.php?option=com_admin&amp;task=sysinfo' );

		mosMenuBar::startTable();
		mosMenuBar::help( 'screen.system.info' );
		mosMenuBar::endTable();
	}

	function cpanel( ) {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Control Panel' ), 'cpanel.png', 'index2.php' );

		mosMenuBar::startTable();
		mosMenuBar::help( 'screen.cpanel' );
		mosMenuBar::endTable();
	}

	function help( ) {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Help' ), 'support.png', 'index2.php?option=com_admin&amp;task=help' );

		mosMenuBar::startTable();
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}
}

$tasker = new adminToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>