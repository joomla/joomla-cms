<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the helper and model used for two factor authentication
JLoader::register('UsersModelUser', JPATH_ADMINISTRATOR . '/components/com_users/models/user.php');
JLoader::register('UsersHelper', JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php');
/**
 * User model.
 *
 * @since  1.6
 */
class AdminModelProfile extends UsersModelUser
{
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_admin.profile', 'profile', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		// Check for username compliance and parameter set
		$isUsernameCompliant = true;

		if ($this->loadFormData()->username)
		{
			$username = $this->loadFormData()->username;
			$isUsernameCompliant = !(preg_match('#[<>"\'%;()&\\\\]|\\.\\./#', $username) || strlen(utf8_decode($username)) < 2
				|| trim($username) != $username);
		}

		$this->setState('user.username.compliant', $isUsernameCompliant);

		if (!JComponentHelper::getParams('com_users')->get('change_login_name') && $isUsernameCompliant)
		{
			$form->setFieldAttribute('username', 'required', 'false');
			$form->setFieldAttribute('username', 'readonly', 'true');
			$form->setFieldAttribute('username', 'description', 'COM_ADMIN_USER_FIELD_NOCHANGE_USERNAME_DESC');
		}

		// When multilanguage is set, a user's default site language should also be a Content Language
		if (JLanguageMultilang::isEnabled())
		{
			$form->setFieldAttribute('language', 'type', 'frontend_language', 'params');
		}

		// If the user needs to change their password, mark the password fields as required
		if (JFactory::getUser()->requireReset)
		{
			$form->setFieldAttribute('password', 'required', 'true');
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
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_users.edit.user.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// Load the users plugins.
		JPluginHelper::importPlugin('user');

		$this->preprocessData('com_admin.profile', $data);

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		return parent::getItem(JFactory::getUser()->id);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$user = JFactory::getUser();

		unset($data['id']);
		unset($data['groups']);
		unset($data['sendEmail']);
		unset($data['block']);

		$isUsernameCompliant = $this->getState('user.username.compliant');

		if (!JComponentHelper::getParams('com_users')->get('change_login_name') && $isUsernameCompliant)
		{
			unset($data['username']);
		}

		// Handle the two factor authentication setup
		if (array_key_exists('twofactor', $data))
		{
			$model = new UsersModelUser;

			$twoFactorMethod = $data['twofactor']['method'];

			// Get the current One Time Password (two factor auth) configuration
			$otpConfig = $model->getOtpConfig($userId);

			if ($twoFactorMethod !== 'none')
			{
				// Run the plugins
				FOFPlatform::getInstance()->importPlugin('twofactorauth');
				$otpConfigReplies = FOFPlatform::getInstance()->runPlugins('onUserTwofactorApplyConfiguration', array($twoFactorMethod));

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

		$user->groups = null;

		// Store the data.
		if (!$user->save())
		{
			$this->setError($user->getError());

			return false;
		}

		$this->setState('user.id', $user->id);

		return true;
	}

	/**
	 * Gets the configuration forms for all two-factor authentication methods
	 * in an array.
	 *
	 * @param   integer  $user_id  The user ID to load the forms for (optional)
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	protected function XXgetTwofactorform($user_id = null)
	{
		$user_id = (!empty($user_id)) ? $user_id : (int) $this->getState('user.id');

		$model = new UsersModelUser;

		$otpConfig = $model->getOtpConfig($user_id);

		FOFPlatform::getInstance()->importPlugin('twofactorauth');

		return FOFPlatform::getInstance()->runPlugins('onUserTwofactorShowConfiguration', array($otpConfig, $user_id));
	}

	/**
	 * Returns the one time password (OTP) – a.k.a. two factor authentication –
	 * configuration for a particular user.
	 *
	 * @param   integer  $user_id  The numeric ID of the user
	 *
	 * @return  stdClass  An object holding the OTP configuration for this user
	 *
	 * @since   3.2
	 */
	protected function XXgetOtpConfig($user_id = null)
	{
		$user_id = (!empty($user_id)) ? $user_id : (int) $this->getState('user.id');

		$model = new UsersModelUser;

		return $model->getOtpConfig($user_id);
	}
}
