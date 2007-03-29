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

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

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

		$plugins =& JPluginHelper::getPlugin('authentication');

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
					jimport('joomla.utilities.log');
					$log = JLog::getInstance();
					$errorlog = array();
					$errorlog['status'] = $result->type . " CANCELED: ";
					$errorlog['comment'] = $result->error_message;
					$log->addEntry($errorlog);
					// do nothing
				} break;

				case JAUTHENTICATE_STATUS_FAILURE :
				{
					jimport('joomla.utilities.log');
					$log = JLog::getInstance();
					$errorlog = array();
					$errorlog['status'] = $result->type . " FAILURE: ";
					$errorlog['comment'] = $result->error_message;
					$log->addEntry($errorlog);
					//do nothing
				}	break;

				default :
				{
					jimport('joomla.utilities.log');
					$log = JLog::getInstance();
					$errorlog = array();
					$errorlog['status'] = $result->type . " UNKNOWN ERROR: ";
					$errorlog['comment'] = $result->error_message;
					$log->addEntry($errorlog);
					//do nothing
				}	break;
			}
		}

		$dispatcher->trigger( 'onAuthenticateFailure', array( array( $username, $password ), $results ) );
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
				$name = 'plgAuthenticate'.$plugin;
				$instances[$plugin] = new $name ($subject);
			}
		}
		return is_object($instances[$plugin]);
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
