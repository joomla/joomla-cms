<?php
/**
* @version $Id: toolbar.menus.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Menus
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Menu Manager
 * @package Mambo
 * @subpackage Menu
 */
class menuToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function menuToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );

		// additional mappings
		$this->registerTask( 'new', 'newmenu' );
		$this->registerTask( 'copymenu', 'copy' );
		$this->registerTask( 'movemenu', 'move' );
	}

	function view() {
		global $_LANG;

		$menutype 	= mosGetParam( $_REQUEST, 'menutype', 'mainmenu' );

		mosMenuBar::title( $_LANG->_( 'Menus' ), 'menu.png', 'index2.php?option=com_menus&amp;menutype='. $menutype );

		mosMenuBar::startTable();
		mosMenuBar::publishList();
		mosMenuBar::unpublishList();
		mosMenuBar::customX( 'movemenu', 'move.png', 'move_f2.png', $_LANG->_( 'Move' ), true );
		mosMenuBar::customX( 'copymenu', 'copy.png', 'copy_f2.png', $_LANG->_( 'Copy' ), true );
		mosMenuBar::trash();
		mosMenuBar::editListX();
		mosMenuBar::addNewX();
		mosMenuBar::help( 'screen.menus' );
		mosMenuBar::endTable();
	}

	function edit( ){
		global $id, $database;
		global $_LANG;

		if ( !$id ) {
			$id = mosGetParam( $_REQUEST, 'cid', '' );
		}

		if ( !$id ) {
			$cid = mosGetParam( $_POST, 'cid', array(0) );
			$id = intval( $cid[0] );
		}
		$menutype	= mosGetParam( $_REQUEST, 'menutype', 'mainmenu' );
		$type		= mosGetParam( $_REQUEST, 'type', 'edit' );

		$text = ( $id ? $_LANG->_( 'Edit Menu Item' ) : $_LANG->_( 'New Menu Item' ) );

		$row 	= new mosMenu($database);
		// load the row from the db table
		$row->load( $id );
		$name = ( $row->type ? $row->type : $type );

		mosMenuBar::title( $text, 'menu.png' );

		mosMenuBar::startTable();
		if ( !$id ) {
			$link = 'index2.php?option=com_menus&menutype='. $menutype .'&task=new';
			mosMenuBar::link( $link );

		}
		mosMenuBar::save();
		mosMenuBar::apply();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', $_LANG->_( 'Close' ) );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::help( 'screen.menus.'. $name );
		mosMenuBar::endTable();
	}

	function newmenu() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'New Menu Item' ), 'menu.png' );

		mosMenuBar::startTable();
		mosMenuBar::customX( 'edit', 'next.png', 'next_f2.png', $_LANG->_( 'Next' ), false );
		mosMenuBar::cancel();
		mosMenuBar::help( 'screen.menus.new' );
		mosMenuBar::endTable();
	}

	function copy() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Copy Menu Items' ), 'menu.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'copymenusave', 'copy.png', 'copy_f2.png', $_LANG->_( 'Copy' ), false );
		mosMenuBar::cancel( 'cancelcopymenu' );
		mosMenuBar::help( 'screen.menus.copy' );
		mosMenuBar::endTable();
	}

	function move() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Move Menu Items' ), 'menu.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'movemenusave', 'move.png', 'move_f2.png', $_LANG->_( 'Move' ), false );
		mosMenuBar::cancel( 'cancelmovemenu' );
		mosMenuBar::help( 'screen.menus.move' );
		mosMenuBar::endTable();
	}

	function trashview() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Trashed Menu Items' ), 'trash.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom('trashrestoreconfirm','restore.png','restore_f2.png',$_LANG->_( 'Restore' ), true);
		mosMenuBar::custom('trashdeleteconfirm','delete.png','delete_f2.png',$_LANG->_( 'Delete' ), true);
		mosMenuBar::help( 'screen.menus.trash' );
		mosMenuBar::endTable();
	}

	function trashrestoreconfirm( ) {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Restore Menu Items' ), 'trash.png' );

		mosMenuBar::startTable();
		mosMenuBar::cancel( 'cancelrestore' );
		mosMenuBar::endTable();
	}

	function trashdeleteconfirm( ) {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Delete Menu Items' ), 'trash.png' );

		mosMenuBar::startTable();
		mosMenuBar::cancel( 'canceldelete' );
		mosMenuBar::endTable();
	}
}

$tasker = new menuToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>