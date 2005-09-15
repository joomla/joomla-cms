<?php
/**
* @version $Id: toolbar.installer.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Installer
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Installer Manager
 * @package Mambo
 * @subpackage Installer
 */
class installerToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function installerToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );

		// additional mappings
		$this->registerTask( 'new', 'newinst' );
	}

	function view() {
	    $element = mosGetParam( $_REQUEST, 'element', '' );
	    if ( $element == 'component' || $element == 'module' || $element == 'mambot' ) {
			installerToolbar::view2();
		} else {
			installerToolbar::view1();
		}
	}

	function view1() {
		global $_LANG;

		$title = ucfirst( mosGetParam( $_GET, 'element', '' ) );
		$title = $title .' '. $_LANG->_( 'Installer' );

		mosMenuBar::title( $title, 'install.png' );

		mosMenuBar::startTable();
		mosMenuBar::help( 'screen.installer' );
		mosMenuBar::endTable();
	}

	function view2() {
		global $_LANG;

		$title = ucfirst( mosGetParam( $_GET, 'element', '' ) );
		$title = $title .' '. $_LANG->_( 'Installer' );

		mosMenuBar::title( $title, 'install.png' );

		mosMenuBar::startTable();
		mosMenuBar::deleteList( '', 'remove', 'Uninstall' );
		mosMenuBar::help( 'screen.installer2' );
		mosMenuBar::endTable();
	}

	function newinst() {
		mosMenuBar::startTable();
		mosMenuBar::save();
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}
}

$tasker = new installerToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>