<?php
/**
* @version $Id: toolbar.newsfeeds.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Newsfeeds Manager
 * @package Mambo
 * @subpackage Newsfeeds
 */
class newsfeedsToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function newsfeedsToolbar() {
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

		mosMenuBar::title( $_LANG->_( 'News Feed Manager' ), 'asterisk.png', 'index2.php?option=com_newsfeeds' );

		mosMenuBar::startTable();
		mosMenuBar::publishList();
		mosMenuBar::unpublishList();
		mosMenuBar::deleteList();
		mosMenuBar::editList();
		mosMenuBar::addNew();
		mosMenuBar::help( 'screen.newsfeeds' );
		mosMenuBar::endTable();
	}

	function edit( ){
		global $_LANG;
		global $id;

		if ( !$id ) {
			$id = mosGetParam( $_REQUEST, 'cid', '' );
		}
		$text = ( $id ? $_LANG->_( 'Edit News Feed' ) : $_LANG->_( 'New News Feed' ) );

		mosMenuBar::title( $text );

		mosMenuBar::startTable();
		mosMenuBar::save();
		mosMenuBar::apply();
		if ( $id ) {
			// for existing content items the button is renamed `close`
			mosMenuBar::cancel( 'cancel', 'Close' );
		} else {
			mosMenuBar::cancel();
		}
		mosMenuBar::help( 'screen.newsfeeds.edit' );
		mosMenuBar::endTable();
	}
}

$tasker = new newsfeedsToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>