<?php
/**
 * Part of the Joomla Framework LDAP Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Ldap;

/**
 * LDAP client class
 *
 * @since       1.0
 * @deprecated  The joomla/ldap package is deprecated
 */
class LdapClient
{
	/**
	 * Hostname of LDAP server
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $host;

	/**
	 * Authorization Method to use
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	public $auth_method;

	/**
	 * Port of LDAP server
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public $port;

	/**
	 * Base DN (e.g. o=MyDir)
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $base_dn;

	/**
	 * User DN (e.g. cn=Users,o=MyDir)
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $users_dn;

	/**
	 * Search String
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $search_string;

	/**
	 * Use LDAP Version 3
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	public $use_ldapV3;

	/**
	 * No referrals (server transfers)
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	public $no_referrals;

	/**
	 * Negotiate TLS (encrypted communications)
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	public $negotiate_tls;

	/**
	 * Ignore TLS Certificate (encrypted communications)
	 *
	 * @var    boolean
	 * @since  1.5.0
	 */
	public $ignore_reqcert_tls;

	/**
	 * Enable LDAP debug
	 *
	 * @var    boolean
	 * @since  1.5.0
	 */
	public $ldap_debug;

	/**
	 * Username to connect to server
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $username;

	/**
	 * Password to connect to server
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $password;

	/**
	 * LDAP Resource Identifier
	 *
	 * @var    resource
	 * @since  1.0
	 */
	private $resource;

	/**
	 * Current DN
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $dn;

	/**
	 * Flag tracking whether the connection has been bound
	 *
	 * @var    boolean
	 * @since  1.3.0
	 */
	private $isBound = false;

	/**
	 * Constructor
	 *
	 * @param   object  $configObj  An object of configuration variables
	 *
	 * @since   1.0
	 */
	public function __construct($configObj = null)
	{
		if (\is_object($configObj))
		{
			$vars = get_class_vars(\get_class($this));

			foreach (array_keys($vars) as $var)
			{
				if (substr($var, 0, 1) != '_')
				{
					$param = $configObj->get($var);

					if ($param)
					{
						$this->$var = $param;
					}
				}
			}
		}
	}

	/**
	 * Class destructor.
	 *
	 * @since   1.3.0
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Connect to an LDAP server
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function connect()
	{
		if ($this->host == '')
		{
			return false;
		}

		if ($this->ignore_reqcert_tls)
		{
			putenv('LDAPTLS_REQCERT=never');
		}

		if ($this->ldap_debug)
		{
			ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7);
		}

		$this->resource = ldap_connect($this->host, $this->port);

		if (!$this->resource)
		{
			return false;
		}

		if ($this->use_ldapV3 && !ldap_set_option($this->resource, LDAP_OPT_PROTOCOL_VERSION, 3))
		{
			return false;
		}

		if (!ldap_set_option($this->resource, LDAP_OPT_REFERRALS, (int) $this->no_referrals))
		{
			return false;
		}

		if ($this->negotiate_tls && !ldap_start_tls($this->resource))
		{
			return false;
		}

		return true;
	}

	/**
	 * Close the connection
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function close()
	{
		if ($this->isConnected())
		{
			$this->unbind();
		}

		$this->resource = null;
	}

	/**
	 * Sets the DN with some template replacements
	 *
	 * @param   string  $username  The username
	 * @param   string  $nosub     ...
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setDn($username, $nosub = 0)
	{
		if ($this->users_dn == '' || $nosub)
		{
			$this->dn = $username;
		}
		elseif (\strlen($username))
		{
			$this->dn = str_replace('[username]', $username, $this->users_dn);
		}
		else
		{
			$this->dn = '';
		}
	}

	/**
	 * Get the configured DN
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getDn()
	{
		return $this->dn;
	}

	/**
	 * Anonymously binds to LDAP directory
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function anonymous_bind()
	{
		if (!$this->isConnected())
		{
			if (!$this->connect())
			{
				return false;
			}
		}

		$this->isBound = ldap_bind($this->resource);

		return $this->isBound;
	}

	/**
	 * Binds to the LDAP directory
	 *
	 * @param   string  $username  The username
	 * @param   string  $password  The password
	 * @param   string  $nosub     ...
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function bind($username = null, $password = null, $nosub = 0)
	{
		if (!$this->isConnected())
		{
			if (!$this->connect())
			{
				return false;
			}
		}

		if ($username === null)
		{
			$username = $this->username;
		}

		if ($password === null)
		{
			$password = $this->password;
		}

		$this->setDn($username, $nosub);

		$this->isBound = ldap_bind($this->resource, $this->getDn(), $password);

		return $this->isBound;
	}

	/**
	 * Unbinds from the LDAP directory
	 *
	 * @return  boolean
	 *
	 * @since   1.3.0
	 */
	public function unbind()
	{
		if ($this->isBound && $this->resource && \is_resource($this->resource))
		{
			return ldap_unbind($this->resource);
		}

		return true;
	}

