<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Registration controller class for Users.
 *
 * @since  1.6
 */
class RegistrationController extends BaseController
{
	/**
	 * Method to activate a user.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function activate()
	{
		$user  	 = $this->app->getIdentity();
		$input 	 = $this->input;
		$uParams = ComponentHelper::getParams('com_users');

		// Check for admin activation. Don't allow non-super-admin to delete a super admin
		if ($uParams->get('useractivation') != 2 && $user->get('id'))
		{
			$this->setRedirect('index.php');

			return true;
		}

		// If user registration or account activation is disabled, throw a 403.
		if ($uParams->get('useractivation') == 0 || $uParams->get('allowUserRegistration') == 0)
		{
			throw new \Exception(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		/** @var \Joomla\Component\Users\Site\Model\RegistrationModel $model */
		$model = $this->getModel('Registration', 'Site');
		$token = $input->getAlnum('token');

		// Check that the token is in a valid format.
		if ($token === null || strlen($token) !== 32)
		{
			throw new \Exception(Text::_('JINVALID_TOKEN'), 403);
		}

		// Get the User ID
		$userIdToActivate = $model->getUserIdFromToken($token);

		if (!$userIdToActivate)
		{
			$this->setMessage(Text::_('COM_USERS_ACTIVATION_TOKEN_NOT_FOUND'));
			$this->setRedirect(Route::_('index.php?option=com_users&view=login', false));

			return false;
		}

		// Get the user we want to activate
		$userToActivate = Factory::getUser($userIdToActivate);

		// Admin activation is on and admin is activating the account
		if (($uParams->get('useractivation') == 2) && $userToActivate->getParam('activate', 0))
		{
			// If a user admin is not logged in, redirect them to the login page with an error message
			if (!$user->authorise('core.create', 'com_users') || !$user->authorise('core.manage', 'com_users'))
			{
				$activationUrl = 'index.php?option=com_users&task=registration.activate&token=' . $token;
				$loginUrl      = 'index.php?option=com_users&view=login&return=' . base64_encode($activationUrl);

				// In case we still run into this in the second step the user does not have the right permissions
				$message = Text::_('COM_USERS_REGISTRATION_ACL_ADMIN_ACTIVATION_PERMISSIONS');

				// When we are not logged in we should login
				if ($user->guest)
				{
					$message = Text::_('COM_USERS_REGISTRATION_ACL_ADMIN_ACTIVATION');
				}

				$this->setMessage($message);
				$this->setRedirect(Route::_($loginUrl, false));

				return false;
			}
		}

		// Attempt to activate the user.
		$return = $model->activate($token);

		// Check for errors.
		if ($return === false)
		{
			// Redirect back to the home page.
			$this->setMessage(Text::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $model->getError()), 'error');
			$this->setRedirect('index.php');

			return false;
		}

		$useractivation = $uParams->get('useractivation');

		// Redirect to the login screen.
		if ($useractivation == 0)
		{
			$this->setMessage(Text::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'));
			$this->setRedirect(Route::_('index.php?option=com_users&view=login', false));
		}
		elseif ($useractivation == 1)
		{
			$this->setMessage(Text::_('COM_USERS_REGISTRATION_ACTIVATE_SUCCESS'));
			$this->setRedirect(Route::_('index.php?option=com_users&view=login', false));
		}
		elseif ($return->getParam('activate'))
		{
			$this->setMessage(Text::_('COM_USERS_REGISTRATION_VERIFY_SUCCESS'));
			$this->setRedirect(Route::_('index.php?option=com_users&view=registration&layout=complete', false));
		}
		else
		{
			$this->setMessage(Text::_('COM_USERS_REGISTRATION_ADMINACTIVATE_SUCCESS'));
			$this->setRedirect(Route::_('index.php?option=com_users&view=registration&layout=complete', false));
		}

		return true;
	}

	/**
	 * Method to register a user.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function register()
	{
		// Check for request forgeries.
		$this->checkToken();

		// If registration is disabled - Redirect to login page.
		if (ComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0)
		{
			$this->setRedirect(Route::_('index.php?option=com_users&view=login', false));

			return false;
		}

		$app   = $this->app;

		/** @var \Joomla\Component\Users\Site\Model\RegistrationModel $model */
		$model = $this->getModel('Registration', 'Site');

		// Get the user data.
		$requestData = $this->input->post->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new \Exception($model->getError(), 500);
		}

		$data = $model->validate($form, $requestData);

		// Check for validation errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'error');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'error');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_users.registration.data', $requestData);

			// Redirect back to the registration screen.
			$this->setRedirect(Route::_('index.php?option=com_users&view=registration', false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->register($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_users.registration.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_users&view=registration', false));

			return false;
		}

		// Flush the data from the session.
		$app->setUserState('com_users.registration.data', null);

		// Redirect to the profile screen.
		if ($return === 'adminactivate')
		{
			$this->setMessage(Text::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY'));
			$this->setRedirect(Route::_('index.php?option=com_users&view=registration&layout=complete', false));
		}
		elseif ($return === 'useractivate')
		{
			$this->setMessage(Text::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));
			$this->setRedirect(Route::_('index.php?option=com_users&view=registration&layout=complete', false));
		}
		else
		{
			$this->setMessage(Text::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'));
			$this->setRedirect(Route::_('index.php?option=com_users&view=login', false));
		}

		return true;
	}
}
