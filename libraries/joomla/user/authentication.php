<?php
/**
 * @package     Joomla.Platform
 * @subpackage  User
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.observable');

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
 * Authenthication class, provides an interface for the Joomla authentication system
 *
 * @package     Joomla.Platform
 * @subpackage  User
 * @since       11.1
 */
class JAuthentication extends JObservable
{
	/**
	 * Constructor
	 *
	 * @since 11.1
	 */
	public function __construct()
	{
		$isLoaded = JPluginHelper::importPlugin('authentication');

		if (!$isLoaded) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('JLIB_USER_ERROR_AUTHENTICATION_LIBRARIES'));
		}
	}

	/**
	 * Returns the global authentication object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return  JAuthentication object
	 * @since   11.1
	 */
	public static function getInstance()
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[0])) {
			$instances[0] = new JAuthentication();
		}

		return $instances[0];
	}

	/**
	 * Finds out if a set of login credentials are valid by asking all obvserving
	 * objects to run their respective authentication routines.
	 *
	 * @param   array  $credentials  Array holding the user credentials
	 * @param   array  $options      Array of options
	 *
	 * @return  mixed  Integer userid for valid user if credentials are valid or
	 *                  boolean false if they are not
	 * @since   11.1
	 * @see     JAuthenticationResponse
	 */
	public function authenticate($credentials, $options)
	{
		// Initialise variables.
		$auth = false;

		// Get plugins
		$plugins = JPluginHelper::getPlugin('authentication');

		// Create authencication response
		$response = new JAuthenticationResponse();

		/*
		 * Loop through the plugins and check of the creditials can be used to authenticate
		 * the user
		 *
		 * Any errors raised in the plugin should be returned via the JAuthenticationResponse
		 * and handled appropriately.
		 */
		foreach ($plugins as $plugin)
		{
			$className = 'plg'.$plugin->type.$plugin->name;
			if (class_exists($className)) {
				$plugin = new $className($this, (array)$plugin);
			}
			else {
				// Bail here if the plugin can't be created
				JError::raiseWarning(50, JText::sprintf('JLIB_USER_ERROR_AUTHENTICATION_FAILED_LOAD_PLUGIN', $className));
				continue;
			}

			// Try to authenticate
			$plugin->onUserAuthenticate($credentials, $options, $response);

			// If authentication is successful break out of the loop
			if ($response->status === JAUTHENTICATE_STATUS_SUCCESS)
			{
				if (empty($response->type)) {
					$response->type = isset($plugin->_name) ? $plugin->_name : $plugin->name;
				}
				if (empty($response->username)) {
					$response->username = $credentials['username'];
				}

				if (empty($response->fullname)) {
					$response->fullname = $credentials['username'];
				}

				if (empty($response->password)) {
					$response->password = $credentials['password'];
				}

				break;
			}
		}
		return $response;
	}
}

/**
 * Authorisation response class, provides an object for storing user and error details
 *
 * @package     Joomla.Platform
 * @subpackage  User
 * @since       11.1
 */
class JAuthenticationResponse extends JObject
{
	/**
	 * Response status (see status codes)
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $status		= JAUTHENTICATE_STATUS_FAILURE;

	/**
	 * The type of authentication that was successful
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type		= '';

	/**
	 *  The error message
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $error_message	= '';

	/**
	 * Any UTF-8 string that the End User wants to use as a username.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $username		= '';

	/**
	 * Any UTF-8 string that the End User wants to use as a password.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $password		= '';

	/**
	 * The email address of the End User as specified in section 3.4.1 of [RFC2822]
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $email			= '';

	/**
	 * UTF-8 string free text representation of the End User's full name.
	 *
	 * @var    string
	 * @since  11.1
	 *
	 */
	public $fullname		= '';

	/**
	 * The End User's date of birth as YYYY-MM-DD. Any values whose representation uses
	 * fewer than the specified number of digits should be zero-padded. The length of this
	 * value MUST always be 10. If the End User user does not want to reveal any particular
	 * component of this value, it MUST be set to zero.
	 *
	 * For instance, if a End User wants to specify that his date of birth is in 1980, but
	 * not the month or day, the value returned SHALL be "1980-00-00".
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $birthdate		= '';

	/**
	 * The End User's gender, "M" for male, "F" for female.
	 *
	 * @var  string
	 * @since  11.1
	 */
	public $gender		= '';

	/**
	 * UTF-8 string free text that SHOULD conform to the End User's country's postal system.
	 *
	 * @var postcode string
	 * @since  11.1
	 */
	public $postcode		= '';

	/**
	 * The End User's country of residence as specified by ISO3166.
	 *
	 * @var string
	 * @since  11.1
	 */
	public $country		= '';

	/**
	 * End User's preferred language as specified by ISO639.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $language		= '';

	/**
	 * ASCII string from TimeZone database
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $timezone		= '';

	/**
	 * Constructor
	 *
	 * @param   string  $name  The type of the response
	 *
	 * @since   11.1
	 */
	function __construct()
	{
	}
}
