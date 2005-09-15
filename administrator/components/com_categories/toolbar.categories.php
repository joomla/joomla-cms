<?php
/**
* @version $Id: toolbar.categories.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Categories
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Categories Manager
 * @package Mambo
 * @subpackage Categories
 */
class categoriesToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function categoriesToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );

		// additional mappings
		$this->registerTask( 'edit', 'edit' );
		$this->registerTask( 'editA', 'edit' );
		$this->registerTask( 'new', 'edit' );
		$this->registerTask( 'copyselect', 'copy' );
		$this->registerTask( 'moveselect', 'move' );
	}

	function view() {
		global $_LANG;

		$section = mosGetParam( $_REQUEST, 'section', 'content' );

		mosMenuBar::title( $_LANG->_( 'Category Manager' ), 'categories.png', 'index2.php?option=com_categories&section='. $section );

		mosMenuBar::startTable();
		mosMenuBar::publishList();
		mosMenuBar::unpublishList();
		if ( $section == 'content' || ( $section > 0 ) ) {
			mosMenuBar::custom( 'moveselect', 'move.png', 'move_f2.png', 'Move', true );
			mosMenuBar::custom( 'copyselect', 'copy.png', 'copy_f2.png', 'Copy', true );
		}
		mosMenuBar::deleteList();
		mosMenuBar::editList();
		mosMenuBar::addNew();
		mosMenuBar::help( 'screen.categories' );
		mosMenuBar::endTable();
	}

	function edit( ){
		global $_LANG;
		global $id;

		if ( !$id ) {
			$id = mosGetParam( $_REQUEST, 'cid', '' );
		}
		$text = ( $id ? $_LANG->_( 'Edit Category' ) : $_LANG->_( 'New Category' ) );

		mosMenuBar::title( $text, 'categories.png' );

		mosMenuBar::startTable();
		mosMenuBar::media_manager();
		mosMenuBar::save();
		mosMenuBar::apply();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', 'Close' );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::help( 'screen.categories.edit' );
		mosMenuBar::endTable();
	}

	function copy( ){
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Copy Categories' ), 'categories.png' );

		mosMenuBar::startTable();
		mosMenuBar::save( 'copysave' );
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}

	function move( ){
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Move Categories' ), 'categories.png' );

		mosMenuBar::startTable();
		mosMenuBar::save( 'movesave' );
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}
}

$tasker = new categoriesToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>