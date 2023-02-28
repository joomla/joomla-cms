<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.cookie
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla Authentication plugin
 *
 * @since  3.2
 * @note   Code based on http://jaspan.com/improved_persistent_login_cookie_best_practice
 *         and http://fishbowl.pastiche.org/2004/01/19/persistent_login_cookie_best_practice/
 */
class PlgAuthenticationCookie extends CMSPlugin
{
    /**
     * Application object
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  3.2
     */
    protected $app;

    /**
     * Database object
     *
     * @var    \Joomla\Database\DatabaseDriver
     * @since  3.2
     */
    protected $db;

    /**
     * Reports the privacy related capabilities for this plugin to site administrators.
     *
     * @return  array
     *
     * @since   3.9.0
     */
    public function onPrivacyCollectAdminCapabilities()
    {
        $this->loadLanguage();

        return [
            Text::_('PLG_AUTHENTICATION_COOKIE') => [
                Text::_('PLG_AUTHENTICATION_COOKIE_PRIVACY_CAPABILITY_COOKIE'),
            ],
        ];
    }

    /**
     * This method should handle any authentication and report back to the subject
     *
     * @param   array   $credentials  Array holding the user credentials
     * @param   array   $options      Array of extra options
     * @param   object  &$response    Authentication response object
     *
     * @return  boolean
     *
     * @since   3.2
     */
    public function onUserAuthenticate($credentials, $options, &$response)
    {
        // No remember me for admin
        if ($this->app->isClient('administrator')) {
            return false;
        }

        // Get cookie
        $cookieName  = 'joomla_remember_me_' . UserHelper::getShortHashedUserAgent();
        $cookieValue = $this->app->input->cookie->get($cookieName);

        // Try with old cookieName (pre 3.6.0) if not found
        if (!$cookieValue) {
            $cookieName  = UserHelper::getShortHashedUserAgent();
            $cookieValue = $this->app->input->cookie->get($cookieName);
        }

        if (!$cookieValue) {
            return false;
        }

        $cookieArray = explode('.', $cookieValue);

        // Check for valid cookie value
        if (count($cookieArray) !== 2) {
            // Destroy the cookie in the browser.
            $this->app->input->cookie->set($cookieName, '', 1, $this->app->get('cookie_path', '/'), $this->app->get('cookie_domain', ''));
            Log::add('Invalid cookie detected.', Log::WARNING, 'error');

            return false;
        }

        $response->type = 'Cookie';

        // Filter series since we're going to use it in the query
        $filter = new InputFilter();
        $series = $filter->clean($cookieArray[1], 'ALNUM');
        $now    = time();

        // Remove expired tokens
        $query = $this->db->getQuery(true)
            ->delete($this->db->quoteName('#__user_keys'))
            ->where($this->db->quoteName('time') . ' < :now')
            ->bind(':now', $now);

        try {
            $this->db->setQuery($query)->execute();
        } catch (RuntimeException $e) {
            // We aren't concerned with errors from this query, carry on
        }

        // Find the matching record if it exists.
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['user_id', 'token', 'series', 'time']))
            ->from($this->db->quoteName('#__user_keys'))
            ->where($this->db->quoteName('series') . ' = :series')
            ->where($this->db->quoteName('uastring') . ' = :uastring')
            ->order($this->db->quoteName('time') . ' DESC')
            ->bind(':series', $series)
            ->bind(':uastring', $cookieName);

        try {
            $results = $this->db->setQuery($query)->loadObjectList();
        } catch (RuntimeException $e) {
            $response->status = Authentication::STATUS_FAILURE;

            return false;
        }

        if (count($results) !== 1) {
            // Destroy the cookie in the browser.
            $this->app->input->cookie->set($cookieName, '', 1, $this->app->get('cookie_path', '/'), $this->app->get('cookie_domain', ''));
            $response->status = Authentication::STATUS_FAILURE;

            return false;
        }

        // We have a user with one cookie with a valid series and a corresponding record in the database.
        if (!UserHelper::verifyPassword($cookieArray[0], $results[0]->token)) {
            /*
             * This is a real attack!
             * Either the series was guessed correctly or a cookie was stolen and used twice (once by attacker and once by victim).
             * Delete all tokens for this user!
             */
            $query = $this->db->getQuery(true)
                ->delete($this->db->quoteName('#__user_keys'))
                ->where($this->db->quoteName('user_id') . ' = :userid')
                ->bind(':userid', $results[0]->user_id);

            try {
                $this->db->setQuery($query)->execute();
            } catch (RuntimeException $e) {
                // Log an alert for the site admin
                Log::add(
                    sprintf('Failed to delete cookie token for user %s with the following error: %s', $results[0]->user_id, $e->getMessage()),
                    Log::WARNING,
                    'security'
                );
            }

            // Destroy the cookie in the browser.
            $this->app->input->cookie->set($cookieName, '', 1, $this->app->get('cookie_path', '/'), $this->app->get('cookie_domain', ''));

            // Issue warning by email to user and/or admin?
            Log::add(Text::sprintf('PLG_AUTHENTICATION_COOKIE_ERROR_LOG_LOGIN_FAILED', $results[0]->user_id), Log::WARNING, 'security');
            $response->status = Authentication::STATUS_FAILURE;

            return false;
        }

        // Make sure there really is a user with this name and get the data for the session.
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['id', 'username', 'password']))
            ->from($this->db->quoteName('#__users'))
            ->where($this->db->quoteName('username') . ' = :userid')
            ->where($this->db->quoteName('requireReset') . ' = 0')
            ->bind(':userid', $results[0]->user_id);

        try {
            $result = $this->db->setQuery($query)->loadObject();
        } catch (RuntimeException $e) {
            $response->status = Authentication::STATUS_FAILURE;

            return false;
        }

        if ($result) {
            // Bring this in line with the rest of the system
            $user = User::getInstance($result->id);

            // Set response data.
            $response->username = $result->username;
            $response->email    = $user->email;
            $response->fullname = $user->name;
            $response->password = $result->password;
            $response->language = $user->getParam('language');

            // Set response status.
            $response->status        = Authentication::STATUS_SUCCESS;
            $response->error_message = '';
        } else {
            $response->status        = Authentication::STATUS_FAILURE;
            $response->error_message = Text::_('JGLOBAL_AUTH_NO_USER');
        }
    }

    /**
     * We set the authentication cookie only after login is successfully finished.
     * We set a new cookie either for a user with no cookies or one
     * where the user used a cookie to authenticate.
     *
     * @param   array  $options  Array holding options
     *
     * @return  boolean  True on success
     *
     * @since   3.2
     */
    public function onUserAfterLogin($options)
    {
        // No remember me for admin
        if ($this->app->isClient('administrator')) {
            return false;
        }

        if (isset($options['responseType']) && $options['responseType'] === 'Cookie') {
            // Logged in using a cookie
            $cookieName = 'joomla_remember_me_' . UserHelper::getShortHashedUserAgent();

            // We need the old data to get the existing series
            $cookieValue = $this->app->input->cookie->get($cookieName);

            // Try with old cookieName (pre 3.6.0) if not found
            if (!$cookieValue) {
                $oldCookieName = UserHelper::getShortHashedUserAgent();
                $cookieValue   = $this->app->input->cookie->get($oldCookieName);

                // Destroy the old cookie in the browser
                $this->app->input->cookie->set($oldCookieName, '', 1, $this->app->get('cookie_path', '/'), $this->app->get('cookie_domain', ''));
            }

            $cookieArray = explode('.', $cookieValue);

            // Filter series since we're going to use it in the query
            $filter = new InputFilter();
            $series = $filter->clean($cookieArray[1], 'ALNUM');
        } elseif (!empty($options['remember'])) {
            // Remember checkbox is set
            $cookieName = 'joomla_remember_me_' . UserHelper::getShortHashedUserAgent();

            // Create a unique series which will be used over the lifespan of the cookie
            $unique     = false;
            $errorCount = 0;

            do {
                $series = UserHelper::genRandomPassword(20);
                $query  = $this->db->getQuery(true)
                    ->select($this->db->quoteName('series'))
                    ->from($this->db->quoteName('#__user_keys'))
                    ->where($this->db->quoteName('series') . ' = :series')
                    ->bind(':series', $series);

                try {
                    $results = $this->db->setQuery($query)->loadResult();

                    if ($results === null) {
                        $unique = true;
                    }
                } catch (RuntimeException $e) {
                    $errorCount++;

                    // We'll let this query fail up to 5 times before giving up, there's probably a bigger issue at this point
                    if ($errorCount === 5) {
                        return false;
                    }
                }
            } while ($unique === false);
        } else {
            return false;
        }

        // Get the parameter values
        $lifetime = $this->params->get('cookie_lifetime', 60) * 24 * 60 * 60;
        $length   = $this->params->get('key_length', 16);

        // Generate new cookie
        $token       = UserHelper::genRandomPassword($length);
        $cookieValue = $token . '.' . $series;

        // Overwrite existing cookie with new value
        $this->app->input->cookie->set(
            $cookieName,
            $cookieValue,
            time() + $lifetime,
            $this->app->get('cookie_path', '/'),
            $this->app->get('cookie_domain', ''),
            $this->app->isHttpsForced(),
            true
        );

        $query = $this->db->getQuery(true);

        if (!empty($options['remember'])) {
            $future = (time() + $lifetime);

            // Create new record
            $query
                ->insert($this->db->quoteName('#__user_keys'))
                ->set($this->db->quoteName('user_id') . ' = :userid')
                ->set($this->db->quoteName('series') . ' = :series')
                ->set($this->db->quoteName('uastring') . ' = :uastring')
                ->set($this->db->quoteName('time') . ' = :time')
                ->bind(':userid', $options['user']->username)
                ->bind(':series', $series)
                ->bind(':uastring', $cookieName)
                ->bind(':time', $future);
        } else {
            // Update existing record with new token
            $query
                ->update($this->db->quoteName('#__user_keys'))
                ->where($this->db->quoteName('user_id') . ' = :userid')
                ->where($this->db->quoteName('series') . ' = :series')
                ->where($this->db->quoteName('uastring') . ' = :uastring')
                ->bind(':userid', $options['user']->username)
                ->bind(':series', $series)
                ->bind(':uastring', $cookieName);
        }

        $hashedToken = UserHelper::hashPassword($token);

        $query->set($this->db->quoteName('token') . ' = :token')
            ->bind(':token', $hashedToken);

        try {
            $this->db->setQuery($query)->execute();
        } catch (RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * This is where we delete any authentication cookie when a user logs out
     *
     * @param   array  $options  Array holding options (length, timeToExpiration)
     *
     * @return  boolean  True on success
     *
     * @since   3.2
     */
    public function onUserAfterLogout($options)
    {
        // No remember me for admin
        if ($this->app->isClient('administrator')) {
            return false;
        }

        $cookieName  = 'joomla_remember_me_' . UserHelper::getShortHashedUserAgent();
        $cookieValue = $this->app->input->cookie->get($cookieName);

        // There are no cookies to delete.
        if (!$cookieValue) {
            return true;
        }

        $cookieArray = explode('.', $cookieValue);

        // Filter series since we're going to use it in the query
        $filter = new InputFilter();
        $series = $filter->clean($cookieArray[1], 'ALNUM');

        // Remove the record from the database
        $query = $this->db->getQuery(true)
            ->delete($this->db->quoteName('#__user_keys'))
            ->where($this->db->quoteName('series') . ' = :series')
            ->bind(':series', $series);

        try {
            $this->db->setQuery($query)->execute();
        } catch (RuntimeException $e) {
            // We aren't concerned with errors from this query, carry on
        }

        // Destroy the cookie
        $this->app->input->cookie->set($cookieName, '', 1, $this->app->get('cookie_path', '/'), $this->app->get('cookie_domain', ''));

        return true;
    }
}
