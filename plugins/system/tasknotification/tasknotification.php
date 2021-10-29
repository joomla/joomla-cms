<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.Tasknotification
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use PHPMailer\PHPMailer\Exception as MailerException;

/**
 * This plugin implements email notification functionality for Tasks configured through the Scheduler component.
 * Notification configuration is supported on a per-task basis, which can be set-up through the Task item form, made
 * possible by injecting the notification fields into the item form with a `onContentPrepareForm` listener.<br/>
 *
 * Notifications can be set-up on: task success, failure, fatal failure (task running too long or crashing the request),
 * or on _orphaned_ task routines (missing parent plugin - either uninstalled, disabled or no longer offering a routine
 * with the same ID).
 *
 * @since __DEPLOY_VERSION__
 */
class PlgSystemTasknotification extends CMSPlugin implements SubscriberInterface
{
	/**
	 * The task notification form. This form is merged into the task item form by {@see
	 * injectTaskNotificationFieldset()}.
	 *
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	private const TASK_NOTIFICATION_FORM = 'task_notification';

	/**
	 * @var  CMSApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * @var  DatabaseInterface
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * @var boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;


	/**
	 * @inheritDoc
	 *
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onContentPrepareForm'  => 'injectTaskNotificationFieldset',
			'onTaskExecuteSuccess'  => 'notifySuccess',
			'onTaskExecuteFailure'  => 'notifyFailure',
			'onTaskRoutineNotFound' => 'notifyOrphan',
			'onTaskRecoverFailure'  => 'notifyFatalRecovery',
		];
	}

	/**
	 * Inject fields to support configuration of post-execution notifications into the task item form.
	 *
	 * @param   EventInterface  $event  The onContentPrepareForm event.
	 *
	 * @return boolean True if successful.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function injectTaskNotificationFieldset(EventInterface $event): bool
	{
		/** @var Form $form */
		$form = $event->getArgument('0');

		if ($form->getName() !== 'com_scheduler.task')
		{
			return true;
		}

		$formFile = __DIR__ . "/forms/" . self::TASK_NOTIFICATION_FORM . '.xml';

		try
		{
			$formFile = Path::check($formFile);
		}
		catch (Exception $e)
		{
			// Log?
			return false;
		}

		if (!File::exists($formFile))
		{
			return false;
		}

		return $form->loadFile($formFile);
	}

	/**
	 * @param   Event  $event  The onTaskExecuteFailure event.
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 * @throws Exception
	 */
	public function notifyFailure(Event $event): void
	{
		if (!(int) $this->params->get('failure_mail', 1))
		{
			return;
		}

		// @todo safety checks, multiple files [?]
		$outFile = $event->getArgument('subject')->snapshot['output_file'] ?? '';
		$data    = $this->getDataFromTask($event->getArgument('subject'));
		$this->sendMail('plg_system_tasknotification.failure_mail', $data, $outFile);
	}

	/**
	 * @param   Event  $event  The onTaskRoutineNotFound event.
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 * @throws Exception
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
	 * @since __DEPLOY_VERSION__
	 * @throws Exception
	 */
	public function notifySuccess(Event $event): void
	{
		if (!(int) $this->params->get('success_mail', 0))
		{
			return;
		}

		// @todo safety checks, multiple files [?]
		$outFile = $event->getArgument('subject')->snapshot['output_file'] ?? '';
		$data    = $this->getDataFromTask($event->getArgument('subject'));
		$this->sendMail('plg_system_tasknotification.success_mail', $data, $outFile);
	}

	/**
	 * @param   Event  $event  The onTaskRecoverFailure event.
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 * @throws Exception
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
			'EXEC_DATE_TIME' => $lockOrExecTime,
			'TASK_OUTPUT'    => $task->snapshot['output_body'] ?? '',
		];
	}

	/**
	 * @param   string  $template    The mail template.
	 * @param   array   $data        The data to bind to the mail template.
	 * @param   string  $attachment  The attachment to send with the mail (@todo multiple)
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 * @throws Exception
	 */
	private function sendMail(string $template, array $data, string $attachment = ''): void
	{
		$app = $this->app;
		$db  = $this->db;

		/** @var UserFactoryInterface $userFactory */
		$userFactory = Factory::getContainer()->get('user.factory');

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
			$user = $userFactory->loadUserById($user->id);

			if ($user->authorise('core.manage', 'com_scheduler'))
			{
				try
				{
					$mailer = new MailTemplate($template, $app->getLanguage()->getTag());
					$mailer->addTemplateData($data);
					$mailer->addRecipient($user->email);

					// @todo improve and make safe
					if ($attachment)
					{
						// @todo we allow multiple files
						$attachName = pathinfo($attachment, PATHINFO_BASENAME);
						$mailer->addAttachment($attachName, $attachment);
					}

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
