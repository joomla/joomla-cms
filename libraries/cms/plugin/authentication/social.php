<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * JPluginAuthenticationSocial Class. Abstract functions for social / single sign on authentication plugins.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JPluginAuthenticationSocial extends JPlugin
{
	/**
	 * Return the custom login form fields for the social login / single sign on implementation
	 *
	 * @param   string  $loginUrl    A string with the URL to be redirected after the successfull login
	 * @param   string  $failureUrl  A string with the URL to be redirected after the unsuccessfull login
	 *
	 * @return  JAuthenticationFieldInterface[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	abstract public function onUserLoginFormFields($loginUrl = null, $failureUrl = null);

	/**
	 * Social network logins do not go through onUserAuthenticate. There are no credentials to be checked against an
	 * authentication source. Instead, we are receiving login authentication from an external source, i.e. the social
	 * network itself.
	 *
	 * @param   array   $credentials  Array holding the user credentials
	 * @param   array   $options      Array of extra options
	 * @param   object  &$response    Authentication response object
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	final public function onUserAuthenticate($credentials, $options, &$response)
	{
	}

	/**
	 * Derive a username from a full name
	 *
	 * @param   string  $fullName  The fullname
	 *
	 * @return  string  The derived username
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function deriveUsername($fullName)
	{
		$username = strtolower($fullName);
		$username = str_replace(' ', '.', $username);
		$username = preg_replace('/[^\pL\.]/', '', $username);
		$username = preg_replace('/\.+/', '.', $username);

		return $username;
	}

	/**
	 * Tries to create a new user account
	 *
	 * @param   string    $email     Email
	 * @param   string    $name      Full name
	 * @param   bool      $verified  Is this a verified Facebook account?
	 * @param   int|null  $offset    GMT offset. Null to not set. NB: Only set for verified accounts.
	 *
	 * @return  string|int  User ID or string "useractivate" / "adminactivate" if activation is required
	 *
	 * @throws  UnexpectedValueException  When the user already exists
	 * @throws  RuntimeException          When a user registration Model error occurs
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function createUser($email, $name, $verified = false, $offset = null)
	{
		// Look for a local user account with a username derived from the Facebook user's full name
		$username = $this->deriveUsername($name);
		$userId   = JUserHelper::getUserId($username);

		// Does an account with the same username already exist on our site?
		if ($userId != 0)
		{
			throw new UnexpectedValueException;
		}

		$randomPassword = JUserHelper::genRandomPassword(32);
		$data           = array(
			'name'      => $name,
			'username'  => $this->deriveUsername($username),
			'password1' => $randomPassword,
			'password2' => $randomPassword,
			'email1'    => JStringPunycode::emailToPunycode($email),
			'email2'    => JStringPunycode::emailToPunycode($email),
		);

		// Load com_users language, because the model doesn't do it automatically
		$jLanguage = JFactory::getLanguage();
		$jLanguage->load('com_users', JPATH_BASE, 'en-GB', true);
		$jLanguage->load('com_users', JPATH_BASE, null, false);

		// Load the Registration model of com_users and register the new user.
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_users/models', 'UsersModel');

		/** @var UsersModelRegistration $model */
		$model = JModelLegacy::getInstance('Registration', 'UsersModel', array('ignore_request' => true));

		/**
		 * Why do we pass the $verified flag? TL;DR: to streamline the user experience without discounting security.
		 *
		 * We do not need to send an account verification email to accounts verified by a third party. For example, in
		 * the case of Facebook login we may have a verified Facebook account. These accounts have already had their
		 * email or phone number verified by Facebook. Therefore verified Facebook accounts get immediate access to our
		 * site, as the users would expect. Unverified accounts have to go through the whole email verification process.
		 */
		$userId = $model->register($data, $verified);

		// Internal error setting up account?
		if ($userId === false)
		{
			throw new RuntimeException($model->getError());
		}

		// Update the time offset
		if (is_numeric($userId) && !is_null($offset))
		{
			$user = JFactory::getUser($userId);
			$user->setParam('offset', $offset);
			$user->save(true);
		}

		// Return the user ID
		return $userId;
	}

	/**
	 * Logs in a user. We use this method to override authentication and Two Factor Authentication plugins (since we are
	 * essentially implementing a single sign on where Facebook acts as our SSO authorization server).
	 *
	 * @param   integer  $userId  Joomla! user ID
	 *
	 * @return  boolean  True on success
	 *
	 * @throws  Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function loginUser($userId)
	{
		JLoader::import('joomla.user.authentication');

		/**
		 * We need this line to load the JAuthentication class file. That file ALSO defines the JAuthenticationResponse
		 * class. That's bad design which dates back to Joomla! 1.5 (possibly 1.0?) and which we can't change for b/c
		 * reasons. Do NOT delete this line!
		 */
		class_exists('JAuthentication');

		$user     = JUser::getInstance($userId);
		$response = $this->getAuthenticationResponseObject($user);

		// If the user doesn't exist
		if (empty($user->id))
		{
			$response->status        = JAuthentication::STATUS_UNKNOWN;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
			$this->processLoginFailure($response);

			return false;
		}

		// If the user is blocked
		if ($user->block == 1)
		{
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JERROR_NOLOGIN_BLOCKED');
			$this->processLoginFailure($response);

			return false;
		}

		// If the user is not activated yet
		if ($user->activation)
		{
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JERROR_NOLOGIN_BLOCKED');
			$this->processLoginFailure($response);

			return false;
		}

		JPluginHelper::importPlugin('user');
		$options = array('remember' => true);
		JEventDispatcher::getInstance()->trigger('onLoginUser', array((array) $response, $options));

		$session = JFactory::getSession();
		$session->set('user', $user);

		return true;
	}

	/**
	 * Returns a generic JAuthenticationResponse object
	 *
	 * @param   JUser  $user  A JUser object
	 *
	 * @return  JAuthenticationResponse
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getAuthenticationResponseObject(JUser $user = null)
	{
		JLoader::import('joomla.user.authentication');

		/**
		 * We need this line to load the JAuthentication class file. That file ALSO defines the JAuthenticationResponse
		 * class. That's bad design which dates back to Joomla! 1.5 (possibly 1.0?) and which we can't change for b/c
		 * reasons. Do NOT delete this line!
		 */
		class_exists('JAuthentication');

		$response                = new JAuthenticationResponse;
		$response->status        = JAuthentication::STATUS_UNKNOWN;
		$response->type          = $this->_name;
		$response->error_message = '';
		$response->username      = '';
		$response->email         = '';
		$response->fullname      = '';
		$response->language      = null;

		if (is_object($user))
		{
			$response->username = $user->username;
			$response->email    = $user->email;
			$response->fullname = $user->name;
			$response->language = $user->getParam('language');

			if (JFactory::getApplication()->isAdmin())
			{
				$response->language = $user->getParam('admin_language');
			}
		}

		return $response;
	}

	/**
	 * Logs a login failure by triggering the onUserLoginFailure event in the application. It will enqueue any error
	 * messages for display.
	 *
	 * @param   JAuthenticationResponse  $response  The authentication response object
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function processLoginFailure(JAuthenticationResponse $response)
	{
		JPluginHelper::importPlugin('user');

		$app = JFactory::getApplication();
		$app->triggerEvent('onUserLoginFailure', array((array) $response));

		if (!empty($response->error_message))
		{
			$app->enqueueMessage($response->error_message, 'error');
		}
	}
}
