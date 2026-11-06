<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Authentication;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Remember Me cookie management.
 *
 * Centralised handling of "Remember Me" cookies. The cookie must only be
 * issued once the login is fully complete (including Multi-factor
 * Authentication) to prevent MFA bypass attacks.
 *
 * @since  5.4.9
 */
abstract class RememberMe
{
    /**
     * Creates or updates a Remember Me cookie for the given user.
     *
     * When $series is null a new series is generated and a new record is
     * inserted into the #__user_keys table. When an existing series is given
     * the matching record is updated with a fresh token.
     *
     * @param   User               $user    The user for whom to create the cookie
     * @param   CMSApplication     $app     The CMS application
     * @param   DatabaseInterface  $db      The database driver
     * @param   Registry           $params  Cookie plugin parameters (cookie_lifetime, key_length)
     * @param   string|null        $series  Existing series for update, null to create a new cookie
     *
     * @return  void
     *
     * @since   5.4.9
     */
    public static function createOrUpdateCookie(
        User $user,
        CMSApplication $app,
        DatabaseInterface $db,
        Registry $params,
        ?string $series = null
    ): void {
        // Generate the cookie name
        $cookieName = 'joomla_remember_me_' . UserHelper::getShortHashedUserAgent();

        // A new record has to be created when no existing series is given
        $isNew = ($series === null);

        // Generate a new, unique series if not provided
        if ($isNew) {
            $unique     = false;
            $errorCount = 0;

            do {
                $series = UserHelper::genRandomPassword(20);
                $query  = $db->createQuery()
                    ->select($db->quoteName('series'))
                    ->from($db->quoteName('#__user_keys'))
                    ->where($db->quoteName('series') . ' = :series')
                    ->bind(':series', $series);

                try {
                    $result = $db->setQuery($query)->loadResult();

                    if ($result === null) {
                        $unique = true;
                    }
                } catch (\RuntimeException) {
                    $errorCount++;

                    // We'll let this query fail up to 5 times before giving up, there's probably a bigger issue at this point
                    if ($errorCount === 5) {
                        return;
                    }
                }
            } while ($unique === false);
        }

        // Get the parameter values
        $lifetime = $params->get('cookie_lifetime', 60) * 24 * 60 * 60;
        $length   = $params->get('key_length', 16);

        // Generate new token and cookie value
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

        $query       = $db->createQuery();
        $hashedToken = UserHelper::hashPassword($token);
        $username    = $user->username;

        if ($isNew) {
            $future = time() + $lifetime;

            // Create new record
            $query
                ->insert($db->quoteName('#__user_keys'))
                ->set($db->quoteName('user_id') . ' = :userid')
                ->set($db->quoteName('series') . ' = :series')
                ->set($db->quoteName('uastring') . ' = :uastring')
                ->set($db->quoteName('time') . ' = :time')
                ->bind(':userid', $username)
                ->bind(':series', $series)
                ->bind(':uastring', $cookieName)
                ->bind(':time', $future);
        } else {
            // Update existing record with new token
            $query
                ->update($db->quoteName('#__user_keys'))
                ->where($db->quoteName('user_id') . ' = :userid')
                ->where($db->quoteName('series') . ' = :series')
                ->where($db->quoteName('uastring') . ' = :uastring')
                ->bind(':userid', $username)
                ->bind(':series', $series)
                ->bind(':uastring', $cookieName);
        }

        $query->set($db->quoteName('token') . ' = :token')
            ->bind(':token', $hashedToken);

        try {
            $db->setQuery($query)->execute();
        } catch (\RuntimeException) {
            // We aren't concerned with errors from this query, carry on
        }
    }
}
