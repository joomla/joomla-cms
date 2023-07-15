<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.remember
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! System Remember Me Plugin
 *
 * @since  1.5
 */

class PlgSystemRemember extends CMSPlugin
{
    /**
     * @var    \Joomla\CMS\Application\CMSApplication
     *
     * @since  3.2
     */
    protected $app;

    /**
     * @var    \Joomla\Database\DatabaseDriver
     *
     * @since  4.0.0
     */
    protected $db;

    /**
     * Remember me method to run onAfterInitialise
     * Only purpose is to initialise the login authentication process if a cookie is present
     *
     * @return  void
     *
     * @since   1.5
     *
     * @throws  InvalidArgumentException
     */
    public function onAfterInitialise()
    {
        // No remember me for admin.
        if ($this->app->isClient('administrator')) {
            return;
        }

        // Check for a cookie if user is not logged in
        if ($this->app->getIdentity()->get('guest')) {
            $cookieName = 'joomla_remember_me_' . UserHelper::getShortHashedUserAgent();

            // Check for the cookie
            if ($this->app->getInput()->cookie->get($cookieName)) {
                $this->app->login(['username' => ''], ['silent' => true]);
            }
        }
    }

    /**
     * Imports the authentication plugin on user logout to make sure that the cookie is destroyed.
     *
     * @param   array  $user     Holds the user data.
     * @param   array  $options  Array holding options (remember, autoregister, group).
     *
     * @return  boolean
     */
    public function onUserLogout($user, $options)
    {
        // No remember me for admin
        if ($this->app->isClient('administrator')) {
            return true;
        }

        $cookieName = 'joomla_remember_me_' . UserHelper::getShortHashedUserAgent();

        // Check for the cookie
        if ($this->app->getInput()->cookie->get($cookieName)) {
            // Make sure authentication group is loaded to process onUserAfterLogout event
            PluginHelper::importPlugin('authentication');
        }

        return true;
    }

    /**
     * Method is called before user data is stored in the database
     * Invalidate all existing remember-me cookies after a password change
     *
     * @param   array    $user   Holds the old user data.
     * @param   boolean  $isnew  True if a new user is stored.
     * @param   array    $data   Holds the new user data.
     *
     * @return  boolean
     *
     * @since   3.8.6
     */
    public function onUserBeforeSave($user, $isnew, $data)
    {
        // Irrelevant on new users
        if ($isnew) {
            return true;
        }

        // Irrelevant, because password was not changed by user
        if (empty($data['password_clear'])) {
            return true;
        }

        // But now, we need to do something - Delete all tokens for this user!
        $db    = $this->db;
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__user_keys'))
            ->where($db->quoteName('user_id') . ' = :userid')
            ->bind(':userid', $user['username']);

        try {
            $db->setQuery($query)->execute();
        } catch (RuntimeException $e) {
            // Log an alert for the site admin
            Log::add(
                sprintf('Failed to delete cookie token for user %s with the following error: %s', $user['username'], $e->getMessage()),
                Log::WARNING,
                'security'
            );
        }

        return true;
    }
}
