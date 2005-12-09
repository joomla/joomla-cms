<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Mambots
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.connectors.ldap');

//Login User event
$mainframe->registerEvent( 'onLoginUser', 'botLdapLoginUser' );

//Logout User event
$mainframe->registerEvent( 'onLogoutUser', 'botLdapLogoutUser' );

/**
* LDAP user authenication method
* @param 	string	The user name
* @param	string	The password
* @return	int		The id of the user
*/
function botLdapLoginUser( $username, $password ) {
	global $database;
	// load mambot parameters
	$query = "SELECT params FROM #__mambots WHERE element = 'ldap.userbot' AND folder = 'user'";
	$database->setQuery( $query );
	$params = $database->loadResult();
	$mambotParams =& new mosParameters( $params );

	$ldap = new JLDAP( $mambotParams );
	//print_r($ldap);
	if (!$ldap->connect()) {
		return 0;
	}
	$success = $ldap->bind( $username, $password );
/*
	// just a test, please leave
	$search_filters = array( '(objectclass=*)' );
	$attributes = $ldap->search( $search_filters );
	print_r($attributes);
*/
	$ldap->close();

	$userId = 0;
	if ($success) {
	  	$query = 'SELECT id
			FROM #__users
			WHERE username=' . $database->Quote( $username );
		$database->setQuery( $query );
		$userId = $database->loadResult();
	}
	return $userId;
}

/**
* LDAP logout user method
* @param 	array	  	holds the user data
*/
function botLdapLogoutUser( $user ) {
	//do nothing
}

?>
