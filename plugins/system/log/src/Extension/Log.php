<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.log
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Log\Extension;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Event\User\LoginFailureEvent;
use Joomla\CMS\Log\Log as Logger;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! System Logging Plugin.
 *
 * @since  1.5
 */
final class Log extends CMSPlugin implements SubscriberInterface
{
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
            'onUserLoginFailure' => 'onUserLoginFailure',
        ];
    }

    /**
     * Called if user fails to be logged in.
     *
     * @param   LoginFailureEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   1.5
     */
    public function onUserLoginFailure(LoginFailureEvent $event): void
    {
        $response = $event->getAuthenticationResponse();
        $errorlog = [];

        switch ($response['status']) {
            case Authentication::STATUS_SUCCESS:
                $errorlog['status']  = $response['type'] . ' CANCELED: ';
                $errorlog['comment'] = $response['error_message'];
                break;

            case Authentication::STATUS_FAILURE:
                $errorlog['status']  = $response['type'] . ' FAILURE: ';

                if ($this->params->get('log_username', 0)) {
                    $errorlog['comment'] = $response['error_message'] . ' ("' . $response['username'] . '")';
                } else {
                    $errorlog['comment'] = $response['error_message'];
                }
                break;

            default:
                $errorlog['status']  = $response['type'] . ' UNKNOWN ERROR: ';
                $errorlog['comment'] = $response['error_message'];
                break;
        }

        Logger::addLogger([], Logger::INFO);

        try {
            Logger::add($errorlog['comment'], Logger::INFO, $errorlog['status']);
        } catch (\Exception) {
            // If the log file is unwriteable during login then we should not go to the error page
            return;
        }
    }
}
