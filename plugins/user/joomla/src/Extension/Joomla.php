<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  User.joomla
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\User\Joomla\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla User plugin
 *
 * @since  1.5
 */
final class Joomla extends CMSPlugin
{
    use DatabaseAwareTrait;

    /**
     * Set as required the passwords fields when mail to user is set to No
     *
     * @param   \Joomla\CMS\Form\Form  $form  The form to be altered.
     * @param   mixed                  $data  The associated data for the form.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function onContentPrepareForm($form, $data)
    {
        // Check we are manipulating a valid user form before modifying it.
        $name = $form->getName();

        if ($name === 'com_users.user') {
            // In case there is a validation error (like duplicated user), $data is an empty array on save.
            // After returning from error, $data is an array but populated
            if (!$data) {
                $data = $this->getApplication()->getInput()->get('jform', [], 'array');
            }

            if (is_array($data)) {
                $data = (object) $data;
            }

            // Passwords fields are required when mail to user is set to No
            if (empty($data->id) && !$this->params->get('mail_to_user', 1)) {
                $form->setFieldAttribute('password', 'required', 'true');
                $form->setFieldAttribute('password2', 'required', 'true');
            }
        }

        return true;
    }

    /**
     * Remove all sessions for the user name
     *
     * Method is called after user data is deleted from the database
     *
     * @param   array    $user     Holds the user data
     * @param   boolean  $success  True if user was successfully stored in the database
     * @param   string   $msg      Message
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onUserAfterDelete($user, $success, $msg): void
    {
        if (!$success) {
            return;
        }

        $userId = (int) $user['id'];

        // Only execute this if the session metadata is tracked
        if ($this->getApplication()->get('session_metadata', true)) {
            UserHelper::destroyUserSessions($userId, true);
        }

        $db = $this->getDatabase();

        try {
            $db->setQuery(
                $db->getQuery(true)
                    ->delete($db->quoteName('#__messages'))
                    ->where($db->quoteName('user_id_from') . ' = :userId')
                    ->bind(':userId', $userId, ParameterType::INTEGER)
            )->execute();
        } catch (ExecutionFailureException $e) {
            // Do nothing.
        }

        // Delete Multi-factor Authentication user profile records
        $profileKey = 'mfa.%';
        $query      = $db->getQuery(true)
            ->delete($db->quoteName('#__user_profiles'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->where($db->quoteName('profile_key') . ' LIKE :profileKey')
            ->bind(':userId', $userId, ParameterType::INTEGER)
            ->bind(':profileKey', $profileKey, ParameterType::STRING);

        try {
            $db->setQuery($query)->execute();
        } catch (\Exception $e) {
            // Do nothing
        }

        // Delete Multi-factor Authentication records
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__user_mfa'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->bind(':userId', $userId, ParameterType::INTEGER);

        try {
            $db->setQuery($query)->execute();
        } catch (\Exception $e) {
            // Do nothing
        }
    }

    /**
     * Utility method to act on a user after it has been saved.
     *
     * This method sends a registration email to new users created in the backend.
     *
     * @param   array    $user     Holds the new user data.
     * @param   boolean  $isnew    True if a new user is stored.
     * @param   boolean  $success  True if user was successfully stored in the database.
     * @param   string   $msg      Message.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onUserAfterSave($user, $isnew, $success, $msg): void
    {
        $mail_to_user = $this->params->get('mail_to_user', 1);

        if (!$isnew || !$mail_to_user) {
            return;
        }

        $app           = $this->getApplication();
        $language      = $app->getLanguage();
        $defaultLocale = $language->getTag();

        // @todo: Suck in the frontend registration emails here as well. Job for a rainy day.
        // The method check here ensures that if running as a CLI Application we don't get any errors
        if (method_exists($app, 'isClient') && ($app->isClient('site') || $app->isClient('cli'))) {
            return;
        }

        // Check if we have a sensible from email address, if not bail out as mail would not be sent anyway
        if (strpos($app->get('mailfrom'), '@') === false) {
            $app->enqueueMessage($language->_('JERROR_SENDING_EMAIL'), 'warning');

            return;
        }

        /**
         * Look for user language. Priority:
         *  1. User frontend language
         *  2. User backend language
         */
        $userParams = new Registry($user['params']);
        $userLocale = $userParams->get('language', $userParams->get('admin_language', $defaultLocale));

        // Temporarily set application language to user's language.
        if ($userLocale !== $defaultLocale) {
            Factory::$language = Factory::getContainer()
                ->get(LanguageFactoryInterface::class)
                ->createLanguage($userLocale, $app->get('debug_lang', false));

            if (method_exists($app, 'loadLanguage')) {
                $app->loadLanguage(Factory::$language);
            }
        }

