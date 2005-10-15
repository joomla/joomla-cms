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
$_MAMBOTS->registerFunction( 'onLoginUser', 'botLdapLoginUser' );

//Logout User event
$_MAMBOTS->registerFunction( 'onLogoutUser', 'botLdapLogoutUser' );

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

	$ldap = new ldapConnector( $mambotParams );
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
function botMamboLogoutUser( $user ) {
	//do nothing
}

/**
 * LDAP Connector class
 * @package Joomla
 * @subpackage Mambots
 */
class ldapConnector {
	/** @var string */
	var $host = null;
	/** @var int */
	var $port = null;
	/** @var string */
	var $base_dn = null;
	/** @var string */
	var $users_dn = null;
	/** @var string */
	var $search_string = null;
	/** @var boolean */
	var $use_ldapV3 = null;
	/** @var boolean */
	var $no_referrals = null;
	/** @var boolean */
	var $negotiate_tls = null;

	/** @var string */
	var $username = null;
	/** @var string */
	var $password = null;

	/** @var mixed */
	var $_resource =  null;
	/** @var string */
	var $_dn = null;

	/**
	 * Constructor
	 * @param object An object of configuration variables
	 */
	function ldapConnector( $configObj=null ) {
		if (is_object( $configObj )) {
			$vars = get_class_vars( get_class( $this ) );
			foreach (array_keys( $vars ) as $var) {
				if (substr( $var, 0, 1 ) != '_') {
					if ($param = $configObj->get( $var )) {
						$this->$var = $param;
					}
				}
			}
		}
	}

	/**
	 * @return boolean True if successful
	 */
	function connect() {
		if ($this->host == '') {
			return false;
		}
		$this->_resource = @ldap_connect( $this->host, $this->port );
		if ($this->_resource) {
			if ($this->use_ldapV3) {
				if (!ldap_set_option( $this->_resource, LDAP_OPT_PROTOCOL_VERSION, 3 )) {
					return false;
					echo "<script> alert(\" failed to set LDAP protocol V3\"); </script>\n";
				}
			}
			if (!ldap_set_option( $this->_resource, LDAP_OPT_REFERRALS, intval( $this->no_referrals ))) {
				return false;
				echo "<script> alert(\" failed to set LDAP_OPT_REFERRALS option\"); </script>\n";
			}
			if ($this->negotiate_tls) {
				if (!ldap_start_tls( $this->_resource )) {
					return false;
					echo "<script> alert(\" ldap_start_tls failed\"); </script>\n";
				}
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Close the connection
	 */
	function close() {
		@ldap_close( $this->_resource );
	}

	/**
	 * Sets the DN with some template replacements
	 * @param string The username
	 */
	function setDN( $username ) {
		if ($this->users_dn == '') {
			$this->_dn = $username;
		} else {
			$this->_dn = str_replace( '[username]', $username, $this->users_dn );
		}
	}

	/**
	 * @return string The current dn
	 */
	function getDN() {
		return $this->_dn;
	}

	/**
	 * Binds to the LDAP directory
	 * @param string The username
	 * @param string The password
	 */
	function bind( $username=null, $password=null ) {
		if (is_null( $username )) {
			$username = $this->username;
		}
		if (is_null( $password )) {
			$username = $this->password;
		}
		$this->setDN( $username );
		$bindResult = @ldap_bind( $this->_resource, $this->getDN(), $password );

		return $bindResult;
	}

	/**
	 * Perform an LDAP search
	 */
	function search( $filters ) {
		$attributes = array();
		$dn = $this->getDN();
		$resource = $this->_resource;

		foreach ($filters as $search_filter) {
			$search_result = ldap_search( $resource, $dn , $search_filter );

			if (ldap_count_entries( $resource, $search_result ) == 1 ) {
				$firstentry = ldap_first_entry( $resource, $search_result );
				$attributes_array = ldap_get_attributes( $resource,  $firstentry ); // load user-specified attributes

				// ldap returns an array of arrays, fit this into attributes result array
				foreach($attributes_array as $ki=>$ai) {
					$attributes[$ki]=$ai[0];
				}
				if ($this->users_dn == '') {
					$attributes['dn'] = ldap_get_dn( $resource, $firstentry );
				} else {
					$attributes['dn'] = $dn;
				}
				break;
			}
		}
		return $attributes;
	}
}
?>