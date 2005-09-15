<?php
/**
* @version $Id: toolbar.statistics.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Statistics
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Statistics Manager
 * @package Mambo
 * @subpackage Statistics
 */
class statisticsToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function statisticsToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );
	}

	function view() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Browser, OS, Domain Statistics' ), 'browser.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'resetStats', 'delete.png', 'delete_f2.png', $_LANG->_( 'Delete' ), false );
		mosMenuBar::help( 'screen.stats.browser' );
		mosMenuBar::endTable();
	}

	function searches( ){
		global $_LANG;
		global $mainframe;

		$title = $_LANG->_( 'Search Engine Text' ) .' : ';
		$title .= $_LANG->_( 'logging is' ) .' : ';
		$title .= $mainframe->getCfg( 'enable_log_searches' ) ? '<b><font color="green">'. $_LANG->_( 'Enabled' ) .'</font></b>' : '<b><font color="red">'. $_LANG->_( 'Disabled' ) .'</font></b>';

		mosMenuBar::title( $title, 'searchtext.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'resetStats', 'delete.png', 'delete_f2.png', $_LANG->_( 'Delete' ), false );
		mosMenuBar::help( 'screen.stats.searches' );
		mosMenuBar::endTable();
	}

	function pageimp( ){
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Page Impression Statistics' ), 'impressions.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'resetStats', 'delete.png', 'delete_f2.png', $_LANG->_( 'Delete' ), false );
		mosMenuBar::help( 'screen.stats.impressions' );
		mosMenuBar::endTable();
	}
}

$tasker = new statisticsToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>