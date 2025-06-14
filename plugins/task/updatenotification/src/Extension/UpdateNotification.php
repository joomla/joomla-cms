<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.updatenotification
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\UpdateNotification\Extension;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Updater\Updater;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use PHPMailer\PHPMailer\Exception as phpMailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A task plugin. Offers 2 task routines Invalidate Expired Consents and Remind Expired Consents
 * {@see ExecuteTaskEvent}.
 *
 * @since 5.0.0
 */
final class UpdateNotification extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use TaskPluginTrait;

    /**
     * @var string[]
     * @since 5.0.0
     */
    private const TASKS_MAP = [
        'update.notification' => [
            'langConstPrefix' => 'PLG_TASK_UPDATENOTIFICATION_SEND',
            'method'          => 'sendNotification',
            'form'            => 'sendForm',
        ],
    ];

    /**
     * @var boolean
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
     * Method to send the update notification.
     *
     * @param   ExecuteTaskEvent  $event  The `onExecuteTask` event.
     *
     * @return integer  The routine exit code.
     *
     * @since  5.0.0
     * @throws \Exception
     */
    private function sendNotification(ExecuteTaskEvent $event): int
    {
        // Load the parameters.
        $specificEmail  = $event->getArgument('params')->email ?? '';
        $forcedLanguage = $event->getArgument('params')->language_override ?? '';

        // This is the extension ID for Joomla! itself
        $eid = ExtensionHelper::getExtensionRecord('joomla', 'file')->extension_id;

        // Get any available updates
        $updater = Updater::getInstance();
        $results = $updater->findUpdates([$eid], 0);

        // If there are no updates our job is done. We need BOTH this check AND the one below.
        if (!$results) {
            return Status::OK;
        }

        // Get the update model and retrieve the Joomla! core updates
        $model = $this->getApplication()->bootComponent('com_installer')
            ->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);

        $model->setState('filter.extension_id', $eid);
        $updates = $model->getItems();

        // If there are no updates we don't have to notify anyone about anything. This is NOT a duplicate check.
        if (empty($updates)) {
            return Status::OK;
        }

        // Get the available update
        $update = array_pop($updates);

        // Check the available version. If it's the same or less than the installed version we have no updates to notify about.
        if (version_compare($update->version, JVERSION, 'le')) {
            return Status::OK;
        }

        // If we're here, we have updates. First, get a link to the Joomla! Update component.
        $baseURL  = Uri::base();
        $baseURL  = rtrim($baseURL, '/');
        $baseURL .= (!str_ends_with($baseURL, 'administrator')) ? '/administrator/' : '/';
        $baseURL .= 'index.php?option=com_joomlaupdate';
        $uri      = new Uri($baseURL);

        /**
         * Some third party security solutions require a secret query parameter to allow log in to the administrator
         * backend of the site. The link generated above will be invalid and could probably block the user out of their
         * site, confusing them (they can't understand the third party security solution is not part of Joomla! proper).
         * So, we're calling the onBuildAdministratorLoginURL system plugin event to let these third party solutions
         * add any necessary secret query parameters to the URL. The plugins are supposed to have a method with the
         * signature:
         *
         * public function onBuildAdministratorLoginURL(Uri &$uri);
         *
         * The plugins should modify the $uri object directly and return null.
         */
        $this->getApplication()->triggerEvent('onBuildAdministratorLoginURL', [&$uri]);

        // Let's find out the email addresses to notify
        $superUsers = [];

        if (!empty($specificEmail)) {
            $superUsers = $this->getSuperUsers($specificEmail);
        }

        if (empty($superUsers)) {
            $superUsers = $this->getSuperUsers();
        }

        if (empty($superUsers)) {
            return Status::KNOCKOUT;
        }

        /*
         * Load the appropriate language. We try to load English (UK), the current user's language and the forced
         * language preference, in this order. This ensures that we'll never end up with untranslated strings in the
         * update email which would make Joomla! seem bad. So, please, if you don't fully understand what the
         * following code does DO NOT TOUCH IT. It makes the difference between a hobbyist CMS and a professional
         * solution!
         */
        $jLanguage = $this->getApplication()->getLanguage();
        $jLanguage->load('plg_task_updatenotification', JPATH_ADMINISTRATOR, 'en-GB', true, true);
        $jLanguage->load('plg_task_updatenotification', JPATH_ADMINISTRATOR, null, true, false);

        // Then try loading the preferred (forced) language
        if (!empty($forcedLanguage)) {
            $jLanguage->load('plg_task_updatenotification', JPATH_ADMINISTRATOR, $forcedLanguage, true, false);
        }

        // Replace merge codes with their values
        $newVersion = $update->version;

        $jVersion       = new Version();
        $currentVersion = $jVersion->getShortVersion();

        $sitename = $this->getApplication()->get('sitename');

        $substitutions = [
            'newversion'  => $newVersion,
            'curversion'  => $currentVersion,
            'sitename'    => $sitename,
            'url'         => Uri::base(),
            'link'        => $uri->toString(),
            'releasenews' => 'https://www.joomla.org/announcements/release-news/',
        ];

        // Send the emails to the Super Users
        foreach ($superUsers as $superUser) {
            try {
                $mailer = new MailTemplate('plg_task_updatenotification.mail', $jLanguage->getTag());
                $mailer->addRecipient($superUser->email);
                $mailer->addTemplateData($substitutions);
                $mailer->send();
            } catch (MailDisabledException | phpMailerException $exception) {
                try {
                    $this->logTask($jLanguage->_($exception->getMessage()));
                } catch (\RuntimeException) {
                    return Status::KNOCKOUT;
                }
            }
        }

        $this->logTask('UpdateNotification end');

        return Status::OK;
    }

    /**
     * Returns the Super Users email information. If you provide a comma separated $email list
     * we will check that these emails do belong to Super Users and that they have not blocked
     * system emails.
     *
     * @param   null|string  $email  A list of Super Users to email
     *
     * @return  array  The list of Super User emails
     *
     * @since   3.5
     */
    private function getSuperUsers($email = null)
    {
        $db     = $this->getDatabase();
        $emails = [];

        // Convert the email list to an array
        if (!empty($email)) {
            $temp   = explode(',', $email);

            foreach ($temp as $entry) {
                $emails[] = trim($entry);
            }

            $emails = array_unique($emails);
        }

        // Get a list of groups which have Super User privileges
        $ret = [];

        try {
            $rootId    = Table::getInstance('Asset')->getRootId();
            $rules     = Access::getAssetRules($rootId)->getData();
            $rawGroups = $rules['core.admin']->getData();
            $groups    = [];

            if (empty($rawGroups)) {
                return $ret;
            }

            foreach ($rawGroups as $g => $enabled) {
                if ($enabled) {
                    $groups[] = $g;
                }
            }

            if (empty($groups)) {
                return $ret;
            }
        } catch (\Exception $exc) {
            return $ret;
        }

        // Get the user IDs of users belonging to the SA groups
        try {
            $query = $db->getQuery(true)
                ->select($db->quoteName('user_id'))
                ->from($db->quoteName('#__user_usergroup_map'))
                ->whereIn($db->quoteName('group_id'), $groups);

            $db->setQuery($query);
            $userIDs = $db->loadColumn(0);

            if (empty($userIDs)) {
                return $ret;
            }
        } catch (\Exception $exc) {
            return $ret;
        }

        // Get the user information for the Super Administrator users
        try {
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'username', 'email']))
                ->from($db->quoteName('#__users'))
                ->whereIn($db->quoteName('id'), $userIDs)
                ->where($db->quoteName('block') . ' = 0')
                ->where($db->quoteName('sendEmail') . ' = 1');

            if (!empty($emails)) {
                $lowerCaseEmails = array_map('strtolower', $emails);
                $query->whereIn('LOWER(' . $db->quoteName('email') . ')', $lowerCaseEmails, ParameterType::STRING);
            }

            $db->setQuery($query);
            $ret = $db->loadObjectList();
        } catch (\Exception) {
            return $ret;
        }

        return $ret;
    }
}
