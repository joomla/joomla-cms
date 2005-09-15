<?php
/**
* @version $Id: toolbar.messages.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Messages
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Messages Manager
 * @package Mambo
 * @subpackage Messages
 */
class messagesToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function messagesToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );

		// additional mappings
		$this->registerTask( 'edit', 'edit' );
		$this->registerTask( 'reply', 'edit' );
		$this->registerTask( 'new', 'edit' );
		$this->registerTask( 'view', 'message' );
	}

	function view() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Private Messaging Manager' ), 'inbox.png', 'index2.php?option=com_messages' );

		mosMenuBar::startTable();
		mosMenuBar::addNew();
		mosMenuBar::deleteList();
		mosMenuBar::help( 'screen.messages.inbox' );
		mosMenuBar::endTable();
	}

	function edit( ){
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'New Private Message' ), 'inbox.png' );

		mosMenuBar::startTable();
		mosMenuBar::save( 'send', 'Send' );
		mosMenuBar::cancel();
		mosMenuBar::help( 'screen.messages.edit' );
		mosMenuBar::endTable();
	}

	function message( ){
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'View Private Message' ), 'inbox.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom('reply', 'restore.png', 'restore_f2.png', 'Reply', false );
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}

	function config( ){
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Private Messaging Configuration' ), 'inbox.png' );

		mosMenuBar::startTable();
		mosMenuBar::save( 'saveconfig' );
		mosMenuBar::apply( 'applyconfig' );
		mosMenuBar::cancel( 'cancelconfig', 'Close' );
		mosMenuBar::help( 'screen.messages.conf' );
		mosMenuBar::endTable();
	}
}

$tasker = new messagesToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>