<?php
/**
 * @package       Joomla.Plugins
 * @subpackage    System.Tasknotification
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Notifications for (scheduled) task executions. */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use PHPMailer\PHPMailer\Exception as MailerException;

/**
 * Plugin class
 *
 * @since __DEPLOY_VERSION__
 */
class PlgSystemTasknotification extends CMSPlugin implements SubscriberInterface
{
	/**
	 * @var  DatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * @var  CMSApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * @var boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;


	/**
	 * Returns event subscriptions
	 *
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskExecuteSuccess'  => 'notifySuccess',
			'onTaskExecuteFailure'  => 'notifyFailure',
			'onTaskRoutineNotFound' => 'notifyOrphan',
			'onTaskRecoverFailure'  => 'notifyFatalRecovery'
		];
	}

	/**
	 * @param   Event  $event  The onTaskExecuteFailure event.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function notifyFailure(Event $event): void
	{
		if (!(int) $this->params->get('failure_mail', 1))
		{
			return;
		}

		$data = $this->getDataFromTask($event->getArgument('subject'));
		$this->sendMail('plg_system_tasknotification.failure_mail', $data);
	}

	/**
	 * @param   Event  $event  The onTaskRoutineNotFound event.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function notifyOrphan(Event $event): void
	{
		if (!(int) $this->params->get('orphan_mail', 1))
		{
			return;
		}

		$data = $this->getDataFromTask($event->getArgument('subject'));
		$this->sendMail('plg_system_tasknotification.orphan_mail', $data);
	}

	/**
	 * @param   Event  $event  The onTaskExecuteSuccess event.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function notifySuccess(Event $event): void
	{
		if (!(int) $this->params->get('success_mail', 0))
		{
			return;
		}

		$data = $this->getDataFromTask($event->getArgument('subject'));
		$this->sendMail('plg_system_tasknotification.success_mail', $data);
	}

	/**
	 * @param   Event  $event  The onTaskRecoverFailure event.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function notifyFatalRecovery(Event $event): void
	{
		if (!(int) $this->params->get('fatal_failure_mail', 1))
		{
			return;
		}

		$data = $this->getDataFromTask($event->getArgument('subject'));
		$this->sendMail('plg_system_tasknotification.fatal_recovery_mail', $data);
	}

	/**
	 * @param   Task  $task  A task object
	 *
	 * @return array  An array of data to bind to a mail template.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function getDataFromTask(Task $task): array
	{
		$lockOrExecTime = Factory::getDate($task->get('locked') ?? $task->get('last_execution'))->toRFC822();

		return [
			'TASK_ID'        => $task->get('id'),
			'TASK_TITLE'     => $task->get('title'),
			'EXIT_CODE'      => $task->snapshot['status'] ?? Status::NO_EXIT,
			'EXEC_DATE_TIME' => $lockOrExecTime
		];
	}

	/**
	 * @param   string  $template  The mail template.
	 * @param   array   $data      The data to bind to the mail template.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function sendMail(string $template, array $data): void
	{
		$app = $this->app;
		$db = $this->db;

		/** @var UserFactoryInterface $uFactory */
		$uFactory = Factory::getContainer()->get('user.factory');

		// Get all admin users
		$query = $db->getQuery(true);

		$query->select($db->qn(['name', 'email', 'sendEmail', 'id']))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('sendEmail') . ' = 1')
			->where($db->quoteName('block') . ' = 0');

		$db->setQuery($query);

		try
		{
			$users = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return;
		}

		// Mail all users with access to scheduler, opt-in mail
		foreach ($users as $user)
		{
			$user = $uFactory->loadUserById($user->id);

			if ($user->authorise('core.manage', 'com_scheduler'))
			{
				try
				{
					$mailer = new MailTemplate($template, $app->getLanguage()->getTag());
					$mailer->addTemplateData($data);
					$mailer->addRecipient($user->email);
					$mailer->send();
				}
				catch (MailerException $exception)
				{
					Log::Add(Text::_('PLG_SYSTEM_TASK_NOTIFICATION_NOTIFY_SEND_EMAIL_FAIL'));
				}
			}
		}
	}
}
