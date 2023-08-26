<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.updatenotification
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Access\Access;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Updater\Updater;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\Database\ParameterType;
use PHPMailer\PHPMailer\Exception as phpMailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

// Uncomment the following line to enable debug mode (update notification email sent every single time)
// define('PLG_SYSTEM_UPDATENOTIFICATION_DEBUG', 1);

/**
 * Joomla! Update Notification plugin
 *
 * Sends out an email to all Super Users or a predefined list of email addresses of Super Users when a new
 * Joomla! version is available.
 *
 * This plugin is a direct adaptation of the corresponding plugin in Akeeba Ltd's Admin Tools. The author has
 * consented to relicensing their plugin's code under GPLv2 or later (the original version was licensed under
 * GPLv3 or later) to allow its inclusion in the Joomla! CMS.
 *
 * @since  3.5
 */
class PlgSystemUpdatenotification extends CMSPlugin
{
    /**
     * Application object
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  4.0.0
     */
    protected $app;

    /**
     * Database driver
     *
     * @var    \Joomla\Database\DatabaseInterface
     * @since  4.0.0
     */
    protected $db;

    /**
     * Load plugin language files automatically
     *
     * @var    boolean
     * @since  3.6.3
     */
    protected $autoloadLanguage = true;

    /**
     * The update check and notification email code is triggered after the page has fully rendered.
     *
     * @return  void
     *
     * @since   3.5
     */
    public function onAfterRender()
    {
        // Get the timeout for Joomla! updates, as configured in com_installer's component parameters
        $component = ComponentHelper::getComponent('com_installer');

        /** @var \Joomla\Registry\Registry $params */
        $params        = $component->getParams();
        $cache_timeout = (int) $params->get('cachetimeout', 6);
        $cache_timeout = 3600 * $cache_timeout;

        // Do we need to run? Compare the last run timestamp stored in the plugin's options with the current
        // timestamp. If the difference is greater than the cache timeout we shall not execute again.
        $now  = time();
        $last = (int) $this->params->get('lastrun', 0);

        if (!defined('PLG_SYSTEM_UPDATENOTIFICATION_DEBUG') && (abs($now - $last) < $cache_timeout)) {
            return;
        }

        // Update last run status
        // If I have the time of the last run, I can update, otherwise insert
        $this->params->set('lastrun', $now);

        $db         = $this->db;
        $paramsJson = $this->params->toString('JSON');

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = :params')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('updatenotification'))
            ->bind(':params', $paramsJson);

        try {
            // Lock the tables to prevent multiple plugin executions causing a race condition
            $db->lockTable('#__extensions');
        } catch (Exception $e) {
            // If we can't lock the tables it's too risky to continue execution
            return;
        }

        try {
            // Update the plugin parameters
            $result = $db->setQuery($query)->execute();

            $this->clearCacheGroups(['com_plugins']);
        } catch (Exception $exc) {
            // If we failed to execute
            $db->unlockTables();
            $result = false;
        }

        try {
            // Unlock the tables after writing
            $db->unlockTables();
        } catch (Exception $e) {
            // If we can't lock the tables assume we have somehow failed
            $result = false;
        }

        // Stop on failure
        if (!$result) {
            return;
        }

        // This is the extension ID for Joomla! itself
        $eid = ExtensionHelper::getExtensionRecord('joomla', 'file')->extension_id;

        // Get any available updates
        $updater = Updater::getInstance();
        $results = $updater->findUpdates([$eid], $cache_timeout);

        // If there are no updates our job is done. We need BOTH this check AND the one below.
        if (!$results) {
            return;
        }

        // Get the update model and retrieve the Joomla! core updates
        $model = $this->app->bootComponent('com_installer')
            ->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);
        $model->setState('filter.extension_id', $eid);
        $updates = $model->getItems();

        // If there are no updates we don't have to notify anyone about anything. This is NOT a duplicate check.
        if (empty($updates)) {
            return;
        }

        // Get the available update
        $update = array_pop($updates);

        // Check the available version. If it's the same or less than the installed version we have no updates to notify about.
        if (version_compare($update->version, JVERSION, 'le')) {
            return;
        }

        // If we're here, we have updates. First, get a link to the Joomla! Update component.
        $baseURL  = Uri::base();
        $baseURL  = rtrim($baseURL, '/');
        $baseURL .= (substr($baseURL, -13) !== 'administrator') ? '/administrator/' : '/';
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
        $this->app->triggerEvent('onBuildAdministratorLoginURL', [&$uri]);

        // Let's find out the email addresses to notify
        $superUsers    = [];
        $specificEmail = $this->params->get('email', '');

        if (!empty($specificEmail)) {
            $superUsers = $this->getSuperUsers($specificEmail);
        }

        if (empty($superUsers)) {
            $superUsers = $this->getSuperUsers();
        }

        if (empty($superUsers)) {
            return;
        }

        /*
         * Load the appropriate language. We try to load English (UK), the current user's language and the forced
         * language preference, in this order. This ensures that we'll never end up with untranslated strings in the
         * update email which would make Joomla! seem bad. So, please, if you don't fully understand what the
         * following code does DO NOT TOUCH IT. It makes the difference between a hobbyist CMS and a professional
         * solution!
         */
        $jLanguage = $this->app->getLanguage();
        $jLanguage->load('plg_system_updatenotification', JPATH_ADMINISTRATOR, 'en-GB', true, true);
        $jLanguage->load('plg_system_updatenotification', JPATH_ADMINISTRATOR, null, true, false);

        // Then try loading the preferred (forced) language
        $forcedLanguage = $this->params->get('language_override', '');

        if (!empty($forcedLanguage)) {
            $jLanguage->load('plg_system_updatenotification', JPATH_ADMINISTRATOR, $forcedLanguage, true, false);
        }

        // Replace merge codes with their values
        $newVersion = $update->version;

        $jVersion       = new Version();
        $currentVersion = $jVersion->getShortVersion();

        $sitename = $this->app->get('sitename');

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
                $mailer = new MailTemplate('plg_system_updatenotification.mail', $jLanguage->getTag());
                $mailer->addRecipient($superUser->email);
                $mailer->addTemplateData($substitutions);
                $mailer->send();
            } catch (MailDisabledException | phpMailerException $exception) {
                try {
                    Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');
                } catch (\RuntimeException $exception) {
                    $this->app->enqueueMessage(Text::_($exception->errorMessage()), 'warning');
                }
            }
        }
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
        $db     = $this->db;
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
        } catch (Exception $exc) {
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
        } catch (Exception $exc) {
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
        } catch (Exception $exc) {
            return $ret;
        }

        return $ret;
    }

    /**
     * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
     *
     * @param   array  $clearGroups  The cache groups to clean
     *
     * @return  void
     *
     * @since   3.5
     */
    private function clearCacheGroups(array $clearGroups)
    {
        foreach ($clearGroups as $group) {
            try {
                $options = [
                    'defaultgroup' => $group,
                    'cachebase'    => $this->app->get('cache_path', JPATH_CACHE),
                ];

                $cache = Cache::getInstance('callback', $options);
                $cache->clean();
            } catch (Exception $e) {
                // Ignore it
            }
        }
    }
}
