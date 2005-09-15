<?php
/**
* @version $Id: toolbar.content.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Contents Manager
 * @package Mambo
 * @subpackage Content
 */
class contentToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function contentToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'viewcontent' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );

		// additional mappings
		$this->registerTask( 'showarchive', 'viewarchive' );
		$this->registerTask( 'movesect', 'move' );
		$this->registerTask( 'edit', 'edit' );
		$this->registerTask( 'editA', 'edit' );
		$this->registerTask( 'new', 'edit' );
	}

	function viewcontent() {
		mosMenuBar::title( 'Content Items Manager', 'addedit.png', 'index2.php?option=com_content&amp;sectionid=0' );
		mosMenuBar::startTable();
		mosMenuBar::popup('', 'previewcontent', 'preview.png', 'Preview', true);
		mosMenuBar::archiveList();
		mosMenuBar::publishList();
		mosMenuBar::unpublishList();
		mosMenuBar::custom( 'movesect', 'move.png', 'move_f2.png', 'Move' );
		mosMenuBar::custom( 'copy', 'copy.png', 'copy_f2.png', 'Copy' );
		mosMenuBar::trash();
		mosMenuBar::editList( 'editA' );
		mosMenuBar::addNew();
		mosMenuBar::help( 'screen.content' );
		mosMenuBar::endTable();
	}

	function viewarchive() {
		mosMenuBar::title( 'Archive Manager', 'addedit.png', 'index2.php?option=com_content&amp;task=showarchive&amp;sectionid=0' );
		mosMenuBar::startTable();
 		mosMenuBar::popup('', 'previewarchive', 'preview.png', 'Preview', true);
		mosMenuBar::unarchiveList();
		mosMenuBar::custom( 'remove', 'delete.png', 'delete_f2.png', 'Trash', false );
		mosMenuBar::help( 'screen.content.archive' );
		mosMenuBar::endTable();
	}

	function edit( ){
		global $id;

		if ( !$id ) {
			$id = mosGetParam( $_REQUEST, 'cid', '' );
		}
		$text = ( $id ? 'Edit Content Item' : 'New Content Item' );

		mosMenuBar::title( $text, 'addedit.png' );

		mosMenuBar::startTable();
		//mosMenuBar::preview( 'contentwindow', true );
		mosMenuBar::media_manager();
		mosMenuBar::save();
		mosMenuBar::apply();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', 'Close' );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::help( 'screen.content.edit' );
		mosMenuBar::endTable();
	}

	function copy( ){
		mosMenuBar::title( 'Copy Content Items', 'addedit.png' );
		mosMenuBar::startTable();
		mosMenuBar::custom( 'copysave', 'save.png', 'save_f2.png', 'Save', false );
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}

	function move( ){
		mosMenuBar::title( 'Move Content Items', 'addedit.png' );
		mosMenuBar::startTable();
		mosMenuBar::custom( 'movesectsave', 'save.png', 'save_f2.png', 'Save', false );
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}

	function trashview() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Trashed Content Items' ), 'trash.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom('trashrestoreconfirm','restore.png','restore_f2.png',$_LANG->_( 'Restore' ), true);
		mosMenuBar::custom('trashdeleteconfirm','delete.png','delete_f2.png',$_LANG->_( 'Delete' ), true);
		mosMenuBar::help( 'screen.content.trash' );
		mosMenuBar::endTable();
	}

	function trashrestoreconfirm( ) {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Restore Content Items' ), 'trash.png' );

		mosMenuBar::startTable();
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}

	function trashdeleteconfirm( ) {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Delete Content Items' ), 'trash.png' );

		mosMenuBar::startTable();
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}
}

$tasker = new contentToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>