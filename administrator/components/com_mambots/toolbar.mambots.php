<?php
/**
* @version $Id: toolbar.mambots.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Mambots
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Mambot Manager
 * @package Mambo
 * @subpackage Mambots
 */
class mambotsToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function mambotsToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		$this->setAccessControl( 'com_mambots', 'manage' );

		// additional mappings
		$this->registerTask( 'editA', 'edit' );
		$this->registerTask( 'new', 'edit' );
		$this->registerTask( 'refreshFiles', 'editXML' );
		$this->registerTask( 'installOptions', 'install' );
		$this->registerTask( 'installUpload', 'install' );
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

	function install() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Mambot Installer' ), 'install.png' );

		mosMenuBar::startTable();
		mosMenuBar::help( 'screen.mambots.installer' );
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

		mosMenuBar::title( $_LANG->_( 'Mambot Manager' ), 'module.png', 'index2.php?option=com_mambots' );

		mosMenuBar::startTable();
		mosMenuBar::publishList();
		mosMenuBar::unpublishList();
		mosMenuBar::custom( 'packageOptions', 'downloads.png', 'downloads_f2.png', $_LANG->_( 'Package' ), true );
		mosMenuBar::deleteList();
		mosMenuBar::custom( 'editXML', 'xml.png', 'xml_f2.png', $_LANG->_( 'Edit XML' ), true );
		mosMenuBar::editList();
		mosMenuBar::addNew();
		mosMenuBar::help( 'screen.mambots' );
		mosMenuBar::endTable();
	}

	function edit( ){
		global $id, $database;
		global $_LANG;

		if ( !$id ) {
			$id = mosGetParam( $_REQUEST, 'cid', '' );
		}
		$text = ( $id ? $_LANG->_( 'Edit Mambot' ) : $_LANG->_( 'New Mambot' ) );

		$row 	= new mosMambot($database);
		// load the row from the db table
		$row->load( $id );
		$name = ( $row->element ? $row->element : 'edit' );

		mosMenuBar::title( $text, 'module.png' );

		mosMenuBar::startTable();
		mosMenuBar::save();
		mosMenuBar::apply();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', 'Close' );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::help( 'screen.mambots.'. $name );
		mosMenuBar::endTable();
	}
}

$tasker = new mambotsToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>