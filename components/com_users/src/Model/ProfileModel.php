<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Registry\Registry;

/**
 * Profile model class for Users.
 *
 * @since  1.6
 */
class ProfileModel extends FormModel
{
	/**
	 * @var		object	The user profile data.
	 * @since   1.6
	 */
	protected $data;

	/**
	 * Constructor.
	 *
	 * @param   array                 $config       An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface   $factory      The factory.
	 * @param   FormFactoryInterface  $formFactory  The form factory.
	 *
	 * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @since   3.2
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{
		$config = array_merge(
			array(
				'events_map' => array('validate' => 'user')
			), $config
		);

		parent::__construct($config, $factory, $formFactory);
	}

	/**
	 * Method to get the profile form data.
	 *
	 * The base form data is loaded and then an event is fired
	 * for users plugins to extend the data.
	 *
	 * @return  User
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function getData()
	{
		if ($this->data === null)
		{
			$userId = $this->getState('user.id');

			// Initialise the table with Joomla\CMS\User\User.
			$this->data = new User($userId);

			// Set the base user data.
			$this->data->email1 = $this->data->get('email');

			// Override the base user data with any data in the session.
			$temp = (array) Factory::getApplication()->getUserState('com_users.edit.profile.data', array());

			foreach ($temp as $k => $v)
			{
				$this->data->$k = $v;
			}

			// Unset the passwords.
			unset($this->data->password1, $this->data->password2);

			$registry           = new Registry($this->data->params);
			$this->data->params = $registry->toArray();
		}

		return $this->data;
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|bool  A Form object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_users.profile', 'profile', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		// Check for username compliance and parameter set
		$isUsernameCompliant = true;
		$username = $loadData ? $form->getValue('username') : $this->loadFormData()->username;

		if ($username)
		{
			$isUsernameCompliant  = !(preg_match('#[<>"\'%;()&\\\\]|\\.\\./#', $username) || strlen(utf8_decode($username)) < 2
				|| trim($username) !== $username);
		}

		$this->setState('user.username.compliant', $isUsernameCompliant);

		if ($isUsernameCompliant && !ComponentHelper::getParams('com_users')->get('change_login_name'))
		{
			$form->setFieldAttribute('username', 'class', '');
			$form->setFieldAttribute('username', 'filter', '');
			$form->setFieldAttribute('username', 'description', 'COM_USERS_PROFILE_NOCHANGE_USERNAME_DESC');
			$form->setFieldAttribute('username', 'validate', '');
			$form->setFieldAttribute('username', 'message', '');
			$form->setFieldAttribute('username', 'readonly', 'true');
			$form->setFieldAttribute('username', 'required', 'false');
		}

		// When multilanguage is set, a user's default site language should also be a Content Language
		if (Multilanguage::isEnabled())
		{
			$form->setFieldAttribute('language', 'type', 'frontend_language', 'params');
		}

		// If the user needs to change their password, mark the password fields as required
		if (Factory::getUser()->requireReset)
		{
			$form->setFieldAttribute('password1', 'required', 'true');
			$form->setFieldAttribute('password2', 'required', 'true');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		$data = $this->getData();

		$this->preprocessData('com_users.profile', $data, 'user');

		return $data;
	}

	/**
	 * Override preprocessForm to load the user plugin group instead of content.
	 *
	 * @param   Form    $form   A Form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @throws	\Exception if there is an error in the form event.
	 *
	 * @since   1.6
	 */
	protected function preprocessForm(Form $form, $data, $group = 'user')
	{
		if (ComponentHelper::getParams('com_users')->get('frontend_userparams'))
		{
			$form->loadFile('frontend', false);

			if (Factory::getUser()->authorise('core.login.admin'))
			{
				$form->loadFile('frontend_admin', false);
			}
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	protected function populateState()
	{
		// Get the application object.
		$params = Factory::getApplication()->getParams('com_users');

		// Get the user id.
		$userId = Factory::getApplication()->getUserState('com_users.edit.profile.id');
		$userId = !empty($userId) ? $userId : (int) Factory::getUser()->get('id');

		// Set the user id.
		$this->setState('user.id', $userId);

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  The user id on success, false on failure.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function save($data)
	{
		$userId = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('user.id');

		$user = new User($userId);

		// Prepare the data for the user object.
		$data['email']    = PunycodeHelper::emailToPunycode($data['email1']);
		$data['password'] = $data['password1'];

		// Unset the username if it should not be overwritten
		$isUsernameCompliant = $this->getState('user.username.compliant');

		if ($isUsernameCompliant && !ComponentHelper::getParams('com_users')->get('change_login_name'))
		{
			unset($data['username']);
		}

		// Unset block and sendEmail so they do not get overwritten
		unset($data['block'], $data['sendEmail']);

		// Handle the two factor authentication setup
		if (array_key_exists('twofactor', $data))
		{
			$model = $this->bootComponent('com_users')->getMVCFactory()
				->createModel('User', 'Administrator');

			$twoFactorMethod = $data['twofactor']['method'];

			// Get the current One Time Password (two factor auth) configuration
			$otpConfig = $model->getOtpConfig($userId);

			if ($twoFactorMethod !== 'none')
			{
				// Run the plugins
				PluginHelper::importPlugin('twofactorauth');
				$otpConfigReplies = Factory::getApplication()->triggerEvent('onUserTwofactorApplyConfiguration', array($twoFactorMethod));

				// Look for a valid reply
				foreach ($otpConfigReplies as $reply)
				{
					if (!is_object($reply) || empty($reply->method) || ($reply->method != $twoFactorMethod))
					{
						continue;
					}

					$otpConfig->method = $reply->method;
					$otpConfig->config = $reply->config;

					break;
				}

				// Save OTP configuration.
				$model->setOtpConfig($userId, $otpConfig);

				// Generate one time emergency passwords if required (depleted or not set)
				if (empty($otpConfig->otep))
				{
					$model->generateOteps($userId);
				}
			}
			else
			{
				$otpConfig->method = 'none';
				$otpConfig->config = array();
				$model->setOtpConfig($userId, $otpConfig);
			}

			// Unset the raw data
			unset($data['twofactor']);

			// Reload the user record with the updated OTP configuration
			$user->load($userId);
		}

		// Bind the data.
		if (!$user->bind($data))
		{
			$this->setError($user->getError());

			return false;
		}

		// Load the users plugin group.
		PluginHelper::importPlugin('user');

		// Retrieve the user groups so they don't get overwritten
		unset($user->groups);
		$user->groups = Access::getGroupsByUser($user->id, false);

		// Store the data.
		if (!$user->save())
		{
			$this->setError($user->getError());

			return false;
		}

		// Destroy all active sessions for the user after changing the password
		if ($data['password'])
		{
			UserHelper::destroyUserSessions($user->id, true);
		}

		return $user->id;
	}

	/**
	 * Gets the configuration forms for all two-factor authentication methods
	 * in an array.
	 *
	 * @param   integer  $userId  The user ID to load the forms for (optional)
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function getTwofactorform($userId = null)
	{
		$userId = (!empty($userId)) ? $userId : (int) $this->getState('user.id');

		$model = $this->bootComponent('com_users')->getMVCFactory()
			->createModel('User', 'Administrator');

		$otpConfig = $model->getOtpConfig($userId);

		PluginHelper::importPlugin('twofactorauth');

		return Factory::getApplication()->triggerEvent('onUserTwofactorShowConfiguration', array($otpConfig, $userId));
	}

	/**
	 * Returns the one time password (OTP) – a.k.a. two factor authentication –
	 * configuration for a particular user.
	 *
	 * @param   integer  $userId  The numeric ID of the user
	 *
	 * @return  \stdClass  An object holding the OTP configuration for this user
	 *
	 * @since   3.2
	 */
	public function getOtpConfig($userId = null)
	{
		$userId = (!empty($userId)) ? $userId : (int) $this->getState('user.id');

		$model = $this->bootComponent('com_users')
			->getMVCFactory()->createModel('User', 'Administrator');

		return $model->getOtpConfig($userId);
	}
}
