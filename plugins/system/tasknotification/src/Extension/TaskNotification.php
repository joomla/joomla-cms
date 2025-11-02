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
use Joomla\Component\Scheduler\Administrator\Helper\SchedulerHelper;
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
            'onContentPrepareForm'    => 'injectTaskNotificationFieldset',
            'onTaskExecuteSuccess'    => 'notifySuccess',
            'onTaskRoutineWillResume' => 'notifyWillResume',
            'onTaskExecuteFailure'    => 'notifyFailure',
            'onTaskRoutineNotFound'   => 'notifyOrphan',
            'onTaskRecoverFailure'    => 'notifyFatalRecovery',
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
        } catch (\Exception) {
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
        $data = $this->getDataFromTask($event->getArgument('subject'));
        $this->saveLog($data);

        if (!(int) $task->get('params.notifications.failure_mail', 1)) {
            return;
        }

        // Load translations
        $this->loadLanguage();
        $groups = $task->get('params.notifications.notification_failure_groups');

        // @todo safety checks, multiple files [?]
        $outFile = $event->getArgument('subject')->snapshot['output_file'] ?? '';
        $this->sendMail('plg_system_tasknotification.failure_mail', $data, $outFile, $groups);
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
        $groups = $task->get('params.notifications.notification_orphan_groups');

        $data = $this->getDataFromTask($event->getArgument('subject'));
        $this->sendMail('plg_system_tasknotification.orphan_mail', $data, '', $groups);
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
        $data = $this->getDataFromTask($event->getArgument('subject'));
        $this->saveLog($data);

        if (!(int) $task->get('params.notifications.success_mail', 0)) {
            return;
        }

        // Load translations
        $this->loadLanguage();
        $groups = $task->get('params.notifications.notification_success_groups');

        // @todo safety checks, multiple files [?]
        $outFile = $event->getArgument('subject')->snapshot['output_file'] ?? '';
        $this->sendMail('plg_system_tasknotification.success_mail', $data, $outFile, $groups);
    }

    /**
     * Log Task execution will resume.
     *
     * @param   Event  $event  The onTaskRoutineWillResume event.
     *
     * @return void
     *
     * @since 5.3.0
     * @throws \Exception
     */
    public function notifyWillResume(Event $event): void
    {
        $this->saveLog($this->getDataFromTask($event->getArgument('subject')));
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
        $groups = $task->get('params.notifications.notification_fatal_groups');

        $data = $this->getDataFromTask($event->getArgument('subject'));
        $this->sendMail('plg_system_tasknotification.fatal_recovery_mail', $data, '', $groups);
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
            'TASK_TYPE'      => $task->get('type'),
            'EXIT_CODE'      => $task->getContent()['status'] ?? Status::NO_EXIT,
            'EXEC_DATE_TIME' => $lockOrExecTime,
            'TASK_OUTPUT'    => $task->getContent()['output_body'] ?? '',
            'TASK_TIMES'     => $task->get('times_executed'),
            'TASK_DURATION'  => $task->getContent()['duration'],
        ];
    }

    /**
     * @param   string  $template    The mail template.
     * @param   array   $data        The data to bind to the mail template.
     * @param   string  $attachment  The attachment to send with the mail (@todo multiple)
     * @param   array   $groups      The user groups to notify.
     *
     * @return void
     *
     * @since 4.1.0
     * @throws \Exception
     */
    private function sendMail(string $template, array $data, string $attachment = '', array $groups = []): void
    {
        $app = $this->getApplication();
        $db  = $this->getDatabase();

        // Get all users who are not blocked and have opted in for system mails.
        $query = $db->getQuery(true);

        $query->select('DISTINCT ' . $db->quoteName('u.id') . ', ' . $db->quoteName('u.email'))
            ->from($db->quoteName('#__users', 'u'))
            ->join('LEFT', $db->quoteName('#__user_usergroup_map', 'g') . ' ON ' . $db->quoteName('g.user_id') . ' = ' . $db->quoteName('u.id'))
            ->where($db->quoteName('u.sendEmail') . ' = 1')
            ->where($db->quoteName('u.block') . ' = 0')
            ->whereIn($db->quoteName('g.group_id'), $groups);

        $db->setQuery($query);

        try {
            $users = $db->loadObjectList();
        } catch (\RuntimeException) {
            return;
        }

        if ($users === null) {
            Log::add($this->getApplication()->getLanguage()->_('PLG_SYSTEM_TASK_NOTIFICATION_USER_FETCH_FAIL'), Log::ERROR);

            return;
        }

        $mailSent = false;

        // Mail all matching users.
        foreach ($users as $user) {
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
            } catch (MailerException) {
                Log::add($this->getApplication()->getLanguage()->_('PLG_SYSTEM_TASK_NOTIFICATION_NOTIFY_SEND_EMAIL_FAIL'), Log::ERROR);
            }
        }

        if (!$mailSent) {
            Log::add($this->getApplication()->getLanguage()->_('PLG_SYSTEM_TASK_NOTIFICATION_NO_MAIL_SENT'), Log::WARNING);
        }
    }

    /**
     * @param   array  $data  The form data
     *
     * @return  void
     *
     * @since  5.3.0
     * @throws \Exception
     */
    private function saveLog(array $data): void
    {
        $model         = $this->getApplication()->bootComponent('com_scheduler')->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);
        $taskInfo      = $model->getItem($data['TASK_ID']);

        $obj           = new \stdClass();
        $obj->tasktype = SchedulerHelper::getTaskOptions()->findOption($taskInfo->type)->title ?? '';
        $obj->taskname = $data['TASK_TITLE'];
        $obj->duration = $data['TASK_DURATION'] ?? 0;
        $obj->jobid    = $data['TASK_ID'];
        $obj->exitcode = $data['EXIT_CODE'];
        $obj->taskid   = $data['TASK_TIMES'];
        $obj->lastdate = Factory::getDate()->toSql();
        $obj->nextdate = $taskInfo->next_execution;

        $model = $this->getApplication()->bootComponent('com_scheduler')
            ->getMVCFactory()->createModel('Log', 'Administrator', ['ignore_request' => true]);
        $model->save((array)$obj);
    }
}
