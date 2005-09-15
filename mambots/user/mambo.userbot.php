<?php
/**
* @version $Id: mambo.userbot.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Mambots
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

//Login User event
$_MAMBOTS->registerFunction( 'onLoginUser', 'botMamboLoginUser' );

//Logout User event
$_MAMBOTS->registerFunction( 'onLogoutUser', 'botMamboLogoutUser' );

/**
* Joomla! user login method
* Method is called when a user is login in
* @param 	string	The user name
* @param	string	The password
* @return	int		The id of the user
*/
function botMamboLoginUser( $username, $password ) {
	global $database;

	$query = 'SELECT id
		FROM #__users
		WHERE username=' . $database->Quote( $username ) . ' AND password=' . $database->Quote( md5( $password ) );
	$database->setQuery( $query );

	return $database->loadResult();
}

/**
* Joomla! logout user method
* Method is called when a user is login out
* @param 	array	  	holds the user data
*/
function botMamboLogoutUser( $user ) {
	//do nothing
}

?>