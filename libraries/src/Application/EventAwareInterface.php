<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Application;

use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface defining application that can trigger Joomla 3.x style events
 *
 * @since       4.0.0
 * @deprecated  5.0   This interface will be removed as the Joomla 3.x compatibility layer will be removed
 */
interface EventAwareInterface extends DispatcherAwareInterface
{
    /**
     * Get the event dispatcher.
     *
     * @return  DispatcherInterface
     *
     * @since   4.0.0
     * @throws  \UnexpectedValueException May be thrown if the dispatcher has not been set.
     */
    public function getDispatcher();

    /**
     * Calls all handlers associated with an event group.
     *
     * This is a legacy method, implementing old-style (Joomla! 3.x) plugin calls. It's best to go directly through the
     * Dispatcher and handle the returned EventInterface object instead of going through this method. This method is
     * deprecated and will be removed in Joomla! 5.x.
     *
     * This method will only return the 'result' argument of the event
     *
     * @param   string       $eventName  The event name.
     * @param   array|Event  $args       An array of arguments or an Event object (optional).
     *
     * @return  array  An array of results from each function call. Note this will be an empty array if no dispatcher is set.
     *
     * @since       4.0.0
     * @throws      \InvalidArgumentException
     * @deprecated  5.0
     */
    public function triggerEvent($eventName, $args = []);
}
