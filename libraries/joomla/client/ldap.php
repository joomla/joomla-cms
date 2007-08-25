<?php

/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Client
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * LDAP client class
 *
 * @author		Samuel Moffatt <pasamio@gmail.com>
 * @package		Joomla.Framework
 * @subpackage		Client
 * @since		1.5
 */
class JLDAP
{
	/** @var string Hostname of LDAP server
		@access public */
	var $host = null;
	/** @var bool Authorization Method to use
		@access public */
	var $auth_method = null;
	/** @var int Port of LDAP server
		@access public */
	var $port = null;
	/** @var string Base DN (e.g. o=MyDir)
		@access public */
	var $base_dn = null;
	/** @var string User DN (e.g. cn=Users,o=MyDir)
		@access public */
	var $users_dn = null;
	/** @var string Search String
		@access public */
	var $search_string = null;
	/** @var boolean Use LDAP Version 3
		@access public */
	var $use_ldapV3 = null;
	/** @var boolean No referrals (server transfers)
		@access public */
	var $no_referrals = null;
	/** @var boolean Negotiate TLS (encrypted communications)
		@access public */
	var $negotiate_tls = null;

	/** @var string Username to connect to server
		@access public */
	var $username = null;
	/** @var string Password to connect to server
		@access public */
	var $password = null;

	/** @var mixed LDAP Resource Identifier
		@access private */
	var $_resource = null;
	/** @var string Current DN
		@access private */
	var $_dn = null;

	/**
	 * Constructor
	 * @param object An object of configuration variables
	 * @access public
	 */
	function JLDAP($configObj = null)
	{
		if (is_object($configObj)) {
			$vars = get_class_vars(get_class($this));
			foreach (array_keys($vars) as $var) {
				if (substr($var, 0, 1) != '_') {
					if ($param = $configObj->get($var)) {
						$this-> $var = $param;
					}
				}
			}
		}
	}

