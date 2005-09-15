<?php
/**
* @version $Id: toolbar.frontpage.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Content
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * Toolbar for Frontpage Manager
 * @package Joomla
 * @subpackage Frontpage
 */
class frontpageToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function frontpageToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		//$this->setAccessControl( 'com_weblinks', 'manage' );
	}

	function view() {
		global $_LANG;

		mosMenuBar::title( $_LANG->_( 'Frontpage Manager' ), 'frontpage.png', 'index2.php?option=com_frontpage' );

		mosMenuBar::startTable();
		// TODO
		//mosMenuBar::popup('', 'previewfrontpage', 'preview.png', 'Preview', true);
		mosMenuBar::custom('remove','delete.png','delete_f2.png','Remove', true);
		mosMenuBar::help( 'screen.frontpage' );
		mosMenuBar::endTable();
	}
}

$tasker = new frontpageToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>