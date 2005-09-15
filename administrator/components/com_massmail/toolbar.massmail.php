<?php
/**
* @version $Id: toolbar.massmail.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
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
 * Toolbar for Massmail Manager
 * @package Mambo
 * @subpackage Massmail
 */
class massmailToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function massmailToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );
	}

	function view() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Mass Mail' ), 'massemail.png', 'index2.php?option=com_massmail' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'send', 'publish.png', 'publish_f2.png', 'Send Mail', false);
		mosMenuBar::cancel( 'cancel', 'Close' );
		mosMenuBar::help( 'screen.users.massmail' );
		mosMenuBar::endTable();
	}
}

$tasker = new massmailToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>