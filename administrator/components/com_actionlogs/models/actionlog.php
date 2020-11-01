<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Utilities\IpHelper;

JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

/**
 * Methods supporting a list of Actionlog records.
 *
 * @since  3.9.0
 */
class ActionlogsModelActionlog extends JModelLegacy
{
	/**
	 * Function to add logs to the database
	 * This method adds a record to #__action_logs contains (message_language_key, message, date, context, user)
	 *
	 * @param   array    $messages            The contents of the messages to be logged
	 * @param   string   $messageLanguageKey  The language key of the message
	 * @param   string   $context             The context of the content passed to the plugin
	 * @param   integer  $userId              ID of user perform the action, usually ID of current logged in user
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function addLog($messages, $messageLanguageKey, $context, $userId = null)
	{
		$user   = Factory::getUser($userId);
		$db     = $this->getDbo();
		$date   = Factory::getDate();
		$params = ComponentHelper::getComponent('com_actionlogs')->getParams();

		if ($params->get('ip_logging', 0))
		{
			$ip = IpHelper::getIp();

			if (!filter_var($ip, FILTER_VALIDATE_IP))
			{
				$ip = 'COM_ACTIONLOGS_IP_INVALID';
			}
		}
		else
		{
			$ip = 'COM_ACTIONLOGS_DISABLED';
		}

		$loggedMessages = array();

		foreach ($messages as $message)
		{
			$logMessage                       = new stdClass;
			$logMessage->message_language_key = $messageLanguageKey;
			$logMessage->message              = json_encode($message);
			$logMessage->log_date             = (string) $date;
			$logMessage->extension            = $context;
			$logMessage->user_id              = $user->id;
			$logMessage->ip_address           = $ip;
			$logMessage->item_id              = isset($message['id']) ? (int) $message['id'] : 0;

			try
			{
				$db->insertObject('#__action_logs', $logMessage);
				$loggedMessages[] = $logMessage;
			}
			catch (RuntimeException $e)
			{
				// Ignore it
			}
		}

		// Send notification email to users who choose to be notified about the action logs
		$this->sendNotificationEmails($loggedMessages, $user->name, $context);
	}

	/**
	 * Send notification emails about the action log
	 *
	 * @param   array   $messages  The logged messages
	 * @param   string  $username  The username
	 * @param   string  $context   The Context
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function sendNotificationEmails($messages, $username, $context)
	{
		$db           = $this->getDbo();
		$query        = $db->getQuery(true);
		$params       = ComponentHelper::getParams('com_actionlogs');
		$showIpColumn = (bool) $params->get('ip_logging', 0);

		$query
			->select($db->quoteName(array('u.email', 'l.extensions')))
			->from($db->quoteName('#__users', 'u'))
			->join(
				'INNER',
				$db->quoteName('#__action_logs_users', 'l') . ' ON ( ' . $db->quoteName('l.notify') . ' = 1 AND '
				. $db->quoteName('l.user_id') . ' = ' . $db->quoteName('u.id') . ')'
			);

		$db->setQuery($query);

		try
		{
			$users = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());

			return;
		}

		$recipients = array();

		foreach ($users as $user)
		{
			$extensions = json_decode($user->extensions, true);

			if ($extensions && in_array(strtok($context, '.'), $extensions))
			{
				$recipients[] = $user->email;
			}
		}

		if (empty($recipients))
		{
			return;
		}

		$layout    = new FileLayout('components.com_actionlogs.layouts.logstable', JPATH_ADMINISTRATOR);
		$extension = strtok($context, '.');
		ActionlogsHelper::loadTranslationFiles($extension);

		foreach ($messages as $message)
		{
			$message->extension = Text::_($extension);
			$message->message   = ActionlogsHelper::getHumanReadableLogMessage($message);
		}

		$displayData = array(
			'messages'     => $messages,
			'username'     => $username,
			'showIpColumn' => $showIpColumn,
		);

		$body   = $layout->render($displayData);
		$mailer = Factory::getMailer();
		$mailer->addRecipient($recipients);
		$mailer->setSubject(Text::_('COM_ACTIONLOGS_EMAIL_SUBJECT'));
		$mailer->isHTML(true);
		$mailer->Encoding = 'base64';
		$mailer->setBody($body);

		if (!$mailer->Send())
		{
			JError::raiseWarning(500, Text::_('JERROR_SENDING_EMAIL'));
		}
	}
}
