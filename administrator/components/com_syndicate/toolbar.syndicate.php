<?php
/**
* @version $Id: toolbar.syndicate.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Syndicate
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Syndicate Manager
 * @package Joomla
 * @subpackage Synidicate
 */
class syndicateToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function syndicateToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' )
	}

	function view() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Syndication Manager', 'asterisk.png','index2.php?option=com_syndicate' ) );

		mosMenuBar::startTable();
		mosMenuBar::save();
		mosMenuBar::cancel( 'cancel', 'Close' );
		mosMenuBar::help( 'screen.syndicate' );
		mosMenuBar::endTable();
	}
}

$tasker = new syndicateToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>