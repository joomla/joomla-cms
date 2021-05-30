<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\Plugin;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Abstract Action Log Plugin
 *
 * @since  3.9.0
 */
abstract class ActionLogPlugin extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  3.9.0
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    \Joomla\Database\DatabaseDriver
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Proxy for ActionlogsModelUserlog addLog method
	 *
	 * This method adds a record to #__action_logs contains (message_language_key, message, date, context, user)
	 *
	 * @param   array   $messages            The contents of the messages to be logged
	 * @param   string  $messageLanguageKey  The language key of the message
	 * @param   string  $context             The context of the content passed to the plugin
	 * @param   int     $userId              ID of user perform the action, usually ID of current logged in user
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function addLog($messages, $messageLanguageKey, $context, $userId = null)
	{
		$user = Factory::getUser();

		foreach ($messages as $index => $message)
		{
			if (!array_key_exists('userid', $message))
			{
				$message['userid'] = $user->id;
			}

			if (!array_key_exists('username', $message))
			{
				$message['username'] = $user->username;
			}

			if (!array_key_exists('accountlink', $message))
			{
				$message['accountlink'] = 'index.php?option=com_users&task=user.edit&id=' . $user->id;
			}

			if (array_key_exists('type', $message))
			{
				$message['type'] = strtoupper($message['type']);
			}

			if (array_key_exists('app', $message))
			{
				$message['app'] = strtoupper($message['app']);
			}

			$messages[$index] = $message;
		}

		/** @var \Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel $model */
		$model = $this->app->bootComponent('com_actionlogs')
			->getMVCFactory()->createModel('Actionlog', 'Administrator', ['ignore_request' => true]);

		$model->addLog($messages, strtoupper($messageLanguageKey), $context, $userId);
	}
}
