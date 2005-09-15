<?php
/**
* @version $Id: toolbar.users.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Users
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Users Manager
 * @package Mambo
 * @subpackage Users
 */
class usersToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function usersToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );

		// additional mappings
		$this->registerTask( 'edit', 'edit' );
		$this->registerTask( 'editA', 'edit' );
		$this->registerTask( 'new', 'edit' );
	}

	function view() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'User Manager' ), 'user.png', 'index2.php?option=com_users' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'logout', 'cancel.png', 'cancel_f2.png', $_LANG->_( 'Logout' ) );
		mosMenuBar::deleteList();
		mosMenuBar::editList();
		mosMenuBar::addNew();
		mosMenuBar::help( 'screen.users' );
		mosMenuBar::endTable();
	}

	function edit( ){
		global $_LANG;
		global $id;

		if ( !$id ) {
			$id = mosGetParam( $_REQUEST, 'cid', '' );
		}
		$text = ( $id ? $_LANG->_( 'Edit User' ) : $_LANG->_( 'New User' ) );

		mosMenuBar::title( $text, 'user.png' );

		mosMenuBar::startTable();
		mosMenuBar::save();
		mosMenuBar::apply();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', $_LANG->_( 'Close' ) );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::help( 'screen.users.edit' );
		mosMenuBar::endTable();
	}

	function masscreate() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Mass Create Users' ), 'user.png', 'index2.php?option=com_users&task=masscreate' );

		mosMenuBar::startTable();
		mosMenuBar::save( 'savemasscreate' );
		mosMenuBar::cancel();
		mosMenuBar::help( 'screen.users.masscreate' );
		mosMenuBar::endTable();
	}
}

$tasker = new usersToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>