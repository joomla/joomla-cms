<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\Model;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Notification Model
 *
 * @internal
 * @since  __DEPLOY_VERSION__
 */
final class NotificationModel extends BaseDatabaseModel
{
    /**
     * Sends the update notification to the specifically configured emails and superusers
     *
     * @param  string  $type        The type of notification to send. This is the last key for the mail template
     * @param  string  $oldVersion  The old version from before the update
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function sendNotification($type, $oldVersion): void
    {
        $params = ComponentHelper::getParams('com_joomlaupdate');

        // Load the parameters.
        $specificEmail = $params->get('automated_updates_email');

        // Let's find out the email addresses to notify
        $superUsers = [];

        if (!empty($specificEmail)) {
            $superUsers = $this->getSuperUsers($specificEmail);
        }

        if (empty($superUsers)) {
            $superUsers = $this->getSuperUsers();
        }

        if (empty($superUsers)) {
            throw new \RuntimeException();
        }

        $app        = Factory::getApplication();
        $jLanguage  = $app->getLanguage();
        $sitename   = $app->get('sitename');
        $newVersion = (new Version())->getShortVersion();

        $substitutions = [
            'oldversion' => $oldVersion,
            'newversion' => $newVersion,
            'sitename'   => $sitename,
            'url'        => Uri::root(),
        ];

        // Send the emails to the Super Users
        foreach ($superUsers as $superUser) {
            $params = new Registry($superUser->params);
            $jLanguage->load('com_joomlaupdate', JPATH_ADMINISTRATOR, 'en-GB', true, true);
            $jLanguage->load('com_joomlaupdate', JPATH_ADMINISTRATOR, $params->get('admin_language', null), true, true);

            $mailer = new MailTemplate('com_joomlaupdate.update.' . $type, $jLanguage->getTag());
            $mailer->addRecipient($superUser->email);
            $mailer->addTemplateData($substitutions);
            $mailer->send();
        }
    }

    /**
     * Returns the Super Users email information. If you provide a comma separated $email list
     * we will check that these emails do belong to Super Users and that they have not blocked
     * system emails.
     *
     * @param null|string $email A list of Super Users to email
     *
     * @return  array  The list of Super User emails
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getSuperUsers($email = null): array
    {
        $db     = $this->getDatabase();
        $emails = [];

        // Convert the email list to an array
        if (!empty($email)) {
            $temp = explode(',', $email);

            foreach ($temp as $entry) {
                if (!MailHelper::isEmailAddress(trim($entry))) {
                    continue;
                }

                $emails[] = trim($entry);
            }

            $emails = array_unique($emails);
        }

        // Get a list of groups which have Super User privileges
        $ret = [];

        try {
            $rootId    = (new Asset($db))->getRootId();
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
                ->select($db->quoteName(['id', 'username', 'email', 'params']))
                ->from($db->quoteName('#__users'))
                ->whereIn($db->quoteName('id'), $userIDs)
                ->where($db->quoteName('block') . ' = 0')
                ->where($db->quoteName('sendEmail') . ' = 1');

            if (!empty($emails)) {
                $lowerCaseEmails = array_map('strtolower', $emails);
                $query->whereIn('LOWER(' . $db->quoteName('email') . ')', $lowerCaseEmails, ParameterType::STRING);
            }

            $ret = $db->setQuery($query)->loadObjectList();
        } catch (\Exception) {
            return $ret;
        }

        return $ret;
    }
}
