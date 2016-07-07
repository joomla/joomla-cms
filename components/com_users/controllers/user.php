<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Registration controller class for Users.
 *
 * @since  1.6
 */
class UsersControllerUser extends UsersController
{
	/**
	 * Method to log in a user.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function login()
	{
		JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		$app    = JFactory::getApplication();
		$input  = $app->input;
		$method = $input->getMethod();

		// Populate the data array:
		$data = array();

		$data['return']    = base64_decode($app->input->post->get('return', '', 'BASE64'));
		$data['username']  = $input->$method->get('username', '', 'USERNAME');
		$data['password']  = $input->$method->get('password', '', 'RAW');
		$data['secretkey'] = $input->$method->get('secretkey', '', 'RAW');

		// Check for a simple menu item id
		if (is_numeric($data['return']))
		{
			if (JLanguageMultilang::isEnabled())
			{

				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('language')
					->from($db->quoteName('#__menu'))
					->where('client_id = 0')
					->where('id =' . $data['return']);

				$db->setQuery($query);

				try
				{
					$language = $db->loadResult();
				}
				catch (RuntimeException $e)
				{
					return;
				}

				if ($language !== '*')
				{
					$lang = '&lang=' . $language;
				}
				else
				{
					$lang = '';
				}
			}
			else
			{
				$lang = '';
			}

			$data['return'] = 'index.php?Itemid=' . $data['return'] . $lang;
		}
		else
		{
			// Don't redirect to an external URL.
			if (!JUri::isInternal($data['return']))
			{
				$data['return'] = '';
			}
		}

		// Set the return URL if empty.
		if (empty($data['return']))
		{
			$data['return'] = 'index.php?option=com_users&view=profile';
		}

		// Set the return URL in the user state to allow modification by plugins
		$app->setUserState('users.login.form.return', $data['return']);

		// Get the log in options.
		$options = array();
		$options['remember'] = $this->input->getBool('remember', false);
		$options['return']   = $data['return'];

		// Get the log in credentials.
		$credentials = array();
		$credentials['username']  = $data['username'];
		$credentials['password']  = $data['password'];
		$credentials['secretkey'] = $data['secretkey'];

		// Perform the log in.
		if (true !== $app->login($credentials, $options))
		{
			// Login failed !
			// Clear user name, password and secret key before sending the login form back to the user.
			$data['remember'] = (int) $options['remember'];
			$data['username'] = '';
			$data['password'] = '';
			$data['secretkey'] = '';
			$app->setUserState('users.login.form.data', $data);
			$app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
		}

		// Success
		if ($options['remember'] == true)
		{
			$app->setUserState('rememberLogin', true);
		}

		$app->setUserState('users.login.form.data', array());
		$app->redirect(JRoute::_($app->getUserState('users.login.form.return'), false));
	}

	/**
	 * Method to log out a user.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function logout()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		// Perform the log out.
		$error  = $app->logout();
		$input  = $app->input;
		$method = $input->getMethod();

		// Check if the log out succeeded.
		if ($error instanceof Exception)
		{
			$app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
		}

		// Get the return url from the request and validate that it is internal.
		$return = $input->$method->get('return', '', 'BASE64');
		$return = base64_decode($return);

		// Check for a simple menu item id
		if (is_numeric($return))
		{
			if (JLanguageMultilang::isEnabled())
			{

				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('language')
					->from($db->quoteName('#__menu'))
					->where('client_id = 0')
					->where('id =' . $return);

				$db->setQuery($query);

				try
				{
					$language = $db->loadResult();
				}
				catch (RuntimeException $e)
				{
					return;
				}

				if ($language !== '*')
				{
					$lang = '&lang=' . $language;
				}
				else
				{
					$lang = '';
				}
			}
			else
			{
				$lang = '';
			}

			$return = 'index.php?Itemid=' . $return . $lang;

			// Redirect to internal URLs only
			if (JUri::isInternal($return))
			{
				$app->redirect(JRoute::_($return, false));
			}
		}
	}

	/**
	 * Method to logout directly and redirect to page.
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	public function menulogout()
	{
		// Get the ItemID of the page to redirect after logout
		$app    = JFactory::getApplication();
		$itemid = $app->getMenu()->getActive()->params->get('logout');

		// Get the language of the page when multilang is on
		if (JLanguageMultilang::isEnabled())
		{
			if ($itemid)
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('language')
					->from($db->quoteName('#__menu'))
					->where('client_id = 0')
					->where('id =' . $itemid);

				$db->setQuery($query);

				try
				{
					$language = $db->loadResult();
				}
				catch (RuntimeException $e)
				{
					return;
				}

				if ($language !== '*')
				{
					$lang = '&lang=' . $language;
				}
				else
				{
					$lang = '';
				}

				// URL to redirect after logout
				$url = 'index.php?Itemid=' . $itemid . $lang;
			}
			else
			{
				// Logout is set to default. Get the home page ItemID
				$lang_code = $app->input->cookie->getString(JApplicationHelper::getHash('language'));
				$item      = $app->getMenu()->getDefault($lang_code);
				$itemid    = $item->id;

				// Redirect to Home page after logout
				$url = 'index.php?Itemid=' . $itemid;
			}
		}
		else
		{
			// URL to redirect after logout, default page if no ItemID is set
			$url = $itemid ? 'index.php?Itemid=' . $itemid : JUri::root();
		}

		// Logout and redirect
		$this->setRedirect('index.php?option=com_users&task=user.logout&' . JSession::getFormToken() . '=1&return=' . base64_encode($url));
	}

	/**
	 * Method to register a user.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function register()
	{
		JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		// Get the application
		$app = JFactory::getApplication();

		// Get the form data.
		$data = $this->input->post->get('user', array(), 'array');

		// Get the model and validate the data.
		$model  = $this->getModel('Registration', 'UsersModel');

		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		$return = $model->validate($form, $data);

		// Check for errors.
		if ($return === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');

					continue;
				}

				$app->enqueueMessage($errors[$i], 'notice');
			}

			// Save the data in the session.
			$app->setUserState('users.registration.form.data', $data);

			// Redirect back to the registration form.
			$this->setRedirect('index.php?option=com_users&view=registration');

			return false;
		}

		// Finish the registration.
		$return = $model->register($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('users.registration.form.data', $data);

			// Redirect back to the registration form.
			$message = JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_users&view=registration', $message, 'error');

			return false;
		}

		// Flush the data from the session.
		$app->setUserState('users.registration.form.data', null);

		return true;
	}

	/**
	 * Method to login a user.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function remind()
	{
		// Check the request token.
		JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		$app   = JFactory::getApplication();
		$model = $this->getModel('User', 'UsersModel');
		$data  = $this->input->post->get('jform', array(), 'array');

		// Submit the username remind request.
		$return = $model->processRemindRequest($data);

		// Check for a hard error.
		if ($return instanceof Exception)
		{
			// Get the error message to display.
			$message = $app->get('error_reporting')
				? $return->getMessage()
				: JText::_('COM_USERS_REMIND_REQUEST_ERROR');

			// Get the route to the next page.
			$itemid = UsersHelperRoute::getRemindRoute();
			$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
			$route  = 'index.php?option=com_users&view=remind' . $itemid;

			// Go back to the complete form.
			$this->setRedirect(JRoute::_($route, false), $message, 'error');

			return false;
		}

		if ($return === false)
		{
			// Complete failed.
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getRemindRoute();
			$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
			$route  = 'index.php?option=com_users&view=remind' . $itemid;

			// Go back to the complete form.
			$message = JText::sprintf('COM_USERS_REMIND_REQUEST_FAILED', $model->getError());
			$this->setRedirect(JRoute::_($route, false), $message, 'notice');

			return false;
		}

		// Complete succeeded.
		// Get the route to the next page.
		$itemid = UsersHelperRoute::getLoginRoute();
		$itemid = $itemid !== null ? '&Itemid=' . $itemid : '';
		$route	= 'index.php?option=com_users&view=login' . $itemid;

		// Proceed to the login form.
		$message = JText::_('COM_USERS_REMIND_REQUEST_SUCCESS');
		$this->setRedirect(JRoute::_($route, false), $message);

		return true;
	}

	/**
	 * Method to login a user.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function resend()
	{
		// Check for request forgeries
		JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));
	}
}
