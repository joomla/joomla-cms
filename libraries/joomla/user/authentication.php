<?php
/**
 * @package     Joomla.Platform
 * @subpackage  User
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Authentication class, provides an interface for the Joomla authentication system
 *
 * @since  11.1
 */
class JAuthentication extends JObject
{
	// Shared success status
	/**
	 * This is the status code returned when the authentication is success (permit login)
	 * @const  STATUS_SUCCESS successful response
	 * @since  11.2
	 */
	const STATUS_SUCCESS = 1;

	// These are for authentication purposes (username and password is valid)
	/**
	 * Status to indicate cancellation of authentication (unused)
	 * @const  STATUS_CANCEL cancelled request (unused)
	 * @since  11.2
	 */
	const STATUS_CANCEL = 2;

	/**
	 * This is the status code returned when the authentication failed (prevent login if no success)
	 * @const  STATUS_FAILURE failed request
	 * @since  11.2
	 */
	const STATUS_FAILURE = 4;

	// These are for authorisation purposes (can the user login)
	/**
	 * This is the status code returned when the account has expired (prevent login)
	 * @const  STATUS_EXPIRED an expired account (will prevent login)
	 * @since  11.2
	 */
	const STATUS_EXPIRED = 8;

	/**
	 * This is the status code returned when the account has been denied (prevent login)
	 * @const  STATUS_DENIED denied request (will prevent login)
	 * @since  11.2
	 */
	const STATUS_DENIED = 16;

	/**
	 * This is the status code returned when the account doesn't exist (not an error)
	 * @const  STATUS_UNKNOWN unknown account (won't permit or prevent login)
	 * @since  11.2
	 */
	const STATUS_UNKNOWN = 32;

	/**
	 * An array of Observer objects to notify
	 *
	 * @var    array
	 * @since  12.1
	 */
	protected $observers = array();

	/**
	 * The state of the observable object
	 *
	 * @var    mixed
	 * @since  12.1
	 */
	protected $state = null;

	/**
	 * A multi dimensional array of [function][] = key for observers
	 *
	 * @var    array
	 * @since  12.1
	 */
	protected $methods = array();

	/**
	 * @var    JAuthentication  JAuthentication instances container.
	 * @since  11.3
	 */
	protected static $instance;

	/**
	 * Constructor
	 *
	 * @since   11.1
	 */
	public function __construct()
	{
		$isLoaded = JPluginHelper::importPlugin('authentication');

		if (!$isLoaded)
		{
			JLog::add(JText::_('JLIB_USER_ERROR_AUTHENTICATION_LIBRARIES'), JLog::WARNING, 'jerror');
		}
	}

	/**
	 * Returns the global authentication object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return  JAuthentication  The global JAuthentication object
	 *
	 * @since   11.1
	 */
	public static function getInstance()
	{
		if (empty(self::$instance))
		{
			self::$instance = new JAuthentication;
		}

		return self::$instance;
	}

	/**
	 * Get the state of the JAuthentication object
	 *
	 * @return  mixed    The state of the object.
	 *
	 * @since   11.1
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Attach an observer object
	 *
	 * @param   object  $observer  An observer object to attach
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function attach($observer)
	{
		if (is_array($observer))
		{
			if (!isset($observer['handler']) || !isset($observer['event']) || !is_callable($observer['handler']))
			{
				return;
			}

			// Make sure we haven't already attached this array as an observer
			foreach ($this->observers as $check)
			{
				if (is_array($check) && $check['event'] == $observer['event'] && $check['handler'] == $observer['handler'])
				{
					return;
				}
			}

			$this->observers[] = $observer;
			end($this->observers);
			$methods = array($observer['event']);
		}
		else
		{
			if (!($observer instanceof JAuthentication))
			{
				return;
			}

			// Make sure we haven't already attached this object as an observer
			$class = get_class($observer);

			foreach ($this->observers as $check)
			{
				if ($check instanceof $class)
				{
					return;
				}
			}

			$this->observers[] = $observer;
			$methods = array_diff(get_class_methods($observer), get_class_methods('JPlugin'));
		}

		$key = key($this->observers);

		foreach ($methods as $method)
		{
			$method = strtolower($method);

			if (!isset($this->methods[$method]))
			{
				$this->methods[$method] = array();
			}

			$this->methods[$method][] = $key;
		}
	}

	/**
	 * Detach an observer object
	 *
	 * @param   object  $observer  An observer object to detach.
	 *
	 * @return  boolean  True if the observer object was detached.
	 *
	 * @since   11.1
	 */
	public function detach($observer)
	{
		$retval = false;

		$key = array_search($observer, $this->observers);

		if ($key !== false)
		{
			unset($this->observers[$key]);
			$retval = true;

			foreach ($this->methods as &$method)
			{
				$k = array_search($key, $method);

				if ($k !== false)
				{
					unset($method[$k]);
				}
			}
		}

		return $retval;
	}

