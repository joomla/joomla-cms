<?php
/**
* @version $Id: toolbar.weblinks.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Weblinks
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Weblinks Manager
 * @package Mambo
 * @subpackage Weblinks
 */
class weblinksToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function weblinksToolbar() {
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

		mosMenuBar::title( $_LANG->_( 'Web Links Manager' ), 'webworld_f2', 'index2.php?option=com_weblinks' );

		mosMenuBar::startTable();
		mosMenuBar::publishList();
		mosMenuBar::unpublishList();
		mosMenuBar::deleteList();
		mosMenuBar::editList();
		mosMenuBar::addNew();
		mosMenuBar::help( 'screen.weblink' );
		mosMenuBar::endTable();
	}

	function edit( ){
		global $_LANG;
		global $id;

		if ( !$id ) {
			$id = mosGetParam( $_REQUEST, 'cid', '' );
		}
		$text = ( $id ? $_LANG->_( 'Edit Web Link' ) : $_LANG->_( 'New Web Link' ) );

		mosMenuBar::title( $text, 'webworld_f2' );

		mosMenuBar::startTable();
		mosMenuBar::save();
		mosMenuBar::apply();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', 'Close' );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::help( 'screen.weblink.edit' );
		mosMenuBar::endTable();
	}
}

$tasker = new weblinksToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>