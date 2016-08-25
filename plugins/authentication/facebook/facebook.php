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
class PlgAuthenticationFacebook extends JPlugin
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
	public function onUserLoginFormFields()
	{
		// Nothing to do in administrator login
		if (JFactory::getApplication()->isAdmin())
		{
			return array();
		}

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

	public function onAjaxFacebook()
	{
		// Load plugin language
		$this->loadLanguage('plg_authentication_facebook');

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

			// Get the return URL
			$returnURL = base64_decode($app->input->get('state', ''));
			$returnURL = empty($returnURL) ? JUri::base() : $returnURL;

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
			$message = JText::sprintf('JGLOBAL_AUTH_FAILED', $e->getMessage());

			$app->enqueueMessage($message, 'error');
			$app->redirect(JUri::base());

			return;
		}

		// Look for a local user account with the Facebook user ID
		$userId = $this->getUserIdByFacebookId($fbUserId);

		// Does a user exist with the same email as the Facebook email??
		if ($userId == 0)
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
				$message =
					JText::sprintf('JGLOBAL_AUTH_FAILED', JText::_('PLG_AUTHENTICATION_FACEBOOK_ERROR_LOCAL_NOT_FOUND'));

				$app->enqueueMessage($message, 'error');
				$app->redirect(JUri::base());

				return;
			}

			try
			{
				$userId = $this->createUser($fbUserEmail, $fullName, $fbUserVerified, $fbUserGMTOffset);
			}
			catch (RuntimeException $e)
			{
				$message =
					JText::sprintf('JGLOBAL_AUTH_FAILED', JText::_('PLG_AUTHENTICATION_FACEBOOK_ERROR_LOCAL_NOT_FOUND'));

				$app->enqueueMessage($message, 'error');
				$app->redirect(JUri::base());

				return;
			}

			// Does the account need user or administrator verification?
			if (in_array(array('useractivate', 'adminactivate'), $userId))
			{
				$message = JText::_('PLG_AUTHENTICATION_FACEBOOK_NOTICE_' . $userId);

				$app->enqueueMessage($message, 'info');
				$app->redirect(JUri::base());

				return;
			}
		}

		// Attach the Facebook user ID and token to the user's profile
		try
		{
			$this->linkToFacebook($userId, $fbUserId, $token);
		}
		catch (Exception $e)
		{
			// Ignore database exceptions at this point
		}

		// Log in the user
		$this->loginUser($userId);

		$app->redirect($returnURL);
	}

	/**
	 * Social network logins do not go through onUserAuthenticate. There are no credentials to be checked against an
	 * authentication source. Instead, we are receiving login authentication from an external source, i.e. the social
	 * network itself.
	 *
	 * @param   array  $credentials Array holding the user credentials
	 * @param   array  $options     Array of extra options
	 * @param   object &$response   Authentication response object
	 *
	 * @return  void
	 *
	 * @since   3.7
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
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

			$currentURI = JUri::getInstance();

			$this->facebook = new JFacebookOAuth($options);
			$this->facebook->setScope('public_profile,email');
			$this->facebook->setOption('state', base64_encode((string) $currentURI));
		}

		return $this->facebook;
	}

	/**
	 * Derive a username from a full name
	 *
	 * @param   string $fullName
	 *
	 * @return  string  The derived username
	 *
	 * @since   3.7
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
	 * @param   string $email    Email
	 * @param   string $name     Full name
	 * @param   bool   $verified Is this a verified Facebook account?
	 * @param   int    $offset   GMT offset
	 *
	 * @return  string|int  User ID or string "useractivate" / "adminactivate" if activation is required
	 *
	 * @throws  RuntimeException  When an error occurs
	 *
	 * @since   3.7
	 */
	protected function createUser($email, $name, $verified, $offset)
	{
		// Look for a local user account with a username derived from the Facebook user's full name
		$username = $this->deriveUsername($name);
		$userId   = JUserHelper::getUserId($username);

		// Does an account with the same username already exist on our site?
		if ($userId != 0)
		{
			throw new RuntimeException(JText::_('PLG_AUTHENTICATION_FACEBOOK_ERROR_LOCAL_USERNAME_CONFLICT'));
		}

		$data = array(
			'name'      => $name,
			'username'  => $this->deriveUsername($username),
			'password1' => JUserHelper::genRandomPassword(32),
			'password2' => JUserHelper::genRandomPassword(32),
			'email1'    => JStringPunycode::emailToPunycode($email),
			'email2'    => JStringPunycode::emailToPunycode($email),
		);

		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_users/models', 'UsersModel');

		/** @var UsersModelRegistration $model */
		$model = JModelLegacy::getInstance('Registration', 'UsersModel', array('ignore_request' => true));

		/**
		 * Why we pass the $verified flag.
		 *
		 * We do not need to send an account verification email to verified Facebook accounts. These accounts have
		 * already had their email or phone number verified by Facebook. Therefore verified Facebook accounts get
		 * immediate access to our site, as the users would expect. Unverified accoutns have to go through the whole
		 * email verification process.
		 */
		$userId = $model->register($data, $verified);

		// Internal error setting up account?
		if ($userId === false)
		{
			throw new RuntimeException($model->getError());
		}

		// Return the user ID
		return $userId;
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
	protected function linkToFacebook($userId, $fbUserId, $token)
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

	/**
	 * Logs in a user. We use this method to override authentication and Two Factor Authentication plugins (since we are
	 * essentially implementing a single sign on where Facebook acts as our SSO authorization server).
	 *
	 * @param   int     $userId    Joomla! user ID
	 *
	 * @return  bool  True on success
	 *
	 * @throws  Exception
	 *
	 * @since   3.7
	 */
	public function loginUser($userId)
	{
		JLoader::import('joomla.user.authentication');

		/**
		 * We need this line to load the JAuthentication class file. That file ALSO defines the JAuthenticationResponse
		 * class. That's bad design which dates back to Joomla! 1.5 (possibly 1.0?) and which we can't change for b/c
		 * reasons. Do NOT delete this line!
		 */
		class_exists('JAuthentication');

		$response                = new JAuthenticationResponse();
		$user                    = JUser::getInstance($userId);
		$response->status        = JAuthentication::STATUS_SUCCESS;
		$response->type          = 'facebook';
		$response->error_message = '';
		$response->username      = $user->username;
		$response->email         = $user->email;
		$response->fullname      = $user->name;

		if (JFactory::getApplication()->isAdmin())
		{
			$response->language = $user->getParam('admin_language');
		}
		else
		{
			$response->language = $user->getParam('language');
		}

		JPluginHelper::importPlugin('user');
		$options = array('remember' => true);
		JEventDispatcher::getInstance()->trigger('onLoginUser', array((array) $response, $options));

		JLoader::import('joomla.user.helper');
		$userid = JUserHelper::getUserId($response->username);
		$user   = JFactory::getUser($userid);

		$session = JFactory::getSession();
		$session->set('user', $user);
	}

}
