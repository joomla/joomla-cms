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

/**
 * Authorization class, provides an interface for the Joomla authentication
 * system
 *
 * @author Louis Landry <louis@webimagery.net>
 * @static
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
class JAuth extends JObject {

	/**
	 * Constructor
	 * 
	 * @access protected
	 */
	function __construct() {
		
		// Get the global event dispatcher to load the plugins
		$dispatcher = &JEventDispatcher :: getInstance();
		
		//TODO: Here is where we will load all the necessary plugins into an array
		$plugins[] = 'joomla'; // JAuth_Joomla.php
		
		foreach ($plugins as $plugin) {
			$isLoaded |= JAuthHelper::loadPlugin($plugin, $dispatcher);
		}

		if (!$isLoaded) {
			JError :: raiseWarning('SOME_ERROR_CODE', 'JAuth::__constructor: Could not load authentication libraries.', $plugins);
		}
	}

	/**
	 * JAuth Login Method
	 * 
	 * Username and Password are sent as credentials (along with other possibilities) 
	 * to each observer (JAuthPlugin) for user validation.  Successful validation will 
	 * update the current session with the user details
	 * 
	 * Credentials Array
	 * 	['username']
	 * 	['password']
	 * 	['userid']	*optional
	 * 	['realm']	*optional
	 * 
	 * @access public
	 * @param array $credentials Array of credentials to authenticate
	 * @return boolean True on success
	 * @since 1.1
	 */
	function login($credentials) 
	{
		global $mainframe;

		// Get the global event dispatcher object
		$dispatcher = &JEventDispatcher :: getInstance();
		
		// Get the global database connector object
		$db = $mainframe->getDBO();
		
		// This is less than stellar, the login details should be passed to the login method
		// or an error should be returned
		if (empty($credentials['username']) || empty($credentials['password'])) {
			$credentials['username'] = $db->getEscaped(trim(mosGetParam($_POST, 'username', '')));
			$credentials['password'] = $db->getEscaped(trim(mosGetParam($_POST, 'passwd', '')));
			$bypost = 1;
		}
		
		// In particular... this error :)
		if (empty($credentials['username']) || empty($credentials['password'])) {
			// Error check if still no username or password values
			echo "<script> alert(\"".JText :: _('LOGIN_INCOMPLETE', true)."\"); </script>\n";
			mosRedirect(mosGetParam($_POST, 'return', '/'));
			exit ();
		} else {

			$authenticated = $this->authenticate($credentials);

			if ($authenticated !== false) {
				// Credentials authenticated
				

				// OK, the credentials are authenticated.  Lets fire the onLogin event
				$results = $dispatcher->dispatch( 'onLogin', $credentials);
				
				/*
				 * If any of the authentication plugins did not successfully complete the login
				 * routine then the whole method fails.  Any errors raised should be done in 
				 * the plugin as this provides the ability to provide much more information 
				 * about why the routine may have failed.
				 */
				if (!in_array(false, $results)) {


					// Create a new user model and load the authenticated userid
					$user = new mosUser($db);
					$user->load(intval($authenticated));
	
					// If the user is blocked, redirect with an error
					if ($user->block == 1) {
						echo "<script>alert(\"".JText :: _('LOGIN_BLOCKED', true)."\"); </script>\n";
						mosRedirect(mosGetParam($_POST, 'return', '/'));
						exit ();
					}
					
					// Fudge the ACL stuff for now...
					// TODO: Implement ACL :)
					$acl = &JFactory :: getACL();
					$grp = $acl->getAroGroup($user->id);
					$row->gid = 1;
	
					if ($acl->is_group_child_of($grp->name, 'Registered', 'ARO') || $acl->is_group_child_of($grp->name, 'Public Backend', 'ARO')) {
						// fudge Authors, Editors, Publishers and Super Administrators into the Special Group
						$user->gid = 2;
					}
					$user->usertype = $grp->name;
	
					// access control check
					//if ( !$acl->acl_check( 'login', $this->_client, 'users', $user->usertype ) ) {
					//	return false;
					//}
	
					// Register the needed session variables
					JSession :: set('guest', 0);
					JSession :: set('username', $user->username);
					JSession :: set('userid', intval($user->id));
					JSession :: set('usertype', $user->usertype);
					JSession :: set('gid', intval($user->gid));
	
					// Register session variables to prevent spoofing
					JSession :: set('JAuth_RemoteAddr', $_SERVER['REMOTE_ADDR']);
					JSession :: set('JAuth_UserAgent', $_SERVER['HTTP_USER_AGENT']);
	
					// TODO: JRegistry will make this unnecessary
					// Get the session object
					$session = & $mainframe->_session;
	
					$session->guest = 0;
					$session->username = $user->username;
					$session->userid = intval($user->id);
					$session->usertype = $user->usertype;
					$session->gid = intval($user->gid);
	
					$session->update();
	
					// Hit the user last visit field
					$user->setLastVisit();
	
					// TODO: If we aren't going to use the database session we need to fix this
					// Set remember me option
					$remember = trim(mosGetParam($_POST, 'remember', ''));
					if ($remember == 'yes') {
						$session->remember($user->username, $user->password);
					}
	
					// Clean the cache for this user
					$cache = JFactory :: getCache();
					$cache->cleanCache();
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Logs a user out by asking all obvserving objects to run their respective 
	 * logout routines.
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.1
	 */
	function logout() 
	{
		global $mainframe;
		
		// Initialize variables
		$retval = false;

		// Get the global event dispatcher object
		$dispatcher = &JEventDispatcher :: getInstance();
		
		// Get a user object from the JApplication
		$user = $mainframe->getUser();
		
		// Build the credentials array
		$credentials['username'] = $user->username;
		$credentials['password'] = $user->password;

		// OK, the credentials are built. Lets fire the onLogout event
		$results = $dispatcher->dispatch( 'onLogout', $credentials);
		
		/*
		 * If any of the authentication plugins did not successfully complete the logout
		 * routine then the whole method fails.  Any errors raised should be done in 
		 * the plugin as this provides the ability to provide much more information 
		 * about why the routine may have failed.
		 */
		if (!in_array(false, $results)) {

			// Clean the cache for this user
			$cache = JFactory :: getCache();
			$cache->cleanCache();
	
			// TODO: JRegistry will make this unnecessary
			// Get the session object
			$session =& $mainframe->_session;
			$session->destroy();
	
			// Destroy the session for this user
			JSession::destroy();
			
			$retval = true;
		}
		return $retval;
	}

	/**
	 * Finds out if a set of login credentials are valid by asking all obvserving 
	 * objects to run their respective authentication routines.
	 *
	 * @access public
	 * @param array $credentials  The credentials to authenticate.
	 * @return mixed Integer userid for valid user if credentials are valid or boolean false if they are not
	 * @since 1.1
	 */
	function authenticate($credentials) 
	{
		// Initialize variables
		$auth = false;

		// Get the global event dispatcher object
		$dispatcher = &JEventDispatcher :: getInstance();
		
		// Time to authenticate the credentials.  Lets fire the auth event
		$results = $dispatcher->dispatch( 'auth', $credentials);

		/*
		 * If any of the authentication plugins did not authenticate the credentials
		 * then the whole method fails.  Any errors raised should be done in the plugin
		 * as this provides the ability to provide much more information about why
		 * authentication may have failed.
		 */
		if (!in_array(false, $results)) {

			// TODO: Perhaps we should check that all returned userids are the same?
			/*
			 * Since none of authentication plugins failed get the userid of the
			 * authenticated user
			 */ 
			$auth = $results[0];
		}
		return $auth;
	}
	
	/**
	 * Returns a reference to a global authentication object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $auth = &JAuth::getInstance();</pre>
	 *
	 * @static
	 * @access public
	 * @return object The global JAuth object
	 * @since 1.1
	 */
	function & getInstance() 
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[0])) {
			$instances[0] = new JAuth();
		}

		return $instances[0];
	}
}