	/**
	 * Perform an LDAP search using comma separated search strings
	 *
	 * @param   string  $search  search string of search values
	 *
	 * @return  array  Search results
	 *
	 * @since   1.0
	 */
	public function simple_search($search)
	{
		$results = explode(';', $search);

		foreach ($results as $key => $result)
		{
			$results[$key] = '(' . $result . ')';
		}

		return $this->search($results);
	}

	/**
	 * Performs an LDAP search
	 *
	 * @param   array   $filters     Search Filters (array of strings)
	 * @param   string  $dnoverride  DN Override
	 * @param   array   $attributes  An array of attributes to return (if empty, all fields are returned).
	 *
	 * @return  array  Multidimensional array of results
	 *
	 * @since   1.0
	 */
	public function search(array $filters, $dnoverride = null, array $attributes = array())
	{
		$result = array();

		if (!$this->isBound || !$this->isConnected())
		{
			return $result;
		}

		if ($dnoverride)
		{
			$dn = $dnoverride;
		}
		else
		{
			$dn = $this->base_dn;
		}

		foreach ($filters as $searchFilter)
		{
			$searchResult = ldap_search($this->resource, $dn, $searchFilter, $attributes);

			if ($searchResult && ($count = ldap_count_entries($this->resource, $searchResult)) > 0)
			{
				for ($i = 0; $i < $count; $i++)
				{
					$result[$i] = array();

					if (!$i)
					{
						$firstentry = ldap_first_entry($this->resource, $searchResult);
					}
					else
					{
						$firstentry = ldap_next_entry($this->resource, $firstentry);
					}

					// Load user-specified attributes
					$attributeResult = ldap_get_attributes($this->resource, $firstentry);

					// LDAP returns an array of arrays, fit this into attributes result array
					foreach ($attributeResult as $ki => $ai)
					{
						if (\is_array($ai))
						{
							$subcount        = $ai['count'];
							$result[$i][$ki] = array();

							for ($k = 0; $k < $subcount; $k++)
							{
								$result[$i][$ki][$k] = $ai[$k];
							}
						}
					}

					$result[$i]['dn'] = ldap_get_dn($this->resource, $firstentry);
				}
			}
		}

		return $result;
	}