	/**
	 * Connect to server
	 * @return boolean True if successful
	 * @access public
	 */
	function connect()
	{
		if ($this->host == '') {
			return false;
		}
		$this->_resource = @ ldap_connect($this->host, $this->port);
		if ($this->_resource) {
			if ($this->use_ldapV3) {
				if (!ldap_set_option($this->_resource, LDAP_OPT_PROTOCOL_VERSION, 3)) {
					return false;
				}
			}
			if (!ldap_set_option($this->_resource, LDAP_OPT_REFERRALS, intval($this->no_referrals))) {
				return false;
			}
			if ($this->negotiate_tls) {
				if (!ldap_start_tls($this->_resource)) {
					return false;
				}
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Close the connection
	 * @access public
	 */
	function close() {
		@ ldap_close($this->_resource);
	}

	/**
	 * Sets the DN with some template replacements
	 * @param string The username
	 * @access public
	 */
	function setDN($username,$nosub=0)
	{
		if ($this->users_dn == '' || $nosub) {
			$this->_dn = $username;
		} else {
			$this->_dn = str_replace('[username]', $username, $this->users_dn);
		}
	}

	/**
	 * @return string The current dn
	 * @access public
	 */
	function getDN() {
		return $this->_dn;
	}

	/**
	 * Anonymously Binds to LDAP Directory
	 */
	function anonymous_bind()
	{
		$bindResult = @ldap_bind($this->_resource);
		return $bindResult;
	}

	/**
	 * Binds to the LDAP directory
	 * @param string The username
	 * @param string The password
	 * @return boolean Result
	 * @access public
	 */
	function bind($username = null, $password = null, $nosub = 0)
	{
		if (is_null($username)) {
			$username = $this->username;
		}
		if (is_null($password)) {
			$password = $this->password;
		}
		$this->setDN($username,$nosub);
		$bindResult = ldap_bind($this->_resource, $this->getDN(), $password);
		return $bindResult;
	}

	/**
	 * Perform an LDAP search using comma seperated search strings
	 * @param string search string of search values
	 */
	function simple_search($search)
	{
		$results = explode(';', $search);
		foreach($results as $key=>$result) {
			$results[$key] = '('.$result.')';
		}
		return $this->search($results);
	}


	/**
	 * Perform an LDAP search
	 * @param array Search Filters (array of strings)
	 * @param string DN Override
	 * @return array Multidimensional array of results
	 * @access public
	 */
	function search($filters, $dnoverride = null)
	{
		$attributes = array ();
		if ($dnoverride) {
			$dn = $dnoverride;
		} else {
			$dn = $this->base_dn;
		}

		$resource = $this->_resource;

		foreach ($filters as $search_filter)
		{
			$search_result = ldap_search($resource, $dn, $search_filter);
			if ($search_result && ($count = ldap_count_entries($resource, $search_result)) > 0)
			{
				for ($i = 0; $i < $count; $i++)
				{
					$attributes[$i] = Array ();
					if (!$i) {
						$firstentry = ldap_first_entry($resource, $search_result);
					} else {
						$firstentry = ldap_next_entry($resource, $firstentry);
					}
					$attributes_array = ldap_get_attributes($resource, $firstentry); // load user-specified attributes
					// ldap returns an array of arrays, fit this into attributes result array
					foreach ($attributes_array as $ki => $ai)
					{
						if (is_array($ai))
						{
							$subcount = $ai['count'];
							$attributes[$i][$ki] = Array ();
							for ($k = 0; $k < $subcount; $k++) {
								$attributes[$i][$ki][$k] = $ai[$k];
							}
						}
					}
					$attributes[$i]['dn'] = ldap_get_dn($resource, $firstentry);
				}
			}
		}
		return $attributes;
	}
	
	/**
	 * Replace an entry and return a true or false result
	 *
	 * @param string dn The DN which contains the attribute you want to compare
	 * @param string attribute The attribute values you want to replace
	 * @return mixed result of comparison (true, false, -1 on error)
	 */

	function replace($dn, $attribute) {
		return ldap_mod_replace($this->_resource, $dn, $attribute);
	}

	/**
	 * Compare an entry and return a true or false result
	 * @param string dn The DN which contains the attribute you want to compare
	 * @param string attribute The attribute whose value you want to compare
	 * @param string value The value you want to check against the LDAP attribute
	 * @return mixed result of comparison (true, false, -1 on error)
	 */
	function compare($dn, $attribute, $value) {
		return ldap_compare($this->_resource, $dn, $attribute, $value);
	}

	/**
	 * Converts a dot notation IP address to net address (e.g. for Netware, etc)
	 * @param string IP Address (e.g. xxx.xxx.xxx.xxx)
	 * @return string Net address
	 * @access public
	 */
	function ipToNetAddress($ip)
	{
		$parts = explode('.', $ip);
		$address = '1#';

		foreach ($parts as $int) {
			$tmp = dechex($int);
			if (strlen($tmp) != 2) {
				$tmp = '0' . $tmp;
			}
			$address .= '\\' . $tmp;
		}
		return $address;
	}

	/**
	 * extract readable network address from the LDAP encoded networkAddress attribute.
	 * @author Jay Burrell, Systems & Networks, Mississippi State University
	 * Please keep this document block and author attribution in place.
	 *  Novell Docs, see: http://developer.novell.com/ndk/doc/ndslib/schm_enu/data/sdk5624.html#sdk5624
	 *  for Address types: http://developer.novell.com/ndk/doc/ndslib/index.html?page=/ndk/doc/ndslib/schm_enu/data/sdk4170.html
	 *  LDAP Format, String:
	 *	 taggedData = uint32String "#" octetstring
	 *	 byte 0 = uint32String = Address Type: 0= IPX Address; 1 = IP Address
	 *	 byte 1 = char = "#" - separator
	 *	 byte 2+ = octetstring - the ordinal value of the address
	 *   Note: with eDirectory 8.6.2, the IP address (type 1) returns
	 *				 correctly, however, an IPX address does not seem to.  eDir 8.7 may
	 *				correct this.
	 */
	function LDAPNetAddr($networkaddress)
	{
		$addr = "";
		$addrtype = intval(substr($networkaddress, 0, 1));
		$networkaddress = substr($networkaddress, 2); // throw away bytes 0 and 1 which should be the addrtype and the "#" separator
		$addrtypes = array (
			'IPX',
			'IP',
			'SDLC',
			'Token Ring',
			'OSI',
			'AppleTalk',
			'NetBEUI',
			'Socket',
			'UDP',
			'TCP',
			'UDP6',
			'TCP6',
			'Reserved (12)',
			'URL',
			'Count'
		);
		$len = strlen($networkaddress);
		if ($len > 0)
		{
			for ($i = 0; $i < $len; $i += 1)
			{
				$byte = substr($networkaddress, $i, 1);
				$addr .= ord($byte);
				if ($addrtype == 1) { // dot separate IP addresses...
					$addr .= ".";
				}
			}
			if ($addrtype == 1) { // strip last period from end of $addr
				$addr = substr($addr, 0, strlen($addr) - 1);
			}
		} else {
			$addr .= "address not available.";
		}
		return Array('protocol'=>$addrtypes[$addrtype], 'address'=>$addr);
	}
}