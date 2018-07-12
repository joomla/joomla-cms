<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Site\Controller;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;

defined('_JEXEC') or die;

/**
 * Registration controller class for Users.
 *
 * @since  1.6
 */
class UserController extends FormController
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
	 *
	 * @since   1.6.4
	 */
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	/**
	 * Method to log in a user.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function login()
	{
		$this->checkToken('post');

		$app    = $this->app;
		$input  = $this->input;
		$method = $input->getMethod();

		// Populate the data array:
		$data = array();

		$data['return']    = base64_decode($this->input->post->get('return', '', 'BASE64'));
		$data['username']  = $input->$method->get('username', '', 'USERNAME');
		$data['password']  = $input->$method->get('password', '', 'RAW');
		$data['secretkey'] = $input->$method->get('secretkey', '', 'RAW');

		// Check for a simple menu item id
		if (is_numeric($data['return']))
		{
			if (Multilanguage::isEnabled())
			{
				$db = \JFactory::getDbo();
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
				catch (\RuntimeException $e)
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
			if (!\JUri::isInternal($data['return']))
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
			$app->redirect(\JRoute::_('index.php?option=com_users&view=login', false));
		}

		// Success
		if ($options['remember'] == true)
		{
			$app->setUserState('rememberLogin', true);
		}

		$app->setUserState('users.login.form.data', array());
		$app->redirect(\JRoute::_($app->getUserState('users.login.form.return'), false));
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
		$this->checkToken('request');

		$app = $this->app;

		// Prepare the logout options.
		$options = array(
			'clientid' => $app->get('shared_session', '0') ? null : 0,
		);

		// Perform the log out.
		$error  = $app->logout(null, $options);
		$input  = $app->input;
		$method = $input->getMethod();

		// Check if the log out succeeded.
		if ($error instanceof \Exception)
		{
			$app->redirect(\JRoute::_('index.php?option=com_users&view=login', false));
		}

		// Get the return URL from the request and validate that it is internal.
		$return = $input->$method->get('return', '', 'BASE64');
		$return = base64_decode($return);

		// Check for a simple menu item id
		if (is_numeric($return))
		{
			if (Multilanguage::isEnabled())
			{
				$db = \JFactory::getDbo();
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
				catch (\RuntimeException $e)
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
		}
		else
		{
			// Don't redirect to an external URL.
			if (!\JUri::isInternal($return))
			{
				$return = '';
			}
		}

		// In case redirect url is not set, redirect user to homepage
		if (empty($return))
		{
			$return = \JUri::root();
		}

		// Redirect the user.
		$app->redirect(\JRoute::_($return, false));
	}

	/**
	 * Method to logout directly and redirect to page.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function menulogout()
	{
		// Get the ItemID of the page to redirect after logout
		$app    = $this->app;
		$itemid = $app->getMenu()->getActive()->params->get('logout');

		// Get the language of the page when multilang is on
		if (Multilanguage::isEnabled())
		{
			if ($itemid)
			{
				$db = \JFactory::getDbo();
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
				catch (\RuntimeException $e)
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
				$lang_code = $app->input->cookie->getString(ApplicationHelper::getHash('language'));
				$item      = $app->getMenu()->getDefault($lang_code);
				$itemid    = $item->id;

				// Redirect to Home page after logout
				$url = 'index.php?Itemid=' . $itemid;
			}
		}
		else
		{
			// URL to redirect after logout, default page if no ItemID is set
			$url = $itemid ? 'index.php?Itemid=' . $itemid : \JUri::root();
		}

		// Logout and redirect
		$this->setRedirect('index.php?option=com_users&task=user.logout&' . \JSession::getFormToken() . '=1&return=' . base64_encode($url));
	}

	/**
	 * Method to request a username reminder.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function remind()
	{
		// Check the request token.
		$this->checkToken('post');

		$app   = $this->app;
		/* @var \Joomla\Component\Users\Site\Model\RemindModel $model */
		$model = $this->getModel('Remind', 'Site');
		$data  = $this->input->post->get('jform', array(), 'array');

		// Submit the username remind request.
		$return = $model->processRemindRequest($data);

		// Check for a hard error.
		if ($return instanceof \Exception)
		{
			// Get the error message to display.
			$message = $app->get('error_reporting')
				? $return->getMessage()
				: \JText::_('COM_USERS_REMIND_REQUEST_ERROR');

			// Go back to the complete form.
			$this->setRedirect(\JRoute::_('index.php?option=com_users&view=remind', false), $message, 'error');

			return false;
		}

		if ($return === false)
		{
			// Go back to the complete form.
			$message = \JText::sprintf('COM_USERS_REMIND_REQUEST_FAILED', $model->getError());
			$this->setRedirect(\JRoute::_('index.php?option=com_users&view=remind', false), $message, 'notice');

			return false;
		}

		// Proceed to the login form.
		$message = \JText::_('COM_USERS_REMIND_REQUEST_SUCCESS');
		$this->setRedirect(\JRoute::_('index.php?option=com_users&view=login', false), $message);

		return true;
	}

	/**
	 * Method to resend a user.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function resend()
	{
		// Check for request forgeries
		// $this->checkToken('post');
	}

	/**
	 * Method to submit the contact form and send an email.
	 *
	 * @return  boolean  True on success sending the email. False on failure.
	 *
	 * @since   1.5.19
	 * @throws  \Exception
	 */
	public function submit()
	{
		// Check for request forgeries.
		$this->checkToken();

		$app    = Factory::getApplication();
		$model  = $this->getModel('user');

		$params = ComponentHelper::getParams('com_users');

		$stub   = $this->input->getString('id');
		$id     = (int) $stub;

		// Get the data from POST
		$data    = $this->input->post->get('jform', array(), 'array');
		$contact = $model->getItem($id);

		$params->merge($contact->params);

		// Check for a valid session cookie
		if ($params->get('validate_session', 0))
		{
			if (Factory::getSession()->getState() !== 'active')
			{
				$this->app->enqueueMessage(Text::_('JLIB_ENVIRONMENT_SESSION_INVALID'), 'warning');

				// Save the data in the session.
				$this->app->setUserState('com_users.contact.data', $data);

				// Redirect back to the contact form.
				$this->setRedirect(Route::_('index.php?option=com_users&view=user&id=' . $stub, false));

				return false;
			}
		}

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new \Exception($model->getError(), 500);

			return false;
		}

		if (!$model->validate($form, $data))
		{
			$errors = $model->getErrors();

			foreach ($errors as $error)
			{
				$errorMessage = $error;

				if ($error instanceof \Exception)
				{
					$errorMessage = $error->getMessage();
				}

				$app->enqueueMessage($errorMessage, 'error');
			}

			$app->setUserState('com_users.contact.data', $data);

			$this->setRedirect(Route::_('index.php?option=com_users&view=user&id=' . $stub, false));

			return false;
		}

		// Send the email
		$sent = $this->_sendEmail($data, $contact, $params->get('show_email_copy', 0));

		// Set the success message if it was a success
		if (!($sent instanceof \Exception))
		{
			$msg = Text::_('COM_USERS_CONTACT_EMAIL_THANKS');
		}
		else
		{
			$msg = '';
		}

		// Flush the data from the session
		$this->app->setUserState('com_users.contact.data', null);

		// Redirect if it is set in the parameters, otherwise redirect back to where we came from
		if ($contact->params->get('redirect'))
		{
			$this->setRedirect($contact->params->get('redirect'), $msg);
		}
		else
		{
			$this->setRedirect(Route::_('index.php?option=com_users&view=user&id=' . $stub, false), $msg);
		}

		return true;
	}

	/**
	 * Method to send an email.
	 *
	 * @param   array      $data                  The data to send in the email.
	 * @param   \stdClass  $contact               The user information to send the email to
	 * @param   boolean    $copy_email_activated  True to send a copy of the email to the user.
	 *
	 * @return  boolean  True on success sending the email, false on failure.
	 *
	 * @since   1.6.4
	 */
	private function _sendEmail($data, $contact, $copy_email_activated)
	{
		$app = $this->app;

		if ($contact->email == '' && $contact->id != 0)
		{
			$contact_user      = User::getInstance($contact->id);
			$contact->email = $contact_user->get('email');
		}

		$mailfrom = $app->get('mailfrom');
		$fromname = $app->get('fromname');
		$sitename = $app->get('sitename');

		$name    = $data['contact_name'];
		$email   = PunycodeHelper::emailToPunycode($data['contact_email']);
		$subject = $data['contact_subject'];
		$body    = $data['contact_message'];

		// Prepare email body
		$prefix = Text::sprintf('COM_USERS_CONTACT_ENQUIRY_TEXT', Uri::base());
		$body   = $prefix . "\n" . $name . ' <' . $email . '>' . "\r\n\r\n" . stripslashes($body);

		$mail = Factory::getMailer();
		$mail->addRecipient($contact->email);
		$mail->addReplyTo($email, $name);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($sitename . ': ' . $subject);
		$mail->setBody($body);
		$sent = $mail->Send();

		// Check whether email copy function activated
		if ($copy_email_activated == true && !empty($data['contact_email_copy']))
		{
			$copytext    = Text::sprintf('COM_USERS_CONTACT_COPYTEXT_OF', $contact->name, $sitename);
			$copytext    .= "\r\n\r\n" . $body;
			$copysubject = Text::sprintf('COM_USERS_CONTACT_COPYSUBJECT_OF', $subject);

			$mail = Factory::getMailer();
			$mail->addRecipient($email);
			$mail->addReplyTo($email, $name);
			$mail->setSender(array($mailfrom, $fromname));
			$mail->setSubject($copysubject);
			$mail->setBody($copytext);
			$sent = $mail->Send();
		}

		return $sent;
	}
}
