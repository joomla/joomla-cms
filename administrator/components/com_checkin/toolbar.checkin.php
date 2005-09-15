<?php
/**
* @version $Id: toolbar.checkin.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Checkin
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Checkin Manager
 * @package Mambo
 * @subpackage Checkin
 */
class checkinToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function checkinToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'checkinList' );

		// set task level access control
		$this->setAccessControl( 'com_checkin', 'manage' );
	}

	function checkinList() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Checkin Manager' ), 'checkin.png', 'index2.php?option=com_checkin' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'checkin', 'save.png', 'save_f2.png', $_LANG->_( 'Checkin' ), true );
		mosMenuBar::help( 'screen.checkin' );
		mosMenuBar::endTable();
	}
}

$tasker = new checkinToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>