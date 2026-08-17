<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.cookie
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Authentication\Cookie\Extension;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Event\Privacy\CollectCapabilitiesEvent;
use Joomla\CMS\Event\User\AfterLoginEvent;
use Joomla\CMS\Event\User\AfterLogoutEvent;
use Joomla\CMS\Event\User\AuthenticationEvent;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;

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
final class Cookie extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

    /**
     * Create a new series used for authentication of a cookie.
     *
     * @return string | null
     */
    private function createNewSeries(): string
    {
        $series = "";
        $db     = $this->getDatabase();
        do {
            $series = UserHelper::genRandomPassword(20);
            $query  = $db->createQuery()
                ->select($db->quoteName('series'))
                ->from($db->quoteName('#__user_keys'))
                ->where($db->quoteName('series') . ' = :series')
                ->bind(':series', $series);

            try {
                $results = $db->setQuery($query)->loadResult();

                if ($results === null) {
                    $unique = true;
                }
            } catch (\RuntimeException $e) {
                $errorCount++;

                // We'll let this query fail up to 5 times before giving up, there's probably a bigger issue at this point
                if ($errorCount === 5) {
                    Log::add(Text::sprintf("Error %s in series generation", $e), Log::WARNING, 'security');
                    return null;
                }
            }
        } while ($unique === false);

        return $series;
    }

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onPrivacyCollectAdminCapabilities' => 'onPrivacyCollectAdminCapabilities',
            'onUserAuthenticate'                => 'onUserAuthenticate',
            'onUserAfterLogin'                  => 'onUserAfterLogin',
            'onUserAfterLogout'                 => 'onUserAfterLogout',
        ];
    }

    /**
     * Reports the privacy related capabilities for this plugin to site administrators.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onPrivacyCollectAdminCapabilities(CollectCapabilitiesEvent $event): void
    {
        $this->loadLanguage();

        $event->addResult([
            $this->getApplication()->getLanguage()->_('PLG_AUTHENTICATION_COOKIE') => [
                $this->getApplication()->getLanguage()->_('PLG_AUTHENTICATION_COOKIE_PRIVACY_CAPABILITY_COOKIE'),
            ],
        ]);
    }

    /**
     * This method should handle any authentication and report back to the subject
     *
     * @param   AuthenticationEvent  $event    Authentication event
     *
     * @return  void
     *
     * @since   3.2
     */
    public function onUserAuthenticate(AuthenticationEvent $event): void
    {
        $app = $this->getApplication();

        // No remember me for admin
        if ($app->isClient('administrator')) {
            return;
        }

        // Get cookie
        $cookieName  = 'joomla_remember_me_' . UserHelper::getShortHashedUserAgent();
        $cookieValue = $app->getInput()->cookie->get($cookieName);

        // Try with old cookieName (pre 3.6.0) if not found
        if (!$cookieValue) {
            $cookieName  = UserHelper::getShortHashedUserAgent();
            $cookieValue = $app->getInput()->cookie->get($cookieName);
        }

        if (!$cookieValue) {
            return;
        }

        $this->loadLanguage();

        $cookieArray = explode('.', $cookieValue);

        // Check for valid cookie value
        if (\count($cookieArray) !== 2) {
            // Destroy the cookie in the browser.
            $app->getInput()->cookie->set(
                $cookieName,
                '',
                [
                    'expires' => 1,
                    'path'    => $app->get('cookie_path', '/'),
                    'domain'  => $app->get('cookie_domain', ''),
                ]
            );
            Log::add('Invalid cookie detected.', Log::WARNING, 'error');

            return;
        }

        $response       = $event->getAuthenticationResponse();
        $response->type = 'Cookie';

        // Filter series since we're going to use it in the query
        $filter = new InputFilter();
        $series = $filter->clean($cookieArray[1], 'ALNUM');
        $now    = time();

        // Remove expired tokens
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->delete($db->quoteName('#__user_keys'))
            ->where($db->quoteName('time') . ' < :now')
            ->bind(':now', $now);

        try {
            $db->setQuery($query)->execute();
        } catch (\RuntimeException $e) {
            // We aren't concerned with errors from this query, carry on
        }

        // Find the matching record if it exists.
        $query = $db->createQuery()
            ->select($db->quoteName(['id', 'user_id', 'token', 'series', 'time']))
            ->from($db->quoteName('#__user_keys'))
            ->where($db->quoteName('series') . ' = :series')
            ->where($db->quoteName('uastring') . ' = :uastring')
            ->order($db->quoteName('time') . ' DESC')
            ->bind(':series', $series)
            ->bind(':uastring', $cookieName);

        try {
            $results = $db->setQuery($query)->loadObjectList();
        } catch (\RuntimeException $e) {
            $response->status = Authentication::STATUS_FAILURE;
            return;
        }

        if (\count($results) === 0) {
            // Destroy the cookie in the browser.
            $app->getInput()->cookie->set(
                $cookieName,
                '',
                [
                    'expires' => 1,
                    'path'    => $app->get('cookie_path', '/'),
                    'domain'  => $app->get('cookie_domain', ''),
                ]
            );
            $response->status = Authentication::STATUS_FAILURE;
            return;
        }

        // For concurrency correction, we keep multiple token in the DB for each session,
        // and delete all older ones (same user agent, smaller id) *after* auth with a newer token.
        $token_id = -1;
        $token_time = 0;
        $user_id = $results[0]->user_id;
        for ($id=0; $id<count($results); $id++) {
            if (UserHelper::verifyPassword($cookieArray[0], $results[$id]->token)) {
                $token_id = $results[$id]->id;
                $token_time = $results[$id]->time;
                $user_id = $results[$id]->user_id;
                break;
            }
        }

        // We have a user with one cookie with a valid series and a corresponding record in the database.
        if ($token_id === -1) {
            /*
             * This is a real attack!
             * Either the series was guessed correctly or a cookie was stolen and used twice (once by attacker and once by victim).
             * It may also be that the user tried to login twice with the same cookie, e.g. due to browser reload.
             * Delete all token for this user id!
             */
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__user_keys'))
                ->where($db->quoteName('user_id') . ' = :userid')
                ->bind(':userid', $user_id);

            try {
                $db->setQuery($query)->execute();
            } catch (\RuntimeException $e) {
                // Log an alert for the site admin
                Log::add(
                    \sprintf('Failed to delete cookie token for user %s with the following error: %s', $results[0]->user_id, $e->getMessage()),
                    Log::WARNING,
                    'security'
                );
            }

            // Destroy the cookie in the browser.
            $app->getInput()->cookie->set(
                $cookieName,
                '',
                [
                    'expires' => 1,
                    'path'    => $app->get('cookie_path', '/'),
                    'domain'  => $app->get('cookie_domain', ''),
                ]
            );

            // Issue warning by email to user and/or admin?
            Log::add(Text::sprintf('PLG_AUTHENTICATION_COOKIE_ERROR_LOG_LOGIN_FAILED', $user_id), Log::WARNING, 'security');
            $response->status = Authentication::STATUS_FAILURE;

            return;
        }

        // Delete previous auth tokens for this session
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->delete($db->quoteName('#__user_keys'))
            ->where($db->quoteName('user_id') . ' = :userid')
            ->where($db->quoteName('uastring') . ' = :uastring')
            ->where($db->quoteName('id') . ' < :token_id')
            ->bind(':userid', $user_id)
            ->bind(':uastring', $cookieName)
            ->bind(':token_id', $token_id);

        try {
            $db->setQuery($query)->execute();
        } catch (\RuntimeException) {
            // We aren't concerned with errors from this query, carry on
        }

        // Make sure there really is a user with this name and get the data for the session.
        $query = $db->createQuery()
            ->select($db->quoteName(['id', 'username', 'password']))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('username') . ' = :userid')
            ->where($db->quoteName('requireReset') . ' = 0')
            ->bind(':userid', $user_id);

        try {
            $result = $db->setQuery($query)->loadObject();
        } catch (\RuntimeException) {
            $response->status = Authentication::STATUS_FAILURE;

            return;
        }

        if ($result) {
            // Bring this in line with the rest of the system
            $user = $this->getUserFactory()->loadUserById($result->id);

            // Set response data.
            $response->username = $result->username;
            $response->email    = $user->email;
            $response->fullname = $user->name;
            $response->password = $result->password;
            $response->language = $user->getParam('language');

            // Set response status.
            $response->status        = Authentication::STATUS_SUCCESS;
            $response->error_message = '';

            // Stop event propagation when status is STATUS_SUCCESS
            $event->stopPropagation();

            $length       = $this->params->get('key_length', 16);
            // Generate new cookie
            $token        = UserHelper::genRandomPassword($length);
            $hashedToken  = UserHelper::hashPassword($token);
            $series = $this->createNewSeries();
            if ($series === null) {
                $response->status = Authentication::STATUS_FAILURE;

                return;
            }
            $cookieValue  = $token . '.' . $series;
            $lifetime     = $this->params->get('cookie_lifetime', 60) * 24 * 60 * 60;

            // Overwrite existing cookie with new value
            $app->getInput()->cookie->set(
                $cookieName,
                $cookieValue,
                [
                    'expires'  => time() + $lifetime,
                    'path'     => $app->get('cookie_path', '/'),
                    'domain'   => $app->get('cookie_domain', ''),
                    'secure'   => $app->isHttpsForced(),
                    'httponly' => true,
                ]
            );

            // Insert the new value in the DB. For concurrency reason, old cookies are deleted
            // only after the next successful athentication.
            $query = $db->createQuery()
                ->insert($db->quoteName('#__user_keys'))
                ->set($db->quoteName('user_id') . ' = :userid')
                ->set($db->quoteName('series') . ' = :series')
                ->set($db->quoteName('uastring') . ' = :uastring')
                ->set($db->quoteName('time') . ' = :time')
                ->set($db->quoteName('token') . ' = :token')
                ->bind(':userid', $result->username)
                ->bind(':series', $series)
                ->bind(':uastring', $cookieName)
                ->bind(':time', $token_time)
                ->bind(':token', $hashedToken);

            try {
                $db->setQuery($query)->execute();
            } catch (\RuntimeException $e) {
                Log::add(Text::sprintf("Error %s in series / token update", $e), Log::WARNING, 'security');
            }
        } else {
            $response->status        = Authentication::STATUS_FAILURE;
            Log::add(Text::sprintf('Authentication failed due to absence of user id %s.', $results[0]->user_id), Log::WARNING, 'security');
            $response->error_message = $app->getLanguage()->_('JGLOBAL_AUTH_NO_USER');
        }
    }

    /**
     * We set the authentication cookie only after login is successfully finished.
     * We set a new cookie either for a user with no cookies or one
     * where the user used a cookie to authenticate.
     *
     * @param   AfterLoginEvent  $event  Login event
     *
     * @return  void
     *
     * @since   3.2
     */
    public function onUserAfterLogin(AfterLoginEvent $event): void
    {
        $app = $this->getApplication();

        // No remember me for admin, and cookie is set only on "remember me" option
        if ($app->isClient('administrator') || empty($options['remember'])) {
            return;
        }

        $db      = $this->getDatabase();
        $options = $event->getOptions();

        $cookieName = 'joomla_remember_me_' . UserHelper::getShortHashedUserAgent();

        // Create a unique series which will be used over the lifespan of the cookie
        $unique     = false;
        $errorCount = 0;

        $series = $this->createNewSeries();
        if ($series === null) {
            return;
        }

        // Get the parameter values
        $lifetime = $this->params->get('cookie_lifetime', 60) * 24 * 60 * 60;
        $length   = $this->params->get('key_length', 16);

        // Generate new cookie
        $token       = UserHelper::genRandomPassword($length);
        $cookieValue = $token . '.' . $series;

        // Overwrite existing cookie with new value
        $app->getInput()->cookie->set(
            $cookieName,
            $cookieValue,
            [
                'expires'  => time() + $lifetime,
                'path'     => $app->get('cookie_path', '/'),
                'domain'   => $app->get('cookie_domain', ''),
                'secure'   => $app->isHttpsForced(),
                'httponly' => true,
            ]
        );

        $future = (time() + $lifetime);
        $hashedToken = UserHelper::hashPassword($token);

        // Create new record
        $query = $db->createQuery()
            ->insert($db->quoteName('#__user_keys'))
            ->set($db->quoteName('user_id') . ' = :userid')
            ->set($db->quoteName('series') . ' = :series')
            ->set($db->quoteName('uastring') . ' = :uastring')
            ->set($db->quoteName('time') . ' = :time')
            ->set($db->quoteName('token') . ' = :token')
            ->bind(':userid', $options['user']->username)
            ->bind(':series', $series)
            ->bind(':uastring', $cookieName)
            ->bind(':time', $future)
            ->bind(':token', $hashedToken);

        try {
            $db->setQuery($query)->execute();
        } catch (\RuntimeException) {
            // We aren't concerned with errors from this query, carry on
        }
    }

    /**
     * This is where we delete any authentication cookie when a user logs out
     *
     * @param   AfterLogoutEvent  $event  Logout event
     *
     * @return  void
     *
     * @since   3.2
     */
    public function onUserAfterLogout(AfterLogoutEvent $event): void
    {
        $app = $this->getApplication();

        // No remember me for admin
        if ($app->isClient('administrator')) {
            return;
        }

        $cookieName  = 'joomla_remember_me_' . UserHelper::getShortHashedUserAgent();
        $cookieValue = $app->getInput()->cookie->get($cookieName);

        // There are no cookies to delete.
        if (!$cookieValue) {
            return;
        }

        $cookieArray = explode('.', $cookieValue);

        // Filter series since we're going to use it in the query
        $filter = new InputFilter();
        $series = $filter->clean($cookieArray[1], 'ALNUM');

        // Remove the record from the database
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->delete($db->quoteName('#__user_keys'))
            ->where($db->quoteName('series') . ' = :series')
            ->bind(':series', $series);

        try {
            $db->setQuery($query)->execute();
        } catch (\RuntimeException) {
            // We aren't concerned with errors from this query, carry on
        }

        // Destroy the cookie
        $app->getInput()->cookie->set(
            $cookieName,
            '',
            [
                'expires' => 1,
                'path'    => $app->get('cookie_path', '/'),
                'domain'  => $app->get('cookie_domain', ''),
            ]
        );
    }
}
