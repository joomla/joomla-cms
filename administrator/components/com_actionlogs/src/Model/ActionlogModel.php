<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\UserFactoryAwareInterface;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Utilities\IpHelper;
use PHPMailer\PHPMailer\Exception as phpMailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of Actionlog records.
 *
 * @since  3.9.0
 */
class ActionlogModel extends BaseDatabaseModel implements UserFactoryAwareInterface
{
    use UserFactoryAwareTrait;

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
    public function addLog($messages, $messageLanguageKey, $context, $userId = 0)
    {
        if (!is_numeric($userId)) {
            @trigger_error(sprintf('User ID must be an integer in %s.', __METHOD__), E_USER_DEPRECATED);
        }

        $user   = $userId ? $this->getUserFactory()->loadUserById($userId) : $this->getCurrentUser();
        $db     = $this->getDatabase();
        $date   = Factory::getDate();
        $params = ComponentHelper::getComponent('com_actionlogs')->getParams();

        if ($params->get('ip_logging', 0)) {
            $ip = IpHelper::getIp();

            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $ip = 'COM_ACTIONLOGS_IP_INVALID';
            }
        } else {
            $ip = 'COM_ACTIONLOGS_DISABLED';
        }

        $loggedMessages = [];

        foreach ($messages as $message) {
            $logMessage                       = new \stdClass();
            $logMessage->message_language_key = $messageLanguageKey;
            $logMessage->message              = json_encode($message);
            $logMessage->log_date             = (string) $date;
            $logMessage->extension            = $context;
            $logMessage->user_id              = $user->id;
            $logMessage->ip_address           = $ip;
            $logMessage->item_id              = isset($message['id']) ? (int) $message['id'] : 0;

            try {
                $db->insertObject('#__action_logs', $logMessage);
                $loggedMessages[] = $logMessage;
            } catch (\RuntimeException $e) {
                // Ignore it
            }
        }

        try {
            // Send notification email to users who choose to be notified about the action logs
            $this->sendNotificationEmails($loggedMessages, $user->name, $context);
        } catch (MailDisabledException | phpMailerException $e) {
            // Ignore it
        }
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
     *
     * @throws  MailDisabledException  if mail is disabled
     * @throws  phpmailerException     if sending mail failed
     */
    protected function sendNotificationEmails($messages, $username, $context)
    {
        $app   = Factory::getApplication();
        $lang  = $app->getLanguage();
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName(['u.email', 'l.extensions']))
            ->from($db->quoteName('#__users', 'u'))
            ->where($db->quoteName('u.block') . ' = 0')
            ->join(
                'INNER',
                $db->quoteName('#__action_logs_users', 'l') . ' ON ( ' . $db->quoteName('l.notify') . ' = 1 AND '
                . $db->quoteName('l.user_id') . ' = ' . $db->quoteName('u.id') . ')'
            );

        $db->setQuery($query);

        $users = $db->loadObjectList();

        $recipients = [];

        foreach ($users as $user) {
            $extensions = json_decode($user->extensions, true);

            if ($extensions && \in_array(strtok($context, '.'), $extensions)) {
                $recipients[] = $user->email;
            }
        }

        if (empty($recipients)) {
            return;
        }

        $extension = strtok($context, '.');
        $lang->load('com_actionlogs', JPATH_ADMINISTRATOR);
        ActionlogsHelper::loadTranslationFiles($extension);
        $temp      = [];

        foreach ($messages as $message) {
            $m              = [];
            $m['extension'] = Text::_($extension);
            $m['message']   = ActionlogsHelper::getHumanReadableLogMessage($message);
            $m['date']      = HTMLHelper::_('date', $message->log_date, 'Y-m-d H:i:s T', 'UTC');
            $m['username']  = $username;
            $temp[]         = $m;
        }

        $templateData = [
            'messages' => $temp,
        ];

        $mailer = new MailTemplate('com_actionlogs.notification', $app->getLanguage()->getTag());
        $mailer->addTemplateData($templateData);

        foreach ($recipients as $recipient) {
            $mailer->addRecipient($recipient);
        }

        $mailer->send();
    }
}
