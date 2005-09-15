<?php
/**
* @version $Id: toolbar.sections.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Sections
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Sections Manager
 * @package Mambo
 * @subpackage Sections
 */
class sectionsToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function sectionsToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );

		// additional mappings
		$this->registerTask( 'copyselect', 'copy' );
		$this->registerTask( 'edit', 'edit' );
		$this->registerTask( 'editA', 'edit' );
		$this->registerTask( 'new', 'edit' );
	}

	function view() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Section Manager' ), 'sections.png', 'index2.php?option=com_sections&amp;scope=content' );

		mosMenuBar::startTable();
		mosMenuBar::publishList();
		mosMenuBar::unpublishList();
		mosMenuBar::custom( 'copyselect', 'copy.png', 'copy_f2.png', 'Copy', true );
		mosMenuBar::deleteList();
		mosMenuBar::editList();
		mosMenuBar::addNew();
		mosMenuBar::help( 'screen.sections' );
		mosMenuBar::endTable();
	}

	function edit( ){
		global $_LANG;
		global $id;

		if ( !$id ) {
			$id = mosGetParam( $_REQUEST, 'cid', '' );
		}
		$text = ( $id ? $_LANG->_( 'Edit Section' ) : $_LANG->_( 'New Section' ) );

		mosMenuBar::title( $text, 'sections.png' );

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
		mosMenuBar::help( 'screen.sections.edit' );
		mosMenuBar::endTable();
	}

	function copy( ){
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Copy Sections' ), 'sections.png' );

		mosMenuBar::startTable();
		mosMenuBar::save( 'copysave' );
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}
}

$tasker = new sectionsToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>