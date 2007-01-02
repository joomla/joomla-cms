<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	User
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * This is the status code returned when the authentication is success.
 */
define('JAUTHENTICATE_STATUS_SUCCESS', 1);

/**
 * Status to indicate cancellation of authentication.
 */
define('JAUTHENTICATE_STATUS_CANCEL', 2);

/**
 * This is the status code returned when the authentication failed
 */
define('JAUTHENTICATE_STATUS_FAILURE', 4);

/**
 * Authorization class, provides an interface for the Joomla authentication system
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	User
 * @since		1.5
 */
class JAuthenticate extends JObject
{
	/**
	 * Constructor
	 *
	 * @access protected
	 */
	function __construct()
	{
		// Get the global event dispatcher to load the plugins
		$dispatcher =& JEventDispatcher::getInstance();

		$plugins = JPluginHelper::getPlugin('authentication');

		$isLoaded = false;
		foreach ($plugins as $plugin) {
			$isLoaded |= $this->loadPlugin($plugin->element, $dispatcher);
		}

		if (!$isLoaded) {
			JError::raiseWarning('SOME_ERROR_CODE', 'JAuthenticate::__constructor: Could not load authentication libraries.', $plugins);
		}
	}

	/**
	 * Returns a reference to a global authentication object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $auth = &JAuthenticate::getInstance();</pre>
	 *
	 * @static
	 * @access public
	 * @return object The global JAuthenticate object
	 * @since 1.5
	 */
	function & getInstance()
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[0])) {
			$instances[0] = new JAuthenticate();
		}

		return $instances[0];
	}

	/**
	 * Finds out if a set of login credentials are valid by asking all obvserving
	 * objects to run their respective authentication routines.
	 *
	 * @access public
	 * @param string 	The username.
	 * @param string 	The password.
	 * @return mixed Integer userid for valid user if credentials are valid or boolean false if they are not
	 * @since 1.5
	 */
	function authenticate($username, $password)
	{
		// Initialize variables
		$auth = false;

		// Get the global event dispatcher object
		$dispatcher = &JEventDispatcher::getInstance();

		// Time to authenticate the credentials.  Lets fire the auth event
		$results = $dispatcher->trigger( 'onAuthenticate', array($username, $password));

		/*
		 * Check each of the results to see if a valid user ID was returned. and use the
		 * first ID to log into the system.

		 * Any errors raised in the plugin should be returned via the JAuthenticateResponse
		 * and handled appropriately.
		 */
		foreach($results as $result)
		{
			switch($result->status)
			{
				case JAUTHENTICATE_STATUS_SUCCESS :
				{
					if(empty($result->username)) {
						$result->username = $username;
					}

					if(empty($result->fullname)) {
						$result->fullname = $username;
					}

					//TODO :: this needs to be changed, should only return at the end
					return $result;

				}	break;

				case JAUTHENTICATE_STATUS_CANCEL :
				{
					// do nothing
				} break;

				case JAUTHENTICATE_STATUS_FAILURE :
				{
					//do nothing
				}	break;
			}
		}

		return false;
	}

	/**
	 * Static method to load an auth plugin and attach it to the JEventDispatcher
	 * object.
	 *
	 * This method should be invoked as:
	 * 		<pre>  $isLoaded = JAuthenticate::loadPlugin($plugin, $subject);</pre>
	 *
	 * @access public
	 * @static
	 * @param string $plugin The authentication plugin to use.
	 * @param object $subject Observable object for the plugin to observe
	 * @return boolean True if plugin is loaded
	 * @since 1.5
	 */
	function loadPlugin($plugin, & $subject)
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$plugin])) {

			if(JPluginHelper::importPlugin('authentication', $plugin)) {
				// Build authentication plugin classname
				$name = 'JAuthenticate'.$plugin;
				$instances[$plugin] = new $name ($subject);
			}
		}
		return is_object($instances[$plugin]);
	}
}


/**
 * Authorization helper class, provides static methods to perform various tasks relevant
 * to the Joomla authorization routines
 *
 * This class has influences and some method logic from the Horde Auth package
 *
 * @static
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	User
 * @since		1.5
 */
class JAuthenticateHelper
{

