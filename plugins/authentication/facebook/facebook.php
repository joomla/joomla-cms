<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.facebook
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Facebook Authentication Plugin
 *
 * @since  3.7
 */
class PlgAuthenticationFacebook extends JPluginAuthenticationSocial
{
	private $facebook = null;

	/**
	 * PlgAuthenticationFacebook constructor. Loads the necessary classes.
	 *
	 * @param object $subject
	 * @param array  $config
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		// Load the custom authentication class
		if (!class_exists('JAuthenticationFieldFacebook'))
		{
			require_once __DIR__ . '/field/facebook.php';
		}
	}

	/**
	 * Return the custom login form fields for Facebook login
	 *
	 * @return  JAuthenticationFieldInterface[]
	 *
	 * @since   3.7
	 */
	public function onUserLoginFormFields($loginUrl = null, $failureUrl = null)
	{
		// Nothing to do in administrator login
		if (JFactory::getApplication()->isAdmin())
		{
			return array();
		}

		// Set the return URLs into the session
		$session = JFactory::getSession();
		$session->set('loginUrl', $loginUrl, 'plg_authenticate_facebook');
		$session->set('failureUrl', $failureUrl, 'plg_authenticate_facebook');

		// Load plugin language
		$this->loadLanguage('plg_authentication_facebook');

		// Initialize the fields
		$fields = array();

		// Try to get a Facebook custom field
		try
		{
			$appId     = $this->params->get('appid', '');
			$appSecret = $this->params->get('appsecret', '');

			if (empty($appId) || empty($appSecret))
			{
				throw new InvalidArgumentException('Cannot have an empty Facebook application ID or Secret');
			}

			$fields[] = new JAuthenticationFieldFacebook($this->getFacebookOauth());
		}
		catch (InvalidArgumentException $e)
		{
			$fields = array();
		}

		return $fields;
	}

