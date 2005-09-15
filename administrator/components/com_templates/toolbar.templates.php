<?php
/**
* @version $Id: toolbar.templates.php 137 2005-09-12 10:21:17Z eddieajau $
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
 * Toolbar for Template Manager
 * @package Mambo
 * @subpackage Templates
 */
class templatesToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function templatesToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		$this->setAccessControl( 'com_templates', 'manage' );

		// additional mappings
		$this->registerTask( 'applyHTML', 'editHTML' );
		$this->registerTask( 'applyCSS', 'editCSS' );
		$this->registerTask( 'preview2', 'preview' );
		$this->registerTask( 'refreshFiles', 'editXML' );
		$this->registerTask( 'installOptions', 'install' );
		$this->registerTask( 'installUpload', 'install' );
	}

	function view() {
		global $mainframe, $_LANG;

		$client = mosGetParam( $_REQUEST, 'client', 0 );
		$client = $mainframe->getClientID( $client );

		mosMenuBar::title( $_LANG->_( 'Template Manager' ), 'templatemanager.png', 'index2.php?option=com_templates&amp;client='. $client );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'packageOptions', 'downloads.png', 'downloads_f2.png', $_LANG->_( 'Package' ), true );
		if ($client == 1) {
			mosMenuBar::custom( 'publish', 'publish.png', 'publish_f2.png', $_LANG->_( 'Default' ), true );
		} else {
			mosMenuBar::custom( 'assign', 'publish.png', 'publish_f2.png', $_LANG->_( 'Assign' ), true );
			mosMenuBar::custom( 'default', 'publish.png', 'publish_f2.png', $_LANG->_( 'Default' ), true );
		}
		mosMenuBar::custom( 'editHTML', 'html.png', 'html_f2.png', $_LANG->_( 'Edit HTML' ), true );
		mosMenuBar::custom( 'editCSS', 'css.png', 'css_f2.png', $_LANG->_( 'Edit CSS' ), true );
		mosMenuBar::custom( 'editXML', 'xml.png', 'xml_f2.png', $_LANG->_( 'Edit XML' ), true );
		mosMenuBar::deleteList();
		mosMenuBar::help( 'screen.templates' );
		mosMenuBar::endTable();
	}

	function editHTML() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Edit HTML Source' ), 'templatemanager.png' );

		mosMenuBar::startTable();
		mosMenuBar::save( 'saveHTML' );
		mosMenuBar::apply( 'applyHTML' );
		mosMenuBar::cancel( 'cancel', 'Close' );
		mosMenuBar::endTable();
	}

	function editCSS() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Edit CSS Source' ), 'templatemanager.png' );

		mosMenuBar::startTable();
		mosMenuBar::save( 'saveCSS' );
		mosMenuBar::apply( 'applyCSS' );
		mosMenuBar::cancel( 'cancel', 'Close' );
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

	function assign() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Assign Templates to Menu Item(s)' ), 'templatemanager.png' );

		mosMenuBar::startTable();
		mosMenuBar::save( 'saveAssign', 'Save' );
		mosMenuBar::cancel();
		mosMenuBar::help( 'screen.templates.assign' );
		mosMenuBar::endTable();
	}

	function positions() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Module Positions' ), 'templatemanager.png' );

		mosMenuBar::startTable();
		mosMenuBar::save( 'savePositions' );
		mosMenuBar::cancel( 'cancel', 'Close' );
		mosMenuBar::help( 'screen.templates.positions' );
		mosMenuBar::endTable();
	}

	function install() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Template Installer' ), 'install.png' );

		mosMenuBar::startTable();
		mosMenuBar::cancel();
		mosMenuBar::help( 'screen.templates.installer' );
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

		mosMenuBar::startTable();
		mosMenuBar::deleteList( '', 'deleteFile' );
		mosMenuBar::cancel( 'cancel', 'Close' );
		mosMenuBar::help( 'screen.listfiles' );
		mosMenuBar::endTable();
	}

	function preview() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Template Preview' ), 'templatemanager.png' );

		mosMenuBar::startTable();
		mosMenuBar::cancel( 'cancel', 'Close' );
		mosMenuBar::help( 'screen.templates.preview' );
		mosMenuBar::endTable();
	}
}

$tasker = new templatesToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>