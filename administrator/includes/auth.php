<?php
/**
* @version $Id: auth.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

require( dirname( dirname( dirname( __FILE__ ) ) ) . '/configuration.php' );

// enables switching to secure https
if ( $_SERVER["SERVER_PORT"] == '443' ) {
   $mosConfig_live_site = str_replace( 'http://', 'https://', $mosConfig_live_site );
}

if (!defined( '_MOS_MAMBO_INCLUDED' )) {
	require( $mosConfig_absolute_path . '/includes/mambo.php' );
}

session_name( 'mosadmin' );
session_start();

if (!mosGetParam( $_SESSION, 'session_id' )) {
	mosRedirect( 'index.php' );
}

// mainframe is an API workhorse, lots of 'core' interaction routines
$mainframe = new mosMainFrame( $database, null, true );
$mainframe->initSession( 'php' );

/** get the information about the current user from the sessions table */
$my = $mainframe->getUser();
// TODO: fix this patch to get gid to work properly
$my->gid = array_shift( $acl->get_object_groups( $acl->get_object_id( 'users', $my->id, 'ARO' ), 'ARO' ) );

// double check
if ($my->id < 1 || !$acl->acl_check( 'login', 'administrator', 'users', $my->usertype )) {
	$mainframe->logout();
	mosRedirect( 'index.php' );
}
?>