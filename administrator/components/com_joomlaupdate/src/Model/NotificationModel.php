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
use Joomla\CMS\Helper\UserGroupsHelper;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Notification Model
 *
 * @internal
 * @since  5.4.0
 */
final class NotificationModel extends BaseDatabaseModel
{
    /**
     * Sends the update notification to the specifically configured emails and superusers
     *
     * @param  string  $type        The type of notification to send. This is the last key for the mail template
     * @param  string  $oldVersion  The old version from before the update
     * @param  string  $newVersion  The new version from after the update
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function sendNotification($type, $oldVersion, $newVersion): void
    {
        $params = ComponentHelper::getParams('com_joomlaupdate');

        // Superusergroups as fallback
        $superUserGroups = $this->getSuperUserGroups();

        if (!\is_array($superUserGroups)) {
            $emailGroups = ArrayHelper::toInteger(explode(',', $superUserGroups));
        }

        // User groups from input field
        $emailGroups = $params->get('automated_updates_email_groups', $superUserGroups, 'array');

        if (!\is_array($emailGroups)) {
            $emailGroups = ArrayHelper::toInteger(explode(',', $emailGroups));
        }

        // Get all users in these groups who can receive emails
        $emailReceivers = $this->getEmailReceivers($emailGroups);

        // If no email receivers are found, we use superusergroups as fallback
        if (empty($emailReceivers)) {
            $emailReceivers  = $this->getEmailReceivers($superUserGroups);
        }

        $app      = Factory::getApplication();
        $sitename = $app->get('sitename');

        $substitutions = [
            'oldversion' => $oldVersion,
            'newversion' => $newVersion,
            'sitename'   => $sitename,
            'url'        => Uri::root(),
        ];

        // Determine the default admin language and load the language file for the fallback and default language
        $defaultLocale   = ComponentHelper::getParams('com_languages')->get('administrator', 'en-GB');
        $defaultLanguage = $app->getLanguage();
        $defaultLanguage->load('com_joomlaupdate', JPATH_ADMINISTRATOR, 'en-GB', true, true);
        $defaultLanguage->load('com_joomlaupdate', JPATH_ADMINISTRATOR);

        // Send emails to all receivers
        foreach ($emailReceivers as $receiver) {
            $receiverParams = new Registry($receiver->params);
            $receiverLocale = $receiverParams->get('admin_language', $defaultLocale);

            // Temporarily set application language to user's language.
            if ($app->getLanguage()->getTag() !== $receiverLocale) {
                $receiverLanguage = Factory::getContainer()
                    ->get(LanguageFactoryInterface::class)
                    ->createLanguage($receiverLocale, $app->get('debug_lang', false));

                Factory::$language = $receiverLanguage;

                if (method_exists($app, 'loadLanguage')) {
                    $app->loadLanguage($receiverLanguage);
                }

                $receiverLanguage->load('com_joomlaupdate', JPATH_ADMINISTRATOR, $receiverLocale);
            }

            $mailer = new MailTemplate('com_joomlaupdate.update.' . $type, $receiverLocale);
            $mailer->addRecipient($receiver->email, $receiver->name);
            $mailer->addTemplateData($substitutions);
            $mailer->send();
        }

        // Set application language back to default
        Factory::$language = $defaultLanguage;

        if (method_exists($app, 'loadLanguage')) {
            $app->loadLanguage($defaultLanguage);
        }
    }

    /**
     * Returns the email information of receivers. Receiver can be any user who is not disabled.
     *
     * @param   array $emailGroups A list of usergroups to email
     *
     * @return  array  The list of email receivers. Can be empty if no users are found.
     *
     * @since   5.4.0
     */
    private function getEmailReceivers($emailGroups): array
    {
        if (empty($emailGroups)) {
            return [];
        }

        $emailReceivers = [];

        // Get the users of all groups in the emailGroups
        $usersModel = Factory::getApplication()->bootComponent('com_users')
            ->getMVCFactory()->createModel('Users', 'Administrator');
        $usersModel->setState('filter.state', (int) 0); // Only enabled users

        foreach ($emailGroups as $group) {
            $usersModel->setState('filter.group_id', $group);

            $usersInGroup = $usersModel->getItems();
            if (empty($usersInGroup)) {
                continue;
            }

            // Users can be in more than one group. Accept only one entry
            foreach ($usersInGroup as $user) {
                if (MailHelper::isEmailAddress($user->email) && $user->sendEmail === 1) {
                    $emailReceivers[$user->id] ??= $user;
                }
            }
        }

        return $emailReceivers;
    }

    /**
     * Returns all Super Users
     *
     * @return  array  The list of super user groups.
     *
     * @since   5.4.0
     */
    private function getSuperUserGroups(): array
    {
        $groups = UserGroupsHelper::getInstance()->getAll();
        $ret    = [];

        // Find groups with core.admin rights (super users)
        foreach ($groups as $group) {
            if (Access::checkGroup($group->id, 'core.admin')) {
                $ret[] = $group->id;
            }
        }

        return $ret;
    }
}