	/**
	 * Formats a password using the current encryption.
	 *
	 * @access	public
	 * @param	string	$plaintext	The plaintext password to encrypt.
	 * @param	string	$salt		The salt to use to encrypt the password. []
	 *								If not present, a new salt will be
	 *								generated.
	 * @param	string	$encryption	The kind of pasword encryption to use.
	 *								Defaults to md5-hex.
	 * @param	boolean	$show_encrypt  Some password systems prepend the kind of
	 *								encryption to the crypted password ({SHA},
	 *								etc). Defaults to false.
	 *
	 * @return string  The encrypted password.
	 */
	function getCryptedPassword($plaintext, $salt = '', $encryption = 'md5-hex', $show_encrypt = false)
	{
		/*
		 * Get the salt to use.
		 */
		$salt = JAuthenticateHelper::getSalt($encryption, $salt, $plaintext);

		/*
		 * Encrypt the password.
		 */
		switch ($encryption) {
			case 'plain' :
				return $plaintext;

			case 'sha' :
				$encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext));
				return ($show_encrypt) ? '{SHA}'.$encrypted : $encrypted;

			case 'crypt' :
			case 'crypt-des' :
			case 'crypt-md5' :
			case 'crypt-blowfish' :
				return ($show_encrypt ? '{crypt}' : '').crypt($plaintext, $salt);

			case 'md5-base64' :
				$encrypted = base64_encode(mhash(MHASH_MD5, $plaintext));
				return ($show_encrypt) ? '{MD5}'.$encrypted : $encrypted;

			case 'ssha' :
				$encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext.$salt).$salt);
				return ($show_encrypt) ? '{SSHA}'.$encrypted : $encrypted;

			case 'smd5' :
				$encrypted = base64_encode(mhash(MHASH_MD5, $plaintext.$salt).$salt);
				return ($show_encrypt) ? '{SMD5}'.$encrypted : $encrypted;

			case 'aprmd5' :
				$length = strlen($plaintext);
				$context = $plaintext.'$apr1$'.$salt;
				$binary = JAuthenticateHelper::_bin(md5($plaintext.$salt.$plaintext));

				for ($i = $length; $i > 0; $i -= 16) {
					$context .= substr($binary, 0, ($i > 16 ? 16 : $i));
				}
				for ($i = $length; $i > 0; $i >>= 1) {
					$context .= ($i & 1) ? chr(0) : $plaintext[0];
				}

				$binary = JAuthenticateHelper::_bin(md5($context));

				for ($i = 0; $i < 1000; $i ++) {
					$new = ($i & 1) ? $plaintext : substr($binary, 0, 16);
					if ($i % 3) {
						$new .= $salt;
					}
					if ($i % 7) {
						$new .= $plaintext;
					}
					$new .= ($i & 1) ? substr($binary, 0, 16) : $plaintext;
					$binary = JAuthenticateHelper::_bin(md5($new));
				}

				$p = array ();
				for ($i = 0; $i < 5; $i ++) {
					$k = $i +6;
					$j = $i +12;
					if ($j == 16) {
						$j = 5;
					}
					$p[] = JAuthenticateHelper::_toAPRMD5((ord($binary[$i]) << 16) | (ord($binary[$k]) << 8) | (ord($binary[$j])), 5);
				}

				return '$apr1$'.$salt.'$'.implode('', $p).JAuthenticateHelper::_toAPRMD5(ord($binary[11]), 3);

			case 'md5-hex' :
			default :
				return ($show_encrypt) ? '{MD5}'.md5($plaintext) : md5($plaintext);
		}
	}

	/**
	 * Returns a salt for the appropriate kind of password encryption.
	 * Optionally takes a seed and a plaintext password, to extract the seed
	 * of an existing password, or for encryption types that use the plaintext
	 * in the generation of the salt.
	 *
	 * @access public
	 * @param string $encryption  The kind of pasword encryption to use.
	 *							Defaults to md5-hex.
	 * @param string $seed		The seed to get the salt from (probably a
	 *							previously generated password). Defaults to
	 *							generating a new seed.
	 * @param string $plaintext   The plaintext password that we're generating
	 *							a salt for. Defaults to none.
	 *
	 * @return string  The generated or extracted salt.
	 */
	function getSalt($encryption = 'md5-hex', $seed = '', $plaintext = '')
	{
		// Encrypt the password.
		switch ($encryption) {
			case 'crypt' :
			case 'crypt-des' :
				if ($seed) {
					return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 2);
				} else {
					return substr(md5(mt_rand()), 0, 2);
				}

			case 'crypt-md5' :
				if ($seed) {
					return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 12);
				} else {
					return '$1$'.substr(md5(mt_rand()), 0, 8).'$';
				}

			case 'crypt-blowfish' :
				if ($seed) {
					return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 16);
				} else {
					return '$2$'.substr(md5(mt_rand()), 0, 12).'$';
				}

			case 'ssha' :
				if ($seed) {
					return substr(preg_replace('|^{SSHA}|', '', $seed), -20);
				} else {
					return mhash_keygen_s2k(MHASH_SHA1, $plaintext, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
				}

			case 'smd5' :
				if ($seed) {
					return substr(preg_replace('|^{SMD5}|', '', $seed), -16);
				} else {
					return mhash_keygen_s2k(MHASH_MD5, $plaintext, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
				}

			case 'aprmd5' :
				/* 64 characters that are valid for APRMD5 passwords. */
				$APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

				if ($seed) {
					return substr(preg_replace('/^\$apr1\$(.{8}).*/', '\\1', $seed), 0, 8);
				} else {
					$salt = '';
					for ($i = 0; $i < 8; $i ++) {
						$salt .= $APRMD5 {
							rand(0, 63)
							};
					}
					return $salt;
				}

			default :
				return '';
		}
	}

	/**
	 * Generate a random password
	 *
	 * @static
	 * @param	int		$length	Length of the password to generate
	 * @return	string			Random Password
	 * @since	1.5
	 */
	function genRandomPassword($length = 8)
	{
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$makepass = '';
		mt_srand(10000000 * (double) microtime());

		for ($i = 0; $i < $length; $i ++) {
			$makepass .= $salt[mt_rand(0, $len -1)];
		}

		return $makepass;
	}

	/**
	 * Converts to allowed 64 characters for APRMD5 passwords.
	 *
	 * @access private
	 * @param string  $value
	 * @param integer $count
	 * @return string  $value converted to the 64 MD5 characters.
	 * @since 1.5
	 */
	function _toAPRMD5($value, $count)
	{
		/* 64 characters that are valid for APRMD5 passwords. */
		$APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

		$aprmd5 = '';
		$count = abs($count);
		while (-- $count) {
			$aprmd5 .= $APRMD5[$value & 0x3f];
			$value >>= 6;
		}
		return $aprmd5;
	}

	/**
	 * Converts hexadecimal string to binary data.
	 *
	 * @access private
	 * @param string $hex  Hex data.
	 * @return string  Binary data.
	 * @since 1.5
	 */
	function _bin($hex)
	{
		$bin = '';
		$length = strlen($hex);
		for ($i = 0; $i < $length; $i += 2) {
			$tmp = sscanf(substr($hex, $i, 2), '%x');
			$bin .= chr(array_shift($tmp));
		}
		return $bin;
	}
}