	/**
	 * Replace attribute values with new ones
	 *
	 * @param   string  $dn         The DN which contains the attribute you want to replace
	 * @param   string  $attribute  The attribute values you want to replace
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function replace($dn, $attribute)
	{
		if (!$this->isBound || !$this->isConnected())
		{
			return false;
		}

		return ldap_mod_replace($this->resource, $dn, $attribute);
	}

	/**
	 * Modify an LDAP entry
	 *
	 * @param   string  $dn         The DN which contains the attribute you want to modify
	 * @param   string  $attribute  The attribute values you want to modify
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function modify($dn, $attribute)
	{
		if (!$this->isBound || !$this->isConnected())
		{
			return false;
		}

		return ldap_modify($this->resource, $dn, $attribute);
	}

	/**
	 * Delete attribute values from current attributes
	 *
	 * @param   string  $dn         The DN which contains the attribute you want to remove
	 * @param   string  $attribute  The attribute values you want to remove
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function remove($dn, $attribute)
	{
		if (!$this->isBound || !$this->isConnected())
		{
			return false;
		}

		return ldap_mod_del($this->resource, $dn, $attribute);
	}

	/**
	 * Compare value of attribute found in entry specified with DN
	 *
	 * @param   string  $dn         The DN which contains the attribute you want to compare
	 * @param   string  $attribute  The attribute whose value you want to compare
	 * @param   string  $value      The value you want to check against the LDAP attribute
	 *
	 * @return  boolean|integer  Boolean result of the comparison or -1 on error
	 *
	 * @since   1.0
	 */
	public function compare($dn, $attribute, $value)
	{
		if (!$this->isBound || !$this->isConnected())
		{
			return false;
		}

		return ldap_compare($this->resource, $dn, $attribute, $value);
	}

	/**
	 * Read attributes of a given DN
	 *
	 * @param   string  $dn  The DN of the object you want to read
	 *
	 * @return  array|boolean  Array of attributes for the given DN or boolean false on failure
	 *
	 * @since   1.0
	 */
	public function read($dn)
	{
		if (!$this->isBound || !$this->isConnected())
		{
			return false;
		}

		$base   = substr($dn, strpos($dn, ',') + 1);
		$cn     = substr($dn, 0, strpos($dn, ','));
		$result = ldap_read($this->resource, $base, $cn);

		if ($result === false)
		{
			return false;
		}

		return ldap_get_entries($this->resource, $result);
	}

	/**
	 * Delete an entry from a directory
	 *
	 * @param   string  $dn  The DN of the object you want to delete
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function delete($dn)
	{
		if (!$this->isBound || !$this->isConnected())
		{
			return false;
		}

		return ldap_delete($this->resource, $dn);
	}

	/**
	 * Add entries to LDAP directory
	 *
	 * @param   string  $dn       The DN where you want to put the object
	 * @param   array   $entries  An array of arrays describing the object to add
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function create($dn, array $entries)
	{
		if (!$this->isBound || !$this->isConnected())
		{
			return false;
		}

		return ldap_add($this->resource, $dn, $entries);
	}

	/**
	 * Add attribute values to current attributes
	 *
	 * @param   string  $dn     The DN of the entry to add the attribute
	 * @param   array   $entry  An array of arrays with attributes to add
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function add($dn, array $entry)
	{
		if (!$this->isBound || !$this->isConnected())
		{
			return false;
		}

		return ldap_mod_add($this->resource, $dn, $entry);
	}

	/**
	 * Modify the name of an entry
	 *
	 * @param   string   $dn           The DN of the entry at the moment
	 * @param   string   $newdn        The DN of the entry should be (only cn=newvalue)
	 * @param   string   $newparent    The full DN of the parent (null by default)
	 * @param   boolean  $deleteolddn  Delete the old values (default)
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function rename($dn, $newdn, $newparent, $deleteolddn)
	{
		if (!$this->isBound || !$this->isConnected())
		{
			return false;
		}

		return ldap_rename($this->resource, $dn, $newdn, $newparent, $deleteolddn);
	}

	/**
	 * Escape a string
	 *
	 * @param   string   $value   The subject string
	 * @param   string   $ignore  Characters to ignore when escaping.
	 * @param   integer  $flags   The context the escaped string will be used in LDAP_ESCAPE_FILTER or LDAP_ESCAPE_DN
	 *
	 * @return  string
	 *
	 * @since   1.2.0
	 */
	public function escape($value, $ignore = '', $flags = 0)
	{
		return ldap_escape($value, $ignore, $flags);
	}

