<?php
/**
* @version $Id: toolbar.cachemanager.php 137 2005-09-12 10:21:17Z eddieajau $
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
 * Toolbar for Contents Manager
 * @package Joomla
 * @subpackage Content
 */
class cachemanagerToolbar extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function cachemanagerToolbar() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'cache_manager' );
	}

	function cache_manager() {
		mosMenuBar::title( 'Cache Manager', 'addedit.png' );
		mosMenuBar::startTable();
		mosMenuBar::custom( 'cleancache', 'delete.png', 'delete_f2.png', 'Clean Selected', true );
		//mosMenuBar::custom( 'cleanallcache', 'delete.png', 'delete_f2.png', 'Clean All', false );
		mosMenuBar::custom( 'listcache', 'reload.png', 'reload_f2.png', 'Refresh', false );
		mosMenuBar::help( 'screen.cache.manager' );
		mosMenuBar::endTable();
	}
}

$tasker = new cachemanagerToolbar();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
?>