	/**
	 * Finds out if a set of login credentials are valid by asking all observing
	 * objects to run their respective authentication routines.
	 *
	 * @param   array  $credentials  Array holding the user credentials.
	 * @param   array  $options      Array holding user options.
	 *
	 * @return  JAuthenticationResponse  Response object with status variable filled in for last plugin or first successful plugin.
	 *
	 * @see     JAuthenticationResponse
	 * @since   11.1
	 */
	public function authenticate($credentials, $options = array())
	{
		// Get plugins
		$plugins = JPluginHelper::getPlugin('authentication');

		// Create authentication response
		$response = new JAuthenticationResponse;

		/*
		 * Loop through the plugins and check if the credentials can be used to authenticate
		 * the user
		 *
		 * Any errors raised in the plugin should be returned via the JAuthenticationResponse
		 * and handled appropriately.
		 */
		foreach ($plugins as $plugin)
		{
			$className = 'plg' . $plugin->type . $plugin->name;

			if (class_exists($className))
			{
				$plugin = new $className($this, (array) $plugin);
			}
			else
			{
				// Bail here if the plugin can't be created
				JLog::add(JText::sprintf('JLIB_USER_ERROR_AUTHENTICATION_FAILED_LOAD_PLUGIN', $className), JLog::WARNING, 'jerror');
				continue;
			}

			// Try to authenticate
			$plugin->onUserAuthenticate($credentials, $options, $response);

			// If authentication is successful break out of the loop
			if ($response->status === self::STATUS_SUCCESS)
			{
				if (empty($response->type))
				{
					$response->type = isset($plugin->_name) ? $plugin->_name : $plugin->name;
				}

				break;
			}
		}

		if (empty($response->username))
		{
			$response->username = $credentials['username'];
		}

		if (empty($response->fullname))
		{
			$response->fullname = $credentials['username'];
		}

		if (empty($response->password) && isset($credentials['password']))
		{
			$response->password = $credentials['password'];
		}

		return $response;
	}

	/**
	 * Authorises that a particular user should be able to login
	 *
	 * @param   JAuthenticationResponse  $response  response including username of the user to authorise
	 * @param   array                    $options   list of options
	 *
	 * @return  array[JAuthenticationResponse]  results of authorisation
	 *
	 * @since  11.2
	 */
	public static function authorise($response, $options = array())
	{
		// Get plugins in case they haven't been imported already
		JPluginHelper::importPlugin('user');

		JPluginHelper::importPlugin('authentication');
		$dispatcher = JEventDispatcher::getInstance();
		$results = $dispatcher->trigger('onUserAuthorisation', array($response, $options));

		return $results;
	}
}

/**
 * Authentication response class, provides an object for storing user and error details
 *
 * @since  11.1
 */
class JAuthenticationResponse
{
	/**
	 * Response status (see status codes)
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $status = JAuthentication::STATUS_FAILURE;

	/**
	 * The type of authentication that was successful
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = '';

	/**
	 *  The error message
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $error_message = '';

	/**
	 * Any UTF-8 string that the End User wants to use as a username.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $username = '';

	/**
	 * Any UTF-8 string that the End User wants to use as a password.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $password = '';

	/**
	 * The email address of the End User as specified in section 3.4.1 of [RFC2822]
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $email = '';

	/**
	 * UTF-8 string free text representation of the End User's full name.
	 *
	 * @var    string
	 * @since  11.1
	 *
	 */
	public $fullname = '';

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
	public $birthdate = '';

	/**
	 * The End User's gender, "M" for male, "F" for female.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $gender = '';

	/**
	 * UTF-8 string free text that SHOULD conform to the End User's country's postal system.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $postcode = '';

	/**
	 * The End User's country of residence as specified by ISO3166.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $country = '';

	/**
	 * End User's preferred language as specified by ISO639.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $language = '';

	/**
	 * ASCII string from TimeZone database
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $timezone = '';
}
