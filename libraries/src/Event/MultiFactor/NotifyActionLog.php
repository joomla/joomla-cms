<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\MultiFactor;

use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Concrete event class for the custom events used to notify the User Action Log plugin about Two
 * Factor Authentication actions.
 *
 * @since 4.2.0
 */
class NotifyActionLog extends AbstractImmutableEvent
{
    private const ACCEPTABLE_EVENTS = [
        'onComUsersCaptiveValidateSuccess',
        'onComUsersViewMethodsAfterDisplay',
        'onComUsersCaptiveShowCaptive',
        'onComUsersCaptiveShowSelect',
        'onComUsersCaptiveValidateFailed',
        'onComUsersCaptiveValidateInvalidMethod',
        'onComUsersCaptiveValidateTryLimitReached',
        'onComUsersCaptiveValidateSuccess',
        'onComUsersControllerMethodAfterRegenerateBackupCodes',
        'onComUsersControllerMethodBeforeAdd',
        'onComUsersControllerMethodBeforeDelete',
        'onComUsersControllerMethodBeforeEdit',
        'onComUsersControllerMethodBeforeSave',
        'onComUsersControllerMethodsBeforeDisable',
        'onComUsersControllerMethodsBeforeDoNotShowThisAgain',
    ];

    /**
     * Public constructor
     *
     * @param   string  $name       Event name. Must belong in self::ACCEPTABLE_EVENTS
     * @param   array   $arguments  Event arguments (different for each event).
     *
     * @since   4.2.0
     */
    public function __construct(string $name, array $arguments = [])
    {
        if (!\in_array($name, self::ACCEPTABLE_EVENTS)) {
            throw new \InvalidArgumentException(\sprintf('The %s event class does not support the %s event name.', __CLASS__, $name));
        }

        parent::__construct($name, $arguments);
    }
}