	/**
	 * Processes the authentication callback from Facebook.
	 *
	 * @return  void
	 */
	public function onAjaxFacebook()
	{
		// Load plugin language
		$this->loadLanguage('plg_authentication_facebook');

		// Get the return URLs from the session
		$session    = JFactory::getSession();
		$loginUrl   = $session->get('loginUrl', null, 'plg_authenticate_facebook');
		$failureUrl = $session->get('failureUrl', null, 'plg_authenticate_facebook');

		// Remove the return URLs from the session
		$session->set('loginUrl', null, 'plg_authenticate_facebook');
		$session->set('failureUrl', null, 'plg_authenticate_facebook');

		// Try to exchange the code with a token
		$facebookOauth = $this->getFacebookOauth();
		$app           = JFactory::getApplication();

		try
		{
			$token = $facebookOauth->authenticate();

			if ($token === false)
			{
				throw new RuntimeException(JText::_('PLG_AUTHENTICATION_FACEBOOK_ERROR_NOT_LOGGED_IN_FB'));
			}

			// Get information about the user from Big Brother... er... Facebook.
			$options = new Registry();
			$options->set('api.url', 'https://graph.facebook.com/v2.7/');
			$fbUserApi    = new JFacebookUser($options, null, $facebookOauth);
			$fbUserFields = $fbUserApi->getUser('me?fields=id,name,email,verified,timezone');

			$fullName        = $fbUserFields->name;
			$fbUserId        = $fbUserFields->id;
			$fbUserEmail     = $fbUserFields->email;
			$fbUserVerified  = $fbUserFields->verified;
			$fbUserGMTOffset = $fbUserFields->timezone;
		}
		catch (Exception $e)
		{
			// Log failed login
			$response = $this->getAuthenticationResponseObject();
			$response->status = JAuthentication::STATUS_UNKNOWN;
			$response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', $e->getMessage());
			$this->processLoginFailure($response);

			$app->redirect($failureUrl);

			return;
		}

		// Look for a local user account with the Facebook user ID
		$userId = $this->getUserIdByFacebookId($fbUserId);

		/**
		 * Does a user exist with the same email as the Facebook email?
		 *
		 * We only do that for verified Facebook users, i.e. people who have already verified that they have control of
		 * their stated email address and / or phone with Facebook. This is a security measure! It prevents someone from
		 * registering a Facebook account under your email address (without verifying that email address) and use it to
		 * login into the Joomla site impersonating you.
		 */
		if ($fbUserVerified && ($userId == 0))
		{
			$userId = JUserHelper::getUserIdByEmail($fbUserEmail);
		}

		if (empty($userId))
		{
			$usersConfig           = JComponentHelper::getParams('com_users');
			$allowUserRegistration = $usersConfig->get('allowUserRegistration');

			// User not found and user registration is disabled
			if ($allowUserRegistration == 0)
			{
				// Log failed login
				$response                = $this->getAuthenticationResponseObject();
				$response->status        = JAuthentication::STATUS_UNKNOWN;
				$response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', JText::_('PLG_AUTHENTICATION_FACEBOOK_ERROR_LOCAL_NOT_FOUND'));
				$this->processLoginFailure($response);

				$app->redirect($failureUrl);

				return;
			}

			try
			{
				$userId = $this->createUser($fbUserEmail, $fullName, $fbUserVerified, $fbUserGMTOffset);
			}
			catch (UnexpectedValueException $e)
			{
				// Log failure to create user (username already exists)
				$response                = $this->getAuthenticationResponseObject();
				$response->status        = JAuthentication::STATUS_UNKNOWN;
				$response->error_message = JText::sprintf('PLG_AUTHENTICATION_FACEBOOK_ERROR_CANNOT_CREATE', JText::_('PLG_AUTHENTICATION_FACEBOOK_ERROR_LOCAL_USERNAME_CONFLICT'));
				$this->processLoginFailure($response);

				$app->redirect($failureUrl);

				return;
			}
			catch (RuntimeException $e)
			{
				// Log failure to create user (other internal error, check the model error message returned in the exception)
				$response                = $this->getAuthenticationResponseObject();
				$response->status        = JAuthentication::STATUS_UNKNOWN;
				$response->error_message = JText::sprintf('PLG_AUTHENTICATION_FACEBOOK_ERROR_CANNOT_CREATE', $e->getMessage());
				$this->processLoginFailure($response);

				$app->redirect($failureUrl);

				return;
			}

			// Does the account need user or administrator verification?
			if (in_array($userId, array('useractivate', 'adminactivate')))
			{
				// Do NOT go through processLoginFailure. This is NOT a login failure.
				$message = JText::_('PLG_AUTHENTICATION_FACEBOOK_NOTICE_' . $userId);

				$app->enqueueMessage($message, 'info');
				$app->redirect($failureUrl);

				return;
			}
		}

		// Attach the Facebook user ID and token to the user's profile
		try
		{
			self::linkToFacebook($userId, $fbUserId, $token);
		}
		catch (Exception $e)
		{
			// Ignore database exceptions at this point
		}

		// Log in the user
		if ($this->loginUser($userId))
		{
			$app->redirect($loginUrl);

			return;
		}

		$app->redirect($failureUrl);
	}

	/**
	 * Returns a JFacebookOAuth object
	 *
	 * @return  JFacebookOAuth
	 *
	 * @since   3.7
	 */
	protected function getFacebookOauth()
	{
		if (is_null($this->facebook))
		{
			$appId     = $this->params->get('appid', '');
			$appSecret = $this->params->get('appsecret', '');

			$options = new Registry(array(
				'clientid'     => $appId,
				'clientsecret' => $appSecret,
				'redirecturi'  => JUri::base() . 'index.php?option=com_ajax&group=authentication&plugin=facebook&format=raw'
			));

			$this->facebook = new JFacebookOAuth($options);
			$this->facebook->setScope('public_profile,email');
		}

		return $this->facebook;
	}