/**
 * Authorization helper class, provides static methods to perform various tasks relevant
 * to the Joomla authorization routines
 *
 * This module has influences and some method logic from the Horde Auth package
 *
 * @author Louis Landry <louis@webimagery.net>
 * @static
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
class JAuthHelper {

	/**
	 * Formats a password using the current encryption.
	 *
	 * @access public
	 * @param string $plaintext The plaintext password to encrypt.
	 * @param string $salt  The salt to use to encrypt the password. []
	 *                               If not present, a new salt will be
	 *                               generated.
	 * @param string $encryption     The kind of pasword encryption to use.
	 *                               Defaults to md5-hex.
	 * @param boolean $show_encrypt  Some password systems prepend the kind of
	 *                               encryption to the crypted password ({SHA},
	 *                               etc). Defaults to false.
	 *
	 * @return string  The encrypted password.
	 */
	function getCryptedPassword($plaintext, $salt = '', $encryption = 'md5-hex', $show_encrypt = false) 
	{
		/* 
		 * Get the salt to use.
		 */
		$salt = JAuthHelper :: getSalt($encryption, $salt, $plaintext);

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
				$binary = JAuthHelper :: _bin(md5($plaintext.$salt.$plaintext));

				for ($i = $length; $i > 0; $i -= 16) {
					$context .= substr($binary, 0, ($i > 16 ? 16 : $i));
				}
				for ($i = $length; $i > 0; $i >>= 1) {
					$context .= ($i & 1) ? chr(0) : $plaintext[0];
				}

				$binary = JAuthHelper :: _bin(md5($context));

				for ($i = 0; $i < 1000; $i ++) {
					$new = ($i & 1) ? $plaintext : substr($binary, 0, 16);
					if ($i % 3) {
						$new .= $salt;
					}
					if ($i % 7) {
						$new .= $plaintext;
					}
					$new .= ($i & 1) ? substr($binary, 0, 16) : $plaintext;
					$binary = JAuthHelper :: _bin(md5($new));
				}

				$p = array ();
				for ($i = 0; $i < 5; $i ++) {
					$k = $i +6;
					$j = $i +12;
					if ($j == 16) {
						$j = 5;
					}
					$p[] = JAuthHelper :: _toAPRMD5((ord($binary[$i]) << 16) | (ord($binary[$k]) << 8) | (ord($binary[$j])), 5);
				}

				return '$apr1$'.$salt.'$'.implode('', $p).JAuthHelper :: _toAPRMD5(ord($binary[11]), 3);

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
	 *                            Defaults to md5-hex.
	 * @param string $seed        The seed to get the salt from (probably a
	 *                            previously generated password). Defaults to
	 *                            generating a new seed.
	 * @param string $plaintext   The plaintext password that we're generating
	 *                            a salt for. Defaults to none.
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
	 * @access public
	 * @return string Random Password
	 * @since 1.1
	 */
	function genRandomPassword() 
	{
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$makepass = '';
		mt_srand(10000000 * (double) microtime());

		for ($i = 0; $i < 8; $i ++) {
			$makepass .= $salt[mt_rand(0, $len -1)];
		}

		return $makepass;
	}

	/**
	 * Is there an authenticated user in the current session
	 * 
	 * @access public
	 * @return boolean True of authenticated user exists.
	 * @since 1.1
	 */
	function isAuthenticated() 
	{
		// Initialize variables
		$ret = false;

		// TODO: Check logic on this... i assume this will work for an authentication check

		/*
		 * If the session 'guest' variable is zero and the session 'userid' variable 
		 * is set, we would assume that a valid user is logged in
		 */
		if (JSession :: get('guest') == 0 && !JSession :: get('userid') != null) {
			$ret = true;
		}
		
		/*
		 * This ensures that the IP of the client does not change from page request to 
		 * page request while the user is authenticated.
		 * 
		 * Useful to protect against spoofing
		 */
		if (!JAuthHelper::_checkRemoteAddr()) {
			$ret = false;
		}

		/*
		 * This ensures that the User Agent string of the client does not change from page request to 
		 * page request while the user is authenticated.
		 * 
		 * Useful to protect against spoofing
		 */
		if (!JAuthHelper::_checkUserAgent()) {
			$ret = false;
		}

		return false;
	}

	/**
	 * Performs check on session to see if IP Address has changed since the
	 * last access.
	 *
	 * @access private
	 * @return boolean  True if IP Address is the same as the last access.
	 * @since 1.1
	 */
	function _checkRemoteAddr() {
		return (JSession :: get('JAuth_RemoteAddr') == $_SERVER['REMOTE_ADDR']);
	}

	/**
	 * Performs check on session to see if user agent has changed since
	 * the last access.
	 *
	 * @access private
	 * @return boolean  True if browser user agent is the same as the last access.
	 * @since 1.1
	 */
	function _checkUserAgent() {
		return (JSession :: get('JAuth_UserAgent') == $_SERVER['HTTP_USER_AGENT']);
	}

	/**
	 * Converts to allowed 64 characters for APRMD5 passwords.
	 *
	 * @access private
	 * @param string  $value
	 * @param integer $count
	 * @return string  $value converted to the 64 MD5 characters.
	 * @since 1.1
	 */
	function _toAPRMD5($value, $count) {
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
	 * @since 1.1
	 */
	function _bin($hex) {
		$bin = '';
		$length = strlen($hex);
		for ($i = 0; $i < $length; $i += 2) {
			$tmp = sscanf(substr($hex, $i, 2), '%x');
			$bin .= chr(array_shift($tmp));
		}
		return $bin;
	}

	/**
	 * Static method to load an auth plugin and attach it to the JEventDispatcher 
	 * object.
	 *
	 * This method should be invoked as:
	 * 		<pre>  $isLoaded = JAuthHelper::loadPlugin($plugin, $subject);</pre>
	 *
	 * @access public
	 * @static
	 * @param string $plugin The authentication plugin to use.
	 * @param object $subject Observable object for the plugin to observe
	 * @return boolean True if plugin is loaded
	 * @since 1.1
	 */
	function loadPlugin($plugin, & $subject) 
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$plugin])) {
			// Build the path to the needed authentication plugin
			$path = JPATH_SITE.DS.'plugins'.DS.'auth'.DS.$plugin.'.php';
			
			// Require plugin file
			require_once($path);
			
			// Build authentication plugin classname
			$name = 'JAuth'.$plugin;
			$instances[$plugin] = new $name ($subject);
		}
		return is_object($instances[$plugin]);
	}
}
?>