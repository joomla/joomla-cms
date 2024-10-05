<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.remember
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Remember\Extension;

use Joomla\CMS\Event\Application\AfterInitialiseEvent;
use Joomla\CMS\Event\User\BeforeSaveEvent;
use Joomla\CMS\Event\User\LogoutEvent;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! System Remember Me Plugin
 *
 * @since  1.5
 */
final class Remember extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise' => 'onAfterInitialise',
            'onUserLogout'      => 'onUserLogout',
            'onUserBeforeSave'  => 'onUserBeforeSave',
        ];
    }

    /**
     * Remember me method to run onAfterInitialise
     * Only purpose is to initialise the login authentication process if a cookie is present
     *
     * @param   AfterInitialiseEvent  $event  The event instance.
     *
     * @return  void
     *
     * @since   1.5
     *
     * @throws  \InvalidArgumentException
     */
    public function onAfterInitialise(AfterInitialiseEvent $event): void
    {
        // No remember me for admin.
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        // Check for a cookie if user is not logged in
        if ($this->getApplication()->getIdentity()->guest) {
            $cookieName = 'joomla_remember_me_' . UserHelper::getShortHashedUserAgent();

            // Check for the cookie
            if ($this->getApplication()->getInput()->cookie->get($cookieName)) {
                $this->getApplication()->login(['username' => ''], ['silent' => true]);
            }
        }
    }

    /**
     * Imports the authentication plugin on user logout to make sure that the cookie is destroyed.
     *
     * @param   LogoutEvent $event  The event instance.
     *
     * @return  void
     */
    public function onUserLogout(LogoutEvent $event): void
    {
        // No remember me for admin
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        $cookieName = 'joomla_remember_me_' . UserHelper::getShortHashedUserAgent();

        // Check for the cookie
        if ($this->getApplication()->getInput()->cookie->get($cookieName)) {
            // Make sure authentication group is loaded to process onUserAfterLogout event
            PluginHelper::importPlugin('authentication');
        }
    }

    /**
     * Method is called before user data is stored in the database
     * Invalidate all existing remember-me cookies after a password change
     *
     * @param   BeforeSaveEvent $event  The event instance.
     *
     * @return  boolean
     *
     * @since   3.8.6
     */
    public function onUserBeforeSave(BeforeSaveEvent $event): void
    {
        $user  = $event->getUser();
        $isnew = $event->getIsNew();
        $data  = $event->getData();

        // Irrelevant on new users
        if ($isnew) {
            return;
        }

        // Irrelevant, because password was not changed by user
        if (empty($data['password_clear'])) {
            return;
        }

        // But now, we need to do something - Delete all tokens for this user!
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__user_keys'))
            ->where($db->quoteName('user_id') . ' = :userid')
            ->bind(':userid', $user['username']);

        try {
            $db->setQuery($query)->execute();
        } catch (\RuntimeException $e) {
            // Log an alert for the site admin
            Log::add(
                \sprintf('Failed to delete cookie token for user %s with the following error: %s', $user['username'], $e->getMessage()),
                Log::WARNING,
                'security'
            );
        }
    }
}
