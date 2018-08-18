<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\FormModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\String\PunycodeHelper;

/**
 * Remind model class for Users.
 *
 * @since  1.5
 */
class RemindModel extends FormModel
{
	/**
	 * Method to get the username remind request form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \JFor     A \JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_users.remind', 'remind', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Override preprocessForm to load the user plugin group instead of content.
	 *
	 * @param   \JForm  $form   A \JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @throws	\Exception if there is an error in the form event.
	 *
	 * @since   1.6
	 */
	protected function preprocessForm(\JForm $form, $data, $group = 'user')
	{
		parent::preprocessForm($form, $data, 'user');
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
		$app = Factory::getApplication();
		$params = $app->getParams('com_users');

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Send the remind username email
	 *
	 * @param   array  $data  Array with the data received from the form
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function processRemindRequest($data)
	{
		// Get the form.
		$form = $this->getForm();
		$data['email'] = PunycodeHelper::emailToPunycode($data['email']);

		// Check for an error.
		if (empty($form))
		{
			return false;
		}

		// Validate the data.
		$data = $this->validate($form, $data);

		// Check for an error.
		if ($data instanceof \Exception)
		{
			return false;
		}

		// Check the validation results.
		if ($data === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $formError)
			{
				$this->setError($formError->getMessage());
			}

			return false;
		}

		// Find the user id for the given email address.
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('email') . ' = ' . $db->quote($data['email']));

		// Get the user id.
		$db->setQuery($query);

		try
		{
			$user = $db->loadObject();
		}
		catch (\RuntimeException $e)
		{
			$this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 500);

			return false;
		}

		// Check for a user.
		if (empty($user))
		{
			$this->setError(Text::_('COM_USERS_USER_NOT_FOUND'));

			return false;
		}

		// Make sure the user isn't blocked.
		if ($user->block)
		{
			$this->setError(Text::_('COM_USERS_USER_BLOCKED'));

			return false;
		}

		$config = Factory::getConfig();

		// Assemble the login link.
		$link = 'index.php?option=com_users&view=login';
		$mode = $config->get('force_ssl', 0) == 2 ? 1 : (-1);

		// Put together the email template data.
		$data = ArrayHelper::fromObject($user);
		$data['fromname'] = $config->get('fromname');
		$data['mailfrom'] = $config->get('mailfrom');
		$data['sitename'] = $config->get('sitename');
		$data['link_text'] = Route::_($link, false, $mode);
		$data['link_html'] = Route::_($link, true, $mode);

		$subject = Text::sprintf(
			'COM_USERS_EMAIL_USERNAME_REMINDER_SUBJECT',
			$data['sitename']
		);
		$body = Text::sprintf(
			'COM_USERS_EMAIL_USERNAME_REMINDER_BODY',
			$data['sitename'],
			$data['username'],
			$data['link_text']
		);

		// Send the password reset request email.
		$return = Factory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $user->email, $subject, $body);

		// Check for an error.
		if ($return !== true)
		{
			$this->setError(Text::_('COM_USERS_MAIL_FAILED'), 500);

			return false;
		}

		return true;
	}
}
