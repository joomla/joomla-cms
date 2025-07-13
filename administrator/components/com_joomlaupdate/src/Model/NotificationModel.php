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
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function sendNotification($type, $oldVersion): void
    {
        $params = ComponentHelper::getParams('com_joomlaupdate');

        // User groups to notify. Default is superuser group.
        $emailGroups = $params->get('automated_updates_email_groups', 8, 'array');

        // If the emailGroups is not an array, convert it to an array
        if (!is_array($emailGroups)) {
            $emailGroups = ArrayHelper::toInteger(explode(',', $emailGroups));
        }
	
		// Get all users in these groups who can receive e-mails
        $emailReceivers = $this->getEmailReceivers($emailGroups);

        // If no email receivers are found, we do not send any notification
        if (empty($emailReceivers)) {
            return;
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
     * Returns the email information of receivers. Receiver can be any users who is not blocked.
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
		
        $groupModel = Factory::getApplication()->bootComponent('com_users')
            ->getMVCFactory()->createModel('Group', 'Administrator');

		// Get the emails of all groups in the emailGroups
        foreach ($emailGroups as $group) {
            $usersInGroup = $groupModel->getUsersInGroup($group);

            if (empty($usersInGroup)) {
                return [];
            }

			// Only users with valid email address who are not blocked can receive the email
			foreach ($usersInGroup as $user) {
                if (MailHelper::isEmailAddress($user->email) && !$user->block) {
                    $user->email = strtolower(trim($user->email));

                    // Check if the email already exists in the emailReceivers array
                    $exist = false;
                    for ($i = 0; $i < count($emailReceivers); $i++) {
                        if ($emailReceivers[$i]->email === $user->email) {
                            $exist = true;
                            break;
                        }
                    }
                    // Add to the list if it is not already in the list
                    if (!$exist) {
                        $emailReceivers[] = $user;
                    }
                }
            }
        }

            return $emailReceivers;
    }
}
