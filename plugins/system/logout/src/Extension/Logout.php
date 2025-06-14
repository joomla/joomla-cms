<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.logout
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Logout\Extension;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Event\User\LogoutEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin class for logout redirect handling.
 *
 * @since  1.6
 */
final class Logout extends CMSPlugin implements SubscriberInterface
{
    /**
     * @param   DispatcherInterface      $dispatcher  The object to observe -- event dispatcher.
     * @param   array                    $config      An optional associative array of configuration settings.
     * @param   CMSApplicationInterface  $app         The object to observe -- event dispatcher.
     *
     * @since   1.6
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, CMSApplicationInterface $app)
    {
        parent::__construct($dispatcher, $config);

        $this->setApplication($app);

        // If we are on admin don't process.
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        $hash  = ApplicationHelper::getHash('PlgSystemLogout');

        if ($this->getApplication()->getInput()->cookie->getString($hash)) {
            // Destroy the cookie.
            $this->getApplication()->getInput()->cookie->set(
                $hash,
                '',
                1,
                $this->getApplication()->get('cookie_path', '/'),
                $this->getApplication()->get('cookie_domain', '')
            );
        }
    }

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onUserLogout' => 'onUserLogout',
        ];
    }

    /**
     * Method to handle any logout logic and report back to the subject.
     *
     * @param   LogoutEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onUserLogout(LogoutEvent $event): void
    {
        if ($this->getApplication()->isClient('site')) {
            // Create the cookie.
            $this->getApplication()->getInput()->cookie->set(
                ApplicationHelper::getHash('PlgSystemLogout'),
                true,
                time() + 86400,
                $this->getApplication()->get('cookie_path', '/'),
                $this->getApplication()->get('cookie_domain', ''),
                $this->getApplication()->isHttpsForced(),
                true
            );
        }
    }
}