	/**
	 * Links the user account to the Facebook account through User Profile fields
	 *
	 * @param   int    $userId   The Joomla! user ID
	 * @param   int    $fbUserId The Facebook user ID
	 * @param   string $token    The Facebook OAuth token
	 *
	 * @return  void
	 *
	 * @since   3.7
	 */
	protected static function linkToFacebook($userId, $fbUserId, $token)
	{
		// Load the profile data from the database.
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true)
		             ->select(array(
			             $db->qn('profile_key'),
			             $db->qn('profile_value'),
		             ))->from($db->qn('#__user_profiles'))
		             ->where($db->qn('user_id') . ' = ' . $db->q((int) $userId))
		             ->where($db->qn('profile_key') . ' LIKE ' . $db->q('facebook.%'))
		             ->order($db->qn('ordering'));
		$fields = $db->setQuery($query)->loadAssocList('profile_key', 'profile_value');

		if (!isset($fields['facebook.userid']))
		{
			$newField = (object) array(
				'user_id'       => $userId,
				'profile_key'   => 'facebook.userid',
				'profile_value' => $fbUserId,
				'ordering'      => 0
			);

			$db->insertObject('#__user_profiles', $newField);
		}
		elseif ($fields['facebook.userid'] != $fbUserId)
		{
			$query = $db->getQuery(true)
			            ->update($db->qn('#__user_profiles'))
			            ->set($db->qn('profile_value') . ' = ' . $db->q($fbUserId))
			            ->where($db->qn('user_id') . ' = ' . $db->q((int) $userId))
			            ->where($db->qn('profile_key') . ' = ' . $db->q('facebook.userid'));
			$db->setQuery($query)->execute();
		}

		$token = json_encode($token);

		if (!isset($fields['facebook.token']))
		{
			$newField = (object) array(
				'user_id'       => $userId,
				'profile_key'   => 'facebook.token',
				'profile_value' => $token,
				'ordering'      => 0
			);

			$db->insertObject('#__user_profiles', $newField);
		}
		elseif ($fields['facebook.token'] != $token)
		{
			$query = $db->getQuery(true)
			            ->update($db->qn('#__user_profiles'))
			            ->set($db->qn('profile_value') . ' = ' . $db->q($token))
			            ->where($db->qn('user_id') . ' = ' . $db->q((int) $userId))
			            ->where($db->qn('profile_key') . ' = ' . $db->q('facebook.token'));
			$db->setQuery($query)->execute();
		}
	}

	/**
	 * Gets the Joomla! user ID that corresponds to a Facebook user ID. Of course that implies that the user has logged
	 * in to the Joomla! site through Facebook in the past or he has otherwise linked his user account to Facebook.
	 *
	 * @param   string $fbUserId The Facebook User ID.
	 *
	 * @return  int  The corresponding user ID or 0 if no user is found
	 *
	 * @since   3.7
	 */
	protected function getUserIdByFacebookId($fbUserId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
		            ->select(array(
			            $db->qn('user_id'),
		            ))->from($db->qn('#__user_profiles'))
		            ->where($db->qn('profile_key') . ' = ' . $db->q('facebook.userid'))
		            ->where($db->qn('profile_value') . ' = ' . $db->q($fbUserId));

		try
		{
			$id = $db->setQuery($query, 0, 1)->loadResult();

			// Not found?
			if (empty($id))
			{
				return 0;
			}

			/**
			 * If you delete a user its profile fields are left behind and confuse our code. Therefore we have to check
			 * if the user *really* exists. However we can't just go through JFactory::getUser() because if the user
			 * does not exist we'll end up with an ugly Warning on our page with a text similar to "JUser: :_load:
			 * Unable to load user with ID: 1234". This cannot be disabled so we have to be, um, a bit creative :/
			 */
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
			            ->select('COUNT(*)')->from($db->qn('#__users'))
			            ->where($db->qn('id') . ' = ' . $db->q($id));
			$userExists = $db->setQuery($query)->loadResult();

			return ($userExists == 0) ? 0 : $id;
		}
		catch (Exception $e)
		{
			return 0;
		}
	}
}