/**
 * Authorization response class, provides an object for storing user and error details
 *
 * @author 		Samuel Moffatt <sam.moffatt@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	User
 * @since		1.5
 */
class JAuthenticateResponse extends JObject
{
	/**
	 * User type (refers to the authentication method used)
	 *
	 * @var name string
	 * @access public
	 */
	var $type	= '';

	/**
	 * Response status (see status codes)
	 *
	 * @var type string
	 * @access public
	 */
	var $status 		= 4;

	/**
	 *  The error message
	 *
	 * @var error_message string
	 * @access public
	 */
	var $error_message 	= '';

	/**
	 * Any UTF-8 string that the End User wants to use as a username.
	 *
	 * @var fullname string
	 * @access public
	 */
	var $username 		= '';

	/**
	 * The email address of the End User as specified in section 3.4.1 of [RFC2822]
	 *
	 * @var email string
	 * @access public
	 */
	var $email			= '';

	/**
	 * UTF-8 string free text representation of the End User's full name.
	 *
	 * @var fullname string
	 * @access public
	 */
	var $fullname 		= '';

	/**
	 * The End User's date of birth as YYYY-MM-DD. Any values whose representation uses
	 * fewer than the specified number of digits should be zero-padded. The length of this
	 * value MUST always be 10. If the End User user does not want to reveal any particular
	 * component of this value, it MUST be set to zero.
	 *
	 * For instance, if a End User wants to specify that his date of birth is in 1980, but
	 * not the month or day, the value returned SHALL be "1980-00-00".
	 *
	 * @var fullname string
	 * @access public
	 */
	var $birthdate	 	= '';

	/**
	 * The End User's gender, "M" for male, "F" for female.
	 *
	 * @var fullname string
	 * @access public
	 */
	var $gender 		= '';

	/**
	 * UTF-8 string free text that SHOULD conform to the End User's country's postal system.
	 *
	 * @var fullname string
	 * @access public
	 */
	var $postcode 		= '';

	/**
	 * The End User's country of residence as specified by ISO3166.
	 *
	 * @var fullname string
	 * @access public
	 */
	var $country 		= '';

	/**
	 * End User's preferred language as specified by ISO639.
	 *
	 * @var fullname string
	 * @access public
	 */
	var $language 		= '';

	/**
	 * ASCII string from TimeZone database
	 *
	 * @var fullname string
	 * @access public
	 */
	var $timezone 		= '';

	/**
	 * Constructor
	 *
	 * @param string $name The type of the response
	 * @since 1.5
	 */
	function __construct($type) {
		$this->type = $type;
	}
}
?>