        // Load plugin language files.
        $this->loadLanguage();

        // Collect data for mail
        $data = [
            'name'     => $user['name'],
            'sitename' => $app->get('sitename'),
            'url'      => Uri::root(),
            'username' => $user['username'],
            'password' => $user['password_clear'],
            'email'    => $user['email'],
        ];

        $mailer = new MailTemplate('plg_user_joomla.mail', $userLocale);
        $mailer->addTemplateData($data);
        $mailer->addRecipient($user['email'], $user['name']);

        try {
            $res = $mailer->send();
        } catch (\Exception $exception) {
            try {
                Log::add($language->_($exception->getMessage()), Log::WARNING, 'jerror');

                $res = false;
            } catch (\RuntimeException $exception) {
                $app->enqueueMessage($language->_($exception->getMessage()), 'warning');

                $res = false;
            }
        }

        if ($res === false) {
            $app->enqueueMessage($language->_('JERROR_SENDING_EMAIL'), 'warning');
        }

        // Set application language back to default if we changed it
        if ($userLocale !== $defaultLocale) {
            Factory::$language = $language;

            if (method_exists($app, 'loadLanguage')) {
                $app->loadLanguage($language);
            }
        }
    }

    /**
     * This method should handle any login logic and report back to the subject
     *
     * @param   array  $user     Holds the user data
     * @param   array  $options  Array holding options (remember, autoregister, group)
     *
     * @return  boolean  True on success
     *
     * @since   1.5
     */
    public function onUserLogin($user, $options = [])
    {
        $instance = $this->getUser($user, $options);

        // If getUser returned an error, then pass it back.
        if ($instance instanceof \Exception) {
            return false;
        }

        // If the user is blocked, redirect with an error
        if ($instance->block == 1) {
            $this->getApplication()->enqueueMessage($this->getApplication()->getLanguage()->_('JERROR_NOLOGIN_BLOCKED'), 'warning');

            return false;
        }

        // Authorise the user based on the group information
        if (!isset($options['group'])) {
            $options['group'] = 'USERS';
        }

        // Check the user can login.
        $result = $instance->authorise($options['action']);

        if (!$result) {
            $this->getApplication()->enqueueMessage($this->getApplication()->getLanguage()->_('JERROR_LOGIN_DENIED'), 'warning');

            return false;
        }

        // Mark the user as logged in
        $instance->guest = 0;

        // Load the logged in user to the application
        $this->getApplication()->loadIdentity($instance);

        $session = $this->getApplication()->getSession();

        // Grab the current session ID
        $oldSessionId = $session->getId();

        // Fork the session
        $session->fork();

        // Register the needed session variables
        $session->set('user', $instance);

        // Update the user related fields for the Joomla sessions table if tracking session metadata.
        if ($this->getApplication()->get('session_metadata', true)) {
            $this->getApplication()->checkSession();
        }

        $db = $this->getDatabase();

        // Purge the old session
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__session'))
            ->where($db->quoteName('session_id') . ' = :sessionid')
            ->bind(':sessionid', $oldSessionId);

        try {
            $db->setQuery($query)->execute();
        } catch (\RuntimeException $e) {
            // The old session is already invalidated, don't let this block logging in
        }

        // Hit the user last visit field
        $instance->setLastVisit();

        // Add "user state" cookie used for reverse caching proxies like Varnish, Nginx etc.
        if ($this->getApplication()->isClient('site')) {
            $this->getApplication()->getInput()->cookie->set(
                'joomla_user_state',
                'logged_in',
                0,
                $this->getApplication()->get('cookie_path', '/'),
                $this->getApplication()->get('cookie_domain', ''),
                $this->getApplication()->isHttpsForced(),
                true
            );
        }

        return true;
    }

    /**
     * This method should handle any logout logic and report back to the subject
     *
     * @param   array  $user     Holds the user data.
     * @param   array  $options  Array holding options (client, ...).
     *
     * @return  boolean  True on success
     *
     * @since   1.5
     */
    public function onUserLogout($user, $options = [])
    {
        $my      = Factory::getUser();
        $session = Factory::getSession();

        $userid = (int) $user['id'];

        // Make sure we're a valid user first
        if ($user['id'] === 0 && !$my->get('tmp_user')) {
            return true;
        }

        $sharedSessions = $this->getApplication()->get('shared_session', '0');

        // Check to see if we're deleting the current session
        if ($my->id == $userid && ($sharedSessions || (!$sharedSessions && $options['clientid'] == $this->getApplication()->getClientId()))) {
            // Hit the user last visit field
            $my->setLastVisit();

            // Destroy the php session for this user
            $session->destroy();
        }

        // Enable / Disable Forcing logout all users with same userid, but only if session metadata is tracked
        $forceLogout = $this->params->get('forceLogout', 1) && $this->getApplication()->get('session_metadata', true);

        if ($forceLogout) {
            $clientId = $sharedSessions ? null : (int) $options['clientid'];
            UserHelper::destroyUserSessions($user['id'], false, $clientId);
        }

        // Delete "user state" cookie used for reverse caching proxies like Varnish, Nginx etc.
        if ($this->getApplication()->isClient('site')) {
            $this->getApplication()->getInput()->cookie->set('joomla_user_state', '', 1, $this->getApplication()->get('cookie_path', '/'), $this->getApplication()->get('cookie_domain', ''));
        }

        return true;
    }

    /**
     * Hooks on the Joomla! login event. Detects silent logins and disables the Multi-Factor
     * Authentication page in this case.
     *
     * Moreover, it will save the redirection URL and the Captive URL which is necessary in Joomla 4. You see, in Joomla
     * 4 having unified sessions turned on makes the backend login redirect you to the frontend of the site AFTER
     * logging in, something which would cause the Captive page to appear in the frontend and redirect you to the public
     * frontend homepage after successfully passing the Two Step verification process.
     *
     * @param   array  $options  Passed by Joomla. user: a User object; responseType: string, authentication response type.
     *
     * @return void
     * @since  4.2.0
     */
    public function onUserAfterLogin(array $options): void
    {
        if (!($this->getApplication()->isClient('administrator')) && !($this->getApplication()->isClient('site'))) {
            return;
        }

        $this->disableMfaOnSilentLogin($options);
    }

    /**
     * Detect silent logins and disable MFA if the relevant com_users option is set.
     *
     * @param   array  $options  The array of login options and login result
     *
     * @return  void
     * @since   4.2.0
     */
    private function disableMfaOnSilentLogin(array $options): void
    {
        $userParams         = ComponentHelper::getParams('com_users');
        $doMfaOnSilentLogin = $userParams->get('mfaonsilent', 0) == 1;

        // Should I show MFA even on silent logins? Default: 1 (yes, show)
        if ($doMfaOnSilentLogin) {
            return;
        }

        // Make sure I have a valid user
        /** @var User $user */
        $user = $options['user'];

        if (!is_object($user) || !($user instanceof User) || $user->guest) {
            return;
        }

        $silentResponseTypes = array_map(
            'trim',
            explode(',', $userParams->get('silentresponses', '') ?: '')
        );
        $silentResponseTypes = $silentResponseTypes ?: ['cookie', 'passwordless'];

        // Only proceed if this is not a silent login
        if (!in_array(strtolower($options['responseType'] ?? ''), $silentResponseTypes)) {
            return;
        }

        // Set the flag indicating that MFA is already checked.
        $this->getApplication()->getSession()->set('com_users.mfa_checked', 1);
    }

    /**
     * This method will return a user object
     *
     * If options['autoregister'] is true, if the user doesn't exist yet they will be created
     *
     * @param   array  $user     Holds the user data.
     * @param   array  $options  Array holding options (remember, autoregister, group).
     *
     * @return  User
     *
     * @since   1.5
     */
    private function getUser($user, $options = [])
    {
        $instance = User::getInstance();
        $id       = (int) UserHelper::getUserId($user['username']);

        if ($id) {
            $instance->load($id);

            return $instance;
        }

        // @todo : move this out of the plugin
        $params = ComponentHelper::getParams('com_users');

        // Read the default user group option from com_users
        $defaultUserGroup = $params->get('new_usertype', $params->get('guest_usergroup', 1));

        $instance->id             = 0;
        $instance->name           = $user['fullname'];
        $instance->username       = $user['username'];
        $instance->password_clear = $user['password_clear'];

        // Result should contain an email (check).
        $instance->email  = $user['email'];
        $instance->groups = [$defaultUserGroup];

        // If autoregister is set let's register the user
        $autoregister = $options['autoregister'] ?? $this->params->get('autoregister', 1);

        if ($autoregister) {
            if (!$instance->save()) {
                Log::add('Failed to automatically create account for user ' . $user['username'] . '.', Log::WARNING, 'error');
            }
        } else {
            // No existing user and autoregister off, this is a temporary user.
            $instance->set('tmp_user', true);
        }

        return $instance;
    }
}
