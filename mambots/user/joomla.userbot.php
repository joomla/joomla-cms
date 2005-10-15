<?php
/**
* @version $Id:  $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

//Login User event
$_MAMBOTS->registerFunction( 'onLoginUser', 'botJoomlaLoginUser' );

//Logout User event
$_MAMBOTS->registerFunction( 'onLogoutUser', 'botJoomlaLogoutUser' );

/**
* Joomla user login method
* Method is called when a user is login in
* @param 	string	The user name
* @param	string	The password
* @return	int		The id of the user
*/
function botJoomlaLoginUser( $username, $password ) {
	global $database;

	$query = 'SELECT id
		FROM #__users
		WHERE username=' . $database->Quote( $username ) . ' AND password=' . $database->Quote( md5( $password ) );
	$database->setQuery( $query );

	return $database->loadResult();
}

/**
* Joomla logout user method
* Method is called when a user is login out
* @param 	array	  	holds the user data
*/
function botMamboLogoutUser( $user ) {
	//do nothing
}

?>