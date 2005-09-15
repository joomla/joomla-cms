<?php
/**
* @version $Id: toolbar.modules.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Modules
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Modules Manager
 * @package Mambo
 * @subpackage Modules
 */
class modulesToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function modulesToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );

		// additional mappings
		$this->registerTask( 'edit', 'edit' );
		$this->registerTask( 'editA', 'edit' );
		$this->registerTask( 'new', 'selectnew' );

		$this->registerTask( 'uninstall', 'install' );
		$this->registerTask( 'refreshFiles', 'editXML' );
	}

	function createOptions() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Create Module' ), 'install.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'create', 'downloads.png', 'downloads_f2.png', $_LANG->_( 'Create' ), false );
		mosMenuBar::help( 'screen.modules.create' );
		mosMenuBar::endTable();
	}

	function editXML() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Edit XML File' ), 'module.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'refreshFiles', 'reload.png', 'reload_f2.png', $_LANG->_( 'Refresh Files' ), false );
		mosMenuBar::save( 'saveXML' );
		mosMenuBar::apply( 'applyXML' );
		mosMenuBar::cancel( 'cancel', 'Close' );
		mosMenuBar::endTable();
	}

	function manage() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Module Manager' ), 'install.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'editXML', 'xml.png', 'xml_f2.png', $_LANG->_( 'Edit XML' ), true );
		mosMenuBar::custom( 'packageOptions', 'downloads.png', 'downloads_f2.png', $_LANG->_( 'Package' ), true );
		mosMenuBar::custom( 'uninstall', 'delete.png', 'delete_f2.png', 'Uninstall', true );
		mosMenuBar::help( 'screen.modules.manage' );
		mosMenuBar::endTable();
	}

	function installOptions() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Module Installer' ), 'install.png' );

		mosMenuBar::startTable();
		mosMenuBar::help( 'screen.modules.installer' );
		mosMenuBar::endTable();
	}

	function install() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Module Installer' ), 'install.png' );

		mosMenuBar::startTable();
		mosMenuBar::help( 'screen.modules.installer' );
		mosMenuBar::endTable();
	}

	function packageOptions() {
		global $_LANG;

		mosMenuBar::startTable();
		mosMenuBar::custom( 'package', 'downloads.png', 'downloads_f2.png', $_LANG->_( 'Make Package' ), false );
		mosMenuBar::help( 'screen.package' );
		mosMenuBar::endTable();
	}

	function listFiles() {
		global $_LANG;

		mosMenuBar::startTable();
		mosMenuBar::deleteList( '', 'deleteFile' );
		mosMenuBar::help( 'screen.listfiles' );
		mosMenuBar::endTable();
	}

	function view() {
		global $_LANG;

		$client = mosGetParam( $_REQUEST, 'client', '' );

		mosMenuBar::title( $_LANG->_( 'Module Manager' ), 'module.png', 'index2.php?option=com_modules&amp;client='. $client );

		mosMenuBar::startTable();
		mosMenuBar::publishList();
		mosMenuBar::unpublishList();
		mosMenuBar::custom( 'copy', 'copy.png', 'copy_f2.png', 'Copy', true );
		mosMenuBar::deleteList();
		mosMenuBar::editList();
		mosMenuBar::addNew();
		mosMenuBar::help( 'screen.modules' );
		mosMenuBar::endTable();
	}

	function edit( ){
		global $id, $database;
		global $_LANG;

		$created 	= intval( mosGetParam( $_REQUEST, 'created', 0 ) );
		$client 	= mosGetParam( $_REQUEST, 'client', '' );
		$module 	= mosGetParam( $_REQUEST, 'module', '' );

		if ( !$id ) {
			$cid = mosGetParam( $_POST, 'cid', array(0) );
			$id = intval( $cid[0] );
		}
		$text = ( $id ? $_LANG->_( 'Edit Module' ) : $_LANG->_( 'New Module' ) );

		$row 	= new mosModule($database);
		// load the row from the db table
		$row->load( $id );
		$name = ( $row->module ? $row->module : $module );

		mosMenuBar::title( $text, 'module.png' );

		mosMenuBar::startTable();
		if ( $created ) {
			mosMenuBar::link( 'index2.php?option=com_modules&task=selectnew&client='. $client );
		}
		// TODO
		//if ( ( $module == 'custom' || !$module ) && $id ) {
		//	mosMenuBar::popup('', 'previewmodule', 'preview.png', 'Preview', false);
		//}
		mosMenuBar::save();
		mosMenuBar::apply();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', 'Close' );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::help( 'screen.modules.'. $name );
		mosMenuBar::endTable();
	}

	function selectnew( ) {
 		global $_LANG;

 		mosMenuBar::title( $_LANG->_( 'New Module' ), 'module.png' );

 		mosMenuBar::startTable();
 		mosMenuBar::customX( 'edit', 'next.png', 'next_f2.png', 'Next', true );
 		mosMenuBar::cancel();
		mosMenuBar::help( 'screen.modules.new' );
 		mosMenuBar::endTable();
	}
}

$tasker = new modulesToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>