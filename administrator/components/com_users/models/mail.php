<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Users mail model.
 *
 * @since  1.6
 */
class UsersModelMail extends JModelAdmin
{
	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm	A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_users.mail', 'mail', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
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
		$data = JFactory::getApplication()->getUserState('com_users.display.mail.data', array());

		$this->preprocessData('com_users.mail', $data);

		return $data;
	}

	/**
	 * Method to preprocess the form
	 *
	 * @param   JForm   $form   A form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  Exception if there is an error loading the form.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'user')
	{
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Send the email
	 *
	 * @return  boolean
	 */
	public function send()
	{
		$app    = JFactory::getApplication();
		$data   = $app->input->post->get('jform', array(), 'array');
		$user   = JFactory::getUser();
		$access = new JAccess;
		$db     = $this->getDbo();

		$mode         = array_key_exists('mode', $data) ? (int) $data['mode'] : 0;
		$subject      = array_key_exists('subject', $data) ? $data['subject'] : '';
		$grp          = array_key_exists('group', $data) ? (int) $data['group'] : 0;
		$recurse      = array_key_exists('recurse', $data) ? (int) $data['recurse'] : 0;
		$bcc          = array_key_exists('bcc', $data) ? (int) $data['bcc'] : 0;
		$disabled     = array_key_exists('disabled', $data) ? (int) $data['disabled'] : 0;
		$message_body = array_key_exists('message', $data) ? $data['message'] : '';

		// Automatically removes html formatting
		if (!$mode)
		{
			$message_body = JFilterInput::getInstance()->clean($message_body, 'string');
		}

		// Check for a message body and subject
		if (!$message_body || !$subject)
		{
			$app->setUserState('com_users.display.mail.data', $data);
			$this->setError(JText::_('COM_USERS_MAIL_PLEASE_FILL_IN_THE_FORM_CORRECTLY'));

			return false;
		}

		// Get users in the group out of the ACL, if group is provided.
		$to = $grp !== 0 ? $access->getUsersByGroup($grp, $recurse) : array();

		// When group is provided but no users are found in the group.
		if ($grp !== 0 && !$to)
		{
			$rows = array();
		}
		else
		{
			// Get all users email and group except for senders
			$query = $db->getQuery(true)
				->select($db->quoteName('email'))
				->from($db->quoteName('#__users'))
				->where($db->quoteName('id') . ' != ' . (int) $user->id);

			if ($grp !== 0)
			{
				$query->where($db->quoteName('id') . ' IN (' . implode(',', $to) . ')');
			}

			if ($disabled === 0)
			{
				$query->where($db->quoteName('block') . ' = 0');
			}

			$db->setQuery($query);
			$rows = $db->loadColumn();
		}

		// Check to see if there are any users in this group before we continue
		if (!$rows)
		{
			$app->setUserState('com_users.display.mail.data', $data);

			if (in_array($user->id, $to))
			{
				$this->setError(JText::_('COM_USERS_MAIL_ONLY_YOU_COULD_BE_FOUND_IN_THIS_GROUP'));
			}
			else
			{
				$this->setError(JText::_('COM_USERS_MAIL_NO_USERS_COULD_BE_FOUND_IN_THIS_GROUP'));
			}

			return false;
		}

		// Get the Mailer
		$mailer = JFactory::getMailer();
		$params = JComponentHelper::getParams('com_users');

		// Build email message format.
		$mailer->setSender(array($app->get('mailfrom'), $app->get('fromname')));
		$mailer->setSubject($params->get('mailSubjectPrefix') . stripslashes($subject));
		$mailer->setBody($message_body . $params->get('mailBodySuffix'));
		$mailer->IsHtml($mode);

		// Add recipients
		if ($bcc)
		{
			$mailer->addBcc($rows);
			$mailer->addRecipient($app->get('mailfrom'));
		}
		else
		{
			$mailer->addRecipient($rows);
		}

		// Send the Mail
		$rs = $mailer->Send();

		// Check for an error
		if ($rs instanceof Exception)
		{
			$app->setUserState('com_users.display.mail.data', $data);
			$this->setError($rs->getError());

			return false;
		}
		elseif (empty($rs))
		{
			$app->setUserState('com_users.display.mail.data', $data);
			$this->setError(JText::_('COM_USERS_MAIL_THE_MAIL_COULD_NOT_BE_SENT'));

			return false;
		}
		else
		{
			/**
			 * Fill the data (specially for the 'mode', 'group' and 'bcc': they could not exist in the array
			 * when the box is not checked and in this case, the default value would be used instead of the '0'
			 * one)
			 */
			$data['mode']    = $mode;
			$data['subject'] = $subject;
			$data['group']   = $grp;
			$data['recurse'] = $recurse;
			$data['bcc']     = $bcc;
			$data['message'] = $message_body;
			$app->setUserState('com_users.display.mail.data', array());
			$app->enqueueMessage(JText::plural('COM_USERS_MAIL_EMAIL_SENT_TO_N_USERS', count($rows)), 'message');

			return true;
		}
	}
}
