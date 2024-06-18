<?php

/**
 * @package     Joomla.Plugins
 * @subpackage  System.tasknotification
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\TaskNotification\Extension;

use Joomla\CMS\Event\Model;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Task\Task;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\Path;
use PHPMailer\PHPMailer\Exception as MailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This plugin implements email notification functionality for Tasks configured through the Scheduler component.
 * Notification configuration is supported on a per-task basis, which can be set-up through the Task item form, made
 * possible by injecting the notification fields into the item form with a `onContentPrepareForm` listener.<br/>
 *
 * Notifications can be set-up on: task success, failure, fatal failure (task running too long or crashing the request),
 * or on _orphaned_ task routines (missing parent plugin - either uninstalled, disabled or no longer offering a routine
 * with the same ID).
 *
 * @since 4.1.0
 */
final class TaskNotification extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

    /**
     * The task notification form. This form is merged into the task item form by {@see
     * injectTaskNotificationFieldset()}.
     *
     * @var string
     * @since 4.1.0
     */
    private const TASK_NOTIFICATION_FORM = 'task_notification';

    /**
     * @inheritDoc
     *
     * @return array
     *
     * @since 4.1.0
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
     * @param   Model\PrepareFormEvent  $event  The onContentPrepareForm event.
     *
     * @return boolean True if successful.
     *
     * @since 4.1.0
     */
    public function injectTaskNotificationFieldset(Model\PrepareFormEvent $event): bool
    {
        $form = $event->getForm();

        if ($form->getName() !== 'com_scheduler.task') {
            return true;
        }

        // Load translations
        $this->loadLanguage();

        $formFile = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms/' . self::TASK_NOTIFICATION_FORM . '.xml';

        try {
            $formFile = Path::check($formFile);
        } catch (\Exception $e) {
            // Log?
            return false;
        }

        $formFile = Path::clean($formFile);

        if (!is_file($formFile)) {
            return false;
        }

        return $form->loadFile($formFile);
    }

    /**
     * Send out email notifications on Task execution failure if task configuration allows it.
     *
     * @param   Event  $event  The onTaskExecuteFailure event.
     *
     * @return void
     *
     * @since 4.1.0
     * @throws \Exception
     */
    public function notifyFailure(Event $event): void
    {
        /** @var Task $task */
        $task = $event->getArgument('subject');

        if (!(int) $task->get('params.notifications.failure_mail', 1)) {
            return;
        }

        // Load translations
        $this->loadLanguage();

        // @todo safety checks, multiple files [?]
        $outFile = $event->getArgument('subject')->snapshot['output_file'] ?? '';
        $data    = $this->getDataFromTask($event->getArgument('subject'));
        $this->sendMail('plg_system_tasknotification.failure_mail', $data, $outFile);
    }

    /**
     * Send out email notifications on orphaned task if task configuration allows.<br/>
     * A task is `orphaned` if the task's parent plugin has been removed/disabled, or no longer offers a task
     * with the same routine ID.
     *
     * @param   Event  $event  The onTaskRoutineNotFound event.
     *
     * @return void
     *
     * @since 4.1.0
     * @throws \Exception
     */
    public function notifyOrphan(Event $event): void
    {
        /** @var Task $task */
        $task = $event->getArgument('subject');

        if (!(int) $task->get('params.notifications.orphan_mail', 1)) {
            return;
        }

        // Load translations
        $this->loadLanguage();

        $data = $this->getDataFromTask($event->getArgument('subject'));
        $this->sendMail('plg_system_tasknotification.orphan_mail', $data);
    }

    /**
     * Send out email notifications on Task execution success if task configuration allows.
     *
     * @param   Event  $event  The onTaskExecuteSuccess event.
     *
     * @return void
     *
     * @since 4.1.0
     * @throws \Exception
     */
    public function notifySuccess(Event $event): void
    {
        /** @var Task $task */
        $task = $event->getArgument('subject');

        if (!(int) $task->get('params.notifications.success_mail', 0)) {
            return;
        }

        // Load translations
        $this->loadLanguage();

        // @todo safety checks, multiple files [?]
        $outFile = $event->getArgument('subject')->snapshot['output_file'] ?? '';
        $data    = $this->getDataFromTask($event->getArgument('subject'));
        $this->sendMail('plg_system_tasknotification.success_mail', $data, $outFile);
    }

    /**
     * Send out email notifications on fatal recovery of task execution if task configuration allows.<br/>
     * Fatal recovery indicated that the task either crashed the parent process or its execution lasted longer
     * than the global task timeout (this is configurable through the Scheduler component configuration).
     * In the latter case, the global task timeout should be adjusted so that this false positive can be avoided.
     * This stands as a limitation of the Scheduler's current task execution implementation, which doesn't involve
     * keeping track of the parent PHP process which could enable keeping track of the task's status.
     *
     * @param   Event  $event  The onTaskRecoverFailure event.
     *
     * @return void
     *
     * @since 4.1.0
     * @throws \Exception
     */
    public function notifyFatalRecovery(Event $event): void
    {
        /** @var Task $task */
        $task = $event->getArgument('subject');

        if (!(int) $task->get('params.notifications.fatal_failure_mail', 1)) {
            return;
        }

        // Load translations
        $this->loadLanguage();

        $data = $this->getDataFromTask($event->getArgument('subject'));
        $this->sendMail('plg_system_tasknotification.fatal_recovery_mail', $data);
    }

    /**
     * @param   Task  $task  A task object
     *
     * @return array  An array of data to bind to a mail template.
     *
     * @since 4.1.0
     */
    private function getDataFromTask(Task $task): array
    {
        $lockOrExecTime = Factory::getDate($task->get('locked') ?? $task->get('last_execution'))->format($this->getApplication()->getLanguage()->_('DATE_FORMAT_LC2'));

        return [
            'TASK_ID'        => $task->get('id'),
            'TASK_TITLE'     => $task->get('title'),
            'EXIT_CODE'      => $task->getContent()['status'] ?? Status::NO_EXIT,
            'EXEC_DATE_TIME' => $lockOrExecTime,
            'TASK_OUTPUT'    => $task->getContent()['output_body'] ?? '',
            'TASK_EXCEPTION' => ($task->getContent()['exception']?->getMessage() ?? '')
                . "\n\n" // This might result in one or two empty strings with new Lines. Not too visible in email or HTML, so not really worth the effort to add a lot of extra code to optimize this.
                . Path::removeRoot($task->getContent()['exception']?->getTraceAsString() ?? ''),
        ];
    }

    /**
     * @param   string  $template    The mail template.
     * @param   array   $data        The data to bind to the mail template.
     * @param   string  $attachment  The attachment to send with the mail (@todo multiple)
     *
     * @return void
     *
     * @since 4.1.0
     * @throws \Exception
     */
    private function sendMail(string $template, array $data, string $attachment = ''): void
    {
        $app = $this->getApplication();
        $db  = $this->getDatabase();

        // Get all users who are not blocked and have opted in for system mails.
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['name', 'email', 'sendEmail', 'id']))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('sendEmail') . ' = 1')
            ->where($db->quoteName('block') . ' = 0');

        $db->setQuery($query);

        try {
            $users = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            return;
        }

        if ($users === null) {
            Log::add($this->getApplication()->getLanguage()->_('PLG_SYSTEM_TASK_NOTIFICATION_USER_FETCH_FAIL'), Log::ERROR);

            return;
        }

        $mailSent = false;

        // Mail all matching users who also have the `core.manage` privilege for com_scheduler.
        foreach ($users as $user) {
            $user = $this->getUserFactory()->loadUserById($user->id);

            if ($user->authorise('core.manage', 'com_scheduler')) {
                try {
                    $mailer = new MailTemplate($template, $app->getLanguage()->getTag());
                    $mailer->addTemplateData($data);
                    $mailer->addRecipient($user->email);

                    if (
                        !empty($attachment)
                        && is_file($attachment)
                    ) {
                        // @todo we allow multiple files [?]
                        $attachName = pathinfo($attachment, PATHINFO_BASENAME);
                        $mailer->addAttachment($attachName, $attachment);
                    }

                    $mailer->send();
                    $mailSent = true;
                } catch (MailerException $exception) {
                    Log::add($this->getApplication()->getLanguage()->_('PLG_SYSTEM_TASK_NOTIFICATION_NOTIFY_SEND_EMAIL_FAIL'), Log::ERROR);
                }
            }
        }

        if (!$mailSent) {
            Log::add($this->getApplication()->getLanguage()->_('PLG_SYSTEM_TASK_NOTIFICATION_NO_MAIL_SENT'), Log::WARNING);
        }
    }
}
