<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define( '_VALID_MOS', 1 );

if (!file_exists( '../configuration.php' )) {
	header( 'Location: ../installation/index.php' );
	exit();
}

require_once( '../configuration.php' );

//Installation sub folder check, removed for work with SVN
/*if (file_exists( '../installation/index.php' )) {
	define( '_INSTALL_CHECK', 1 );
	include ('../offline.php');
	exit();
}*/

// enables switching to secure https
require_once(  $mosConfig_absolute_path . '/includes/joomla.php' );

// load system bot group
$_MAMBOTS->loadBotGroup( 'system' );

// trigger the onStart events
$_MAMBOTS->trigger( 'onBeforeStart' );

$option = mosGetParam( $_REQUEST, 'option', NULL );
$handle = mosGetParam( $_POST, 'handle', NULL );

// must start the session before we create the mainframe object
session_name( md5( $mosConfig_live_site ) );
session_start();

// mainframe is an API workhorse, lots of 'core' interaction routines
$mainframe = new mosMainFrame( $database, $option, '..', 1 );
$mainframe->initSession( 'php' );

// trigger the onAfterStart events
$_MAMBOTS->trigger( 'onAfterStart' );

$lang = mosGetParam( $_REQUEST, 'lang', $mosConfig_lang );

$_LANG =& JFactory::getLanguage( $option, true );
$_LANG->debug( $mosConfig_debug );

// gets template for page
$cur_template = $mainframe->getTemplate();

if (isset( $_POST['submit'] )) {
	$query = "SELECT COUNT(*)"
	. "\n FROM #__users"
	. "\n WHERE gid = 25"
	;
	$database->setQuery( $query );
	$count = intval( $database->loadResult() );

	if ( $count < 1 ) {
		mosErrorAlert( $_LANG->_( 'errorNoAdmins' ) );
	}

	if ($mainframe->login()) {
		$mainframe->setUserState( 'lang', $lang );
		session_write_close();
		/** cannot using mosredirect as this stuffs up the cookie in IIS */
		$handle = isset($handle) ? ('?handle=' . $handle) : '';
		mosErrorAlert( '', "document.location.href='index2.php" . $handle . "'", 2 );
	} else {
		mosErrorAlert( $_LANG->_( 'validUserPassAccess' ), "document.location.href='index.php'" );
	}
} else {

	initGzip();
	header(' Content-Type: text/html; charset=UTF-8');
	$path = $mosConfig_absolute_path . '/administrator/templates/' . $cur_template . '/login.php';
	require_once( $path );
	doGzip();
}
?>