<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility trait to facilitate returning data from event handlers.
 *
 * @since 4.2.0
 */
trait EventReturnAware
{
    /**
     * Adds a result value to an event
     *
     * @param   Event   $event  The event we were processing
     * @param   mixed   $value  The value to append to the event's results
     *
     * @return  void
     * @since   4.2.0
     */
    private function returnFromEvent(Event $event, $value = null): void
    {
        if ($event instanceof ResultAwareInterface) {
            $event->addResult($value);
            return;
        }

        $result = $event->getArgument('result') ?: [];

        if (!\is_array($result)) {
            $result = [$result];
        }

        $result[] = $value;

        $event->setArgument('result', $result);
    }
}
