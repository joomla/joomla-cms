<?php
/**
* @version $Id: toolbar.media.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Massmail
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Media Manager
 * @package Joomla
 * @subpackage Media
 */
class mediaToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function mediaToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );
	}

	function view() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Media Manager' ), 'mediamanager.png', 'index2.php?option=com_media' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'showNewDir', 'new.png', 'new_f2.png', $_LANG->_( 'Folder' ), false );
		mosMenuBar::custom( 'showUpload', 'upload.png', 'upload_f2.png', $_LANG->_( 'Upload' ), false );
		mosMenuBar::help( 'screen.mediamanager' );
		mosMenuBar::endTable();
	}
}

$tasker = new mediaToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>