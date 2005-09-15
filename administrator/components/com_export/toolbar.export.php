<?php
/**
* @version $Id: toolbar.export.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Export
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Export
 * @package Joomla
 * @subpackage Export
 */
class exportToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function exportToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'exportOptions' );

		// set task level access control
		$this->setAccessControl( 'com_checkin', 'manage' );
	}

	function exportOptions() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Export Manager' ), 'backup.png', 'index2.php?option=com_export' );

		mosMenuBar::startTable();
		mosMenuBar::custom( 'export', 'forward.png','forward_f2.png', $_LANG->_( 'Export' ), true );
		mosMenuBar::help( 'screen.exportoptions' );
		mosMenuBar::endTable();
	}

	function export() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Export' ), 'backup.png' );

		mosMenuBar::startTable();
		mosMenuBar::back();
		mosMenuBar::help( 'screen.exportoptions' );
		mosMenuBar::endTable();
	}

	function restoreList() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Exported Files' ), 'backup.png' );

		mosMenuBar::startTable();
		mosMenuBar::custom('restore','restore.png','restore_f2.png',$_LANG->_( 'Restore' ), true);
		mosMenuBar::custom('deleteFiles','delete.png','delete_f2.png',$_LANG->_( 'Delete' ), true);
		mosMenuBar::help( 'screen.restorelist' );
		mosMenuBar::endTable();
	}
}

$tasker = new exportToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>