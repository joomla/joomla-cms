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

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$currentDate = date( "Y-m-d\TH:i:s" );

if ( isset( $_SESSION['session_user_id'] ) && $_SESSION['session_user_id'] != '' ) {
	$query = "UPDATE #__users"
	. "\n SET lastvisitDate = '$currentDate'"
	. "\n WHERE id = ". $_SESSION['session_user_id']
	;
	$database->setQuery( $query );

	if (!$database->query()) {
		echo $database->stderr();
	}
}

if ( isset( $_SESSION['session_id'] ) && $_SESSION['session_id'] != '' ) {
	$query = "DELETE FROM #__session"
	. "\n WHERE session_id = '". $_SESSION['session_id'] ."'"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo $database->stderr();
	}
}

$name 		= '';
$fullname 	= '';
$id 		= '';
$session_id = '';

session_unregister( 'session_id' );
session_unregister( 'session_user_id' );
session_unregister( 'session_username' );
session_unregister( 'session_usertype' );
session_unregister( 'session_logintime' );

if (session_is_registered( 'session_id' )) {
	session_destroy();
}
if (session_is_registered( 'session_user_id' )) {
	session_destroy();
}
if (session_is_registered( 'session_username' )) {
	session_destroy();
}
if (session_is_registered( 'session_usertype' )) {
	session_destroy();
}
if (session_is_registered( 'session_logintime' )) {
	session_destroy();
}
mosRedirect( '../index.php' );
?>