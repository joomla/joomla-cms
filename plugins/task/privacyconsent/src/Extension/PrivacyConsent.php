<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.PrivacyConsent
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\PrivacyConsent\Extension;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\CMS\User\UserHelper;
use Joomla\Component\Messages\Administrator\Model\MessageModel;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use PHPMailer\PHPMailer\Exception as phpmailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A task plugin. Offers 2 task routines Invalidate Expired Consents and Remind Expired Consents
 * {@see ExecuteTaskEvent}.
 *
 * @since 5.0.0
 */
final class PrivacyConsent extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use TaskPluginTrait;
    use UserFactoryAwareTrait;

    /**
     * @var string[]
     * @since 5.0.0
     */
    private const TASKS_MAP = [
        'privacy.consent' => [
            'langConstPrefix' => 'PLG_TASK_PRIVACYCONSENT_INVALIDATE',
            'method'          => 'privacyConsents',
            'form'            => 'privacyconsentForm',
        ],
    ];

    /**
     * @var boolean
     *
     * @since 5.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since 5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList'    => 'advertiseRoutines',
            'onExecuteTask'        => 'standardRoutineHandler',
            'onContentPrepareForm' => 'enhanceTaskItemForm',
        ];
    }

    /**
     * Method to send the remind for privacy consents renew.
     *
     * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
     *
     * @return integer  The routine exit code.
     *
     * @since  5.0.0
     * @throws \Exception
     */
    private function privacyConsents(ExecuteTaskEvent $event): int
    {
        // Load the parameters.
        $expire = (int) $event->getArgument('params')->consentexpiration ?? 365;
        $remind = (int) $event->getArgument('params')->remind ?? 30;

        if (
            $this->invalidateExpiredConsents($expire) === Status::OK
            && $this->remindExpiringConsents($expire, $remind) === Status::OK
        ) {
            return Status::OK;
        }

        return Status::KNOCKOUT;
    }

    /**
     * Method to send the remind for privacy consents renew.
     *
     * @param   integer    $expire
     * @param   integer    $remind
     *
     * @return integer  The routine exit code.
     *
     * @since  5.0.0
     * @throws \Exception
     */
    private function remindExpiringConsents($expire, $remind): int
    {
        $now      = Factory::getDate()->toSql();
        $period   = '-' . ($expire - $remind);
        $db       = $this->getDatabase();
        $query    = $db->getQuery(true);

        $query->select($db->quoteName(['r.id', 'r.user_id', 'u.email']))
            ->from($db->quoteName('#__privacy_consents', 'r'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('r.user_id'))
            ->where($db->quoteName('subject') . ' = ' . $db->quote('PLG_SYSTEM_PRIVACYCONSENT_SUBJECT'))
            ->where($db->quoteName('remind') . ' = 0')
            ->where($query->dateAdd($db->quote($now), $period, 'DAY') . ' > ' . $db->quoteName('created'));

        try {
            $users = $db->setQuery($query)->loadObjectList();
        } catch (\RuntimeException) {
            return Status::KNOCKOUT;
        }

        // Do not process further if no expired consents found
        if (empty($users)) {
            return Status::OK;
        }

        $app      = $this->getApplication();
        $linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

        foreach ($users as $user) {
            $token       = ApplicationHelper::getHash(UserHelper::genRandomPassword());
            $hashedToken = UserHelper::hashPassword($token);

            // The mail
            try {
                $templateData = [
                    'sitename' => $app->get('sitename'),
                    'url'      => Uri::root(),
                    'tokenurl' => Route::link('site', 'index.php?option=com_privacy&view=remind&remind_token=' . $token, false, $linkMode, true),
                    'formurl'  => Route::link('site', 'index.php?option=com_privacy&view=remind', false, $linkMode, true),
                    'token'    => $token,
                ];

                $mailer = new MailTemplate('plg_task_privacyconsent.request.reminder', $app->getLanguage()->getTag());
                $mailer->addTemplateData($templateData);
                $mailer->addRecipient($user->email);

                $mailResult = $mailer->send();

                if ($mailResult === false) {
                    return Status::KNOCKOUT;
                }

                $userId = (int) $user->id;

                // Update the privacy_consents item to not send the reminder again
                $query->clear()
                    ->update($db->quoteName('#__privacy_consents'))
                    ->set($db->quoteName('remind') . ' = 1')
                    ->set($db->quoteName('token') . ' = :token')
                    ->where($db->quoteName('id') . ' = :userid')
                    ->bind(':token', $hashedToken)
                    ->bind(':userid', $userId, ParameterType::INTEGER);
                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (\RuntimeException) {
                    return Status::KNOCKOUT;
                }
            } catch (MailDisabledException | phpmailerException) {
                return Status::KNOCKOUT;
            }
        }
        $this->logTask('Remind end');

        return Status::OK;
    }

    /**
     * Method to delete the expired privacy consents.
     *
     * @param   integer    $expire
     *
     * @return integer  The routine exit code.
     *
     * @since  5.0.0
     * @throws \Exception
     */
    private function invalidateExpiredConsents($expire): int
    {
        $now    = Factory::getDate()->toSql();
        $period = '-' . $expire;
        $db     = $this->getDatabase();
        $query  = $db->getQuery(true);

        $query->select($db->quoteName(['id', 'user_id']))
            ->from($db->quoteName('#__privacy_consents'))
            ->where($query->dateAdd($db->quote($now), $period, 'DAY') . ' > ' . $db->quoteName('created'))
            ->where($db->quoteName('subject') . ' = ' . $db->quote('PLG_SYSTEM_PRIVACYCONSENT_SUBJECT'))
            ->where($db->quoteName('state') . ' = 1');

        $db->setQuery($query);

        try {
            $users = $db->loadObjectList();
        } catch (\RuntimeException) {
            return Status::KNOCKOUT;
        }

        // Do not process further if no expired consents found
        if (empty($users)) {
            return Status::OK;
        }

        // Push a notification to the site's super users
        /** @var MessageModel $messageModel */
        $messageModel = $this->getApplication()->bootComponent('com_messages')->getMVCFactory()->createModel('Message', 'Administrator');

        foreach ($users as $user) {
            $userId = (int) $user->id;
            $query  = $db->getQuery(true)
                ->update($db->quoteName('#__privacy_consents'))
                ->set($db->quoteName('state') . ' = 0')
                ->where($db->quoteName('id') . ' = :userid')
                ->bind(':userid', $userId, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException) {
                return Status::KNOCKOUT;
            }

            $messageModel->notifySuperUsers(
                Text::_('PLG_TASK_PRIVACYCONSENT_NOTIFICATION_USER_PRIVACY_EXPIRED_SUBJECT'),
                Text::sprintf('PLG_TASK_PRIVACYCONSENT_NOTIFICATION_USER_PRIVACY_EXPIRED_MESSAGE', $this->getUserFactory()->loadUserById($user->user_id)->username)
            );
        }

        return Status::OK;
    }
}
