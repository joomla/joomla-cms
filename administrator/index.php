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
require_once( '../includes/joomla.php' );
include_once ( $mosConfig_absolute_path . '/language/'. $mosConfig_lang .'.php' );

//Installation sub folder check, removed for work with SVN
/*if (file_exists( '../installation/index.php' )) {	
	define( '_INSTALL_CHECK', 1 );
	include ('../offline.php');
	exit();
}*/

$option = mosGetParam( $_REQUEST, 'option', NULL );

// mainframe is an API workhorse, lots of 'core' interaction routines
$mainframe = new mosMainFrame( $database, $option, '..', true );

$_LANG =& mosFactory::getLanguage( $option, true );
$_LANG->debug( $mosConfig_debug );

if (isset( $_POST['submit'] )) {
	/** escape and trim to minimise injection of malicious sql */
	$usrname 	= $database->getEscaped( mosGetParam( $_POST, 'usrname', '' ) );
	$pass 		= $database->getEscaped( mosGetParam( $_POST, 'pass', '' ) );

	if (!$pass) {
		echo "<script>alert('Please enter a password'); document.location.href='index.php';</script>\n";
	} else {
		$pass = md5( $pass );
	}

	$query = "SELECT COUNT(*)"
	. "\n FROM #__users"
	. "\n WHERE ("
	// Administrators
	. "\n gid = 24"
	// Super Administrators
	. "\n OR gid = 25"
	. "\n )"
	;
	$database->setQuery( $query );
	$count = intval( $database->loadResult() );
	if ($count < 1) {
		echo "<script>alert(\"". _LOGIN_NOADMINS ."\"); window.history.go(-1); </script>\n";
		exit();
	}

	$my = null;
	$query = "SELECT *"
	. "\n FROM #__users"
	. "\n WHERE username = '$usrname'"
	. "\n AND block = 0"
	;
	$database->setQuery( $query );
	$database->loadObject( $my );

	/** find the user group (or groups in the future) */
	if (@$my->id) {
		$grp 			= $acl->getAroGroup( $my->id );
		$my->gid 		= $grp->group_id;
		$my->usertype 	= $grp->name;

		if ( strcmp( $my->password, $pass ) || !$acl->acl_check( 'administration', 'login', 'users', $my->usertype ) ) {
			echo "<script>alert('Incorrect Username, Password, or Access Level.  Please try again'); document.location.href='index.php';</script>\n";
			exit();
		}

		session_name( md5( $mosConfig_live_site ) );
		session_start();

		$logintime 	= time();
		$session_id = md5( $my->id . $my->username . $my->usertype . $logintime );
		$query = "INSERT INTO #__session"
		. "\n SET time = '$logintime', session_id = '$session_id', userid = $my->id, usertype = '$my->usertype', username = '$my->username'"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			echo $database->stderr();
		}

		$_SESSION['session_id'] 		= $session_id;
		$_SESSION['session_user_id'] 	= $my->id;
		$_SESSION['session_username'] 	= $my->username;
		$_SESSION['session_usertype'] 	= $my->usertype;
		$_SESSION['session_gid'] 		= $my->gid;
		$_SESSION['session_logintime'] 	= $logintime;
		$_SESSION['session_user_params']= $my->params;
		$_SESSION['session_userstate'] 	= array();

		session_write_close();
		/** cannot using mosredirect as this stuffs up the cookie in IIS */
		echo "<script>document.location.href='index2.php';</script>\n";
		exit();
	} else {
		echo "<script>alert('Incorrect Username and Password, please try again'); document.location.href='index.php';</script>\n";
		exit();
	}
} else {
	initGzip();
	$path = $mosConfig_absolute_path . '/administrator/templates/' . $mainframe->getTemplate() . '/login.php';
	require_once( $path );
	doGzip();
}
?>