	/**
	 * Return the LDAP error message of the last LDAP command
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getErrorMsg()
	{
		if (!$this->isBound || !$this->isConnected())
		{
			return '';
		}

		return ldap_error($this->resource);
	}

	/**
	 * Check if the connection is established
	 *
	 * @return  boolean
	 *
	 * @since   1.3.0
	 */
	public function isConnected()
	{
		return $this->resource && \is_resource($this->resource);
	}

	/**
	 * Converts a dot notation IP address to net address (e.g. for Netware, etc)
	 *
	 * @param   string  $ip  IP Address (e.g. xxx.xxx.xxx.xxx)
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public static function ipToNetAddress($ip)
	{
		$parts   = explode('.', $ip);
		$address = '1#';

		foreach ($parts as $int)
		{
			$tmp = dechex($int);

			if (\strlen($tmp) != 2)
			{
				$tmp = '0' . $tmp;
			}

			$address .= '\\' . $tmp;
		}

		return $address;
	}

	/**
	 * Extract readable network address from the LDAP encoded networkAddress attribute.
	 *
	 * Please keep this document block and author attribution in place.
	 *
	 * Novell Docs, see: http://developer.novell.com/ndk/doc/ndslib/schm_enu/data/sdk5624.html#sdk5624
	 * for Address types: http://developer.novell.com/ndk/doc/ndslib/index.html?page=/ndk/doc/ndslib/schm_enu/data/sdk4170.html
	 * LDAP Format, String:
	 * taggedData = uint32String "#" octetstring
	 * byte 0 = uint32String = Address Type: 0= IPX Address; 1 = IP Address
	 * byte 1 = char = "#" - separator
	 * byte 2+ = octetstring - the ordinal value of the address
	 * Note: with eDirectory 8.6.2, the IP address (type 1) returns
	 * correctly, however, an IPX address does not seem to.  eDir 8.7 may correct this.
	 * Enhancement made by Merijn van de Schoot:
	 * If addresstype is 8 (UDP) or 9 (TCP) do some additional parsing like still returning the IP address
	 *
	 * @param   string  $networkaddress  The network address
	 *
	 * @return  array
	 *
	 * @author  Jay Burrell, Systems & Networks, Mississippi State University
	 * @since   1.0
	 */
	public static function ldapNetAddr($networkaddress)
	{
		$addr     = '';
		$addrtype = (int) substr($networkaddress, 0, 1);

		// Throw away bytes 0 and 1 which should be the addrtype and the "#" separator
		$networkaddress = substr($networkaddress, 2);

		if (($addrtype == 8) || ($addrtype = 9))
		{
			// TODO 1.6: If UDP or TCP, (TODO fill addrport and) strip portnumber information from address
			$networkaddress = substr($networkaddress, (\strlen($networkaddress) - 4));
		}

		$addrtypes = array(
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
			'Count',
		);

		$len = \strlen($networkaddress);

		if ($len > 0)
		{
			for ($i = 0; $i < $len; $i++)
			{
				$byte = substr($networkaddress, $i, 1);
				$addr .= \ord($byte);

				if (($addrtype == 1) || ($addrtype == 8) || ($addrtype = 9))
				{
					// Dot separate IP addresses...
					$addr .= '.';
				}
			}

			if (($addrtype == 1) || ($addrtype == 8) || ($addrtype = 9))
			{
				// Strip last period from end of $addr
				$addr = substr($addr, 0, \strlen($addr) - 1);
			}
		}
		else
		{
			$addr .= 'Address not available.';
		}

		return array('protocol' => $addrtypes[$addrtype], 'address' => $addr);
	}

	/**
	 * Generates a LDAP compatible password
	 *
	 * @param   string  $password  Clear text password to encrypt
	 * @param   string  $type      Type of password hash, either md5 or SHA
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public static function generatePassword($password, $type = 'md5')
	{
		switch (strtolower($type))
		{
			case 'sha':
				return '{SHA}' . base64_encode(pack('H*', sha1($password)));

			case 'md5':
			default:
				return '{MD5}' . base64_encode(pack('H*', md5($password)));
		}
	}
}
