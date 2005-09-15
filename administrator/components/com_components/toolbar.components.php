<?php
/**
* @version $Id: toolbar.components.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Templates
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Component Manager
 * @package Mambo
 * @subpackage Templates
 */
class componentsToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function componentsToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		$this->setAccessControl( 'com_templates', 'manage' );

		// additional mappings
		$this->registerTask( 'refreshFiles', 'editXML' );
		$this->registerTask( 'addUninstallQuery', 'editXML' );
		$this->registerTask( 'refreshMenus', 'editXML' );
		$this->registerTask( 'addTable', 'editXML' );
		$this->registerTask( 'refreshTables', 'editXML' );

		$this->registerTask( 'createOptions', 'create' );
		$this->registerTask( 'installOptions', 'install' );
		$this->registerTask( 'installUpload', 'install' );
	}

	function view() {
		global $mainframe, $_LANG;

		$client = mosGetParam( $_REQUEST, 'client', 0 );
		$client = $mainframe->getClientID( $client );

		mosMenuBar::title( $_LANG->_( 'Component Manager' ), 'module.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'packageOptions', 'downloads.png', 'downloads_f2.png', $_LANG->_( 'Package' ), true );
		mosMenuBar::custom( 'editXML', 'xml.png', 'xml_f2.png', $_LANG->_( 'Edit XML' ), true );
		mosMenuBar::deleteList();
		mosMenuBar::help( 'screen.components' );
		mosMenuBar::endTable();
	}

	function editXML() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Edit XML File' ), 'templatemanager.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'refreshFiles', 'reload.png', 'reload_f2.png', $_LANG->_( 'Refresh Files' ), false );
		mosMenuBar::save( 'saveXML' );
		mosMenuBar::apply( 'applyXML' );
		mosMenuBar::cancel( 'cancel', 'Close' );
		mosMenuBar::endTable();
	}

	function create() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Create Component' ), 'install.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'create', 'downloads.png', 'downloads_f2.png', $_LANG->_( 'Create' ), false );
		mosMenuBar::help( 'screen.components.create' );
		mosMenuBar::endTable();
	}

	function install() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Component Installer' ), 'install.png' );

		mosMenuBar::startTable();
		mosMenuBar::help( 'screen.components.installer' );
		mosMenuBar::endTable();
	}

	function packageOptions() {
		global $_LANG;

		mosMenuBar::startTable();
		mosMenuBar::custom( 'package', 'downloads.png', 'downloads_f2.png', $_LANG->_( 'Make Package' ), false );
		mosMenuBar::cancel();
		mosMenuBar::help( 'screen.package' );
		mosMenuBar::endTable();
	}

	function listFiles() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Packages' ), 'install.png' );

		mosMenuBar::startTable();
		mosMenuBar::deleteList( '', 'deleteFile' );
		mosMenuBar::cancel( 'cancel', 'Close' );
		mosMenuBar::help( 'screen.listfiles' );
		mosMenuBar::endTable();
	}

	function listcomponents( ) {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Components' ), 'module.png', 'index2.php?option=com_admin&task=listcomponents' );
	}
}

$tasker = new componentsToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>
