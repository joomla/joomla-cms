<?php
/**
* @version $Id: joomla.php 104 2005-09-11 21:20:55Z stingrey $ 
* @package Joomla
* @subpackage LDAP Connector
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
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
	function search( $filters, $dnoverride=null ) {
		$attributes = array();
		if($dnoverride) {
			$dn = $dnoverride;
		} else {
			$dn = $this->getDN();
		}
		
		$resource = $this->_resource;

		foreach ($filters as $search_filter) {
			$search_result = ldap_search( $resource, $dn , $search_filter);
			if (($count = ldap_count_entries( $resource, $search_result )) > 0 ) {
				for($i = 0; $i < $count; $i++) {
					$attributes[$i] = Array();
					if(!$i) {
						$firstentry = ldap_first_entry( $resource, $search_result );
					} else {
						$firstentry = ldap_next_entry ( $resource, $firstentry );
					}
					$attributes_array = ldap_get_attributes( $resource,  $firstentry ); // load user-specified attributes
					// ldap returns an array of arrays, fit this into attributes result array
					foreach($attributes_array as $ki=>$ai) {	
						if(is_array($ai)) {
							$subcount = $ai['count'];
							$attributes[$i][$ki] = Array();
							for($k = 0; $k < $subcount; $k++) {
								$attributes[$i][$ki][$k] = $ai[$k];
							}
						} /*else {
							//$attributes[$i][$ki]=$ai;
						}*/
						
					}
//					if ($this->users_dn == '') {
					$attributes[$i]['dn'] = ldap_get_dn( $resource, $firstentry );
//					} else {
//						$attributes[$i]['dn'] = $dn;
//					}
				} //*/
			}
		}
		return $attributes;
	}

	/**
	 * Converts a dot notation IP address to net address
	 * @param string
	 * @return string
	 */
	function ipToNetAddress($ip) {
		$parts = explode('.',$ip);
		$address = '1#';
		
		foreach($parts as $int) {
			$tmp = dechex($int);
			if(strlen($tmp) != 2) {
				$tmp = '0'.$tmp;
			}
			$address .= '\\' . $tmp;
		}
		return $address;
	}
}
