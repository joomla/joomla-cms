<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Authentication;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Authentication class, provides an interface for the Joomla authentication system
 *
 * @since  11.1
 */
class Authentication extends \JObject
{
	/**
	 * This is the status code returned when the authentication is success (permit login)
	 * @const  STATUS_SUCCESS successful response
	 * @since  11.2
	 */
	const STATUS_SUCCESS = 1;

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
	 * @var    Authentication  JAuthentication instances container.
	 * @since  11.3
	 */
	protected static $instance;

	/**
	 * @var    PluginFactoryInterface  The factory
	 * @since  __DEPLOY_VERSION__
	 */
	protected $factory;

	/**
	 * Constructor
	 *
	 * @param   PluginFactoryInterface  $factory  The plugin factory
	 *
	 * @since   11.1
	 */
	public function __construct(PluginFactoryInterface $factory = null)
	{
		// Set the factory
		if (!is_object($factory))
		{
			$factory = Factory::getContainer()->get(PluginFactoryInterface::class);
		}

		$this->factory = $factory;

		$isLoaded = PluginHelper::importPlugin('authentication');

		if (!$isLoaded)
		{
			Log::add(\JText::_('JLIB_USER_ERROR_AUTHENTICATION_LIBRARIES'), Log::WARNING, 'jerror');
		}
	}

	/**
	 * Returns the global authentication object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return  Authentication  The global Authentication object
	 *
	 * @since   11.1
	 */
	public static function getInstance()
	{
		if (empty(self::$instance))
		{
			self::$instance = new static;
		}

		return self::$instance;
	}

	/**
	 * Finds out if a set of login credentials are valid by asking all observing
	 * objects to run their respective authentication routines.
	 *
	 * @param   array  $credentials  Array holding the user credentials.
	 * @param   array  $options      Array holding user options.
	 *
	 * @return  AuthenticationResponse  Response object with status variable filled in for last plugin or first successful plugin.
	 *
	 * @see     AuthenticationResponse
	 * @since   11.1
	 */
	public function authenticate($credentials, $options = array())
	{
		// Get plugins
		$plugins = $this->factory->getPlugins('authentication');

		// Create authentication response
		$response = new AuthenticationResponse;

		/*
		 * Loop through the plugins and check if the credentials can be used to authenticate
		 * the user
		 *
		 * Any errors raised in the plugin should be returned via the AuthenticationResponse
		 * and handled appropriately.
		 */
		foreach ($plugins as $plugin)
		{
			// Check if we have the right plugin
			if (!$plugin instanceof AuthenticationPluginInterface && !method_exists($plugin, 'onUserAuthenticate'))
			{
				continue;
			}

			// Try to authenticate
			$plugin->onUserAuthenticate($credentials, $options, $response);

			// If authentication is successful break out of the loop
			if ($response->status === self::STATUS_SUCCESS)
			{
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
	 * @param   AuthenticationResponse  $response  response including username of the user to authorise
	 * @param   array                   $options   list of options
	 *
	 * @return  AuthenticationResponse[]  Array of authentication response objects
	 *
	 * @since  11.2
	 */
	public static function authorise($response, $options = array())
	{
		// Get plugins in case they haven't been imported already
		PluginHelper::importPlugin('user');
		PluginHelper::importPlugin('authentication');
		$results = Factory::getApplication()->triggerEvent('onUserAuthorisation', array($response, $options));

		return $results;
	}
}
