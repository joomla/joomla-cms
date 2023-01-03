<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event;

use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Joomla\String\Normalise;

/**
 * This class is a factory system that allows the creation of most core events in the Joomla Ecosystem, falling back to
 * the creation of a \Joomla\CMS\Event\GenericEvent object for the class if no specific event class is matched. You can
 * create a new Event with something like this:
 *
 * $event = EventFactory::create('onModelBeforeSomething', $myModel, $arguments);
 *
 * You can access the subject object from your event Listener using $event['subject']. It is up to your listener to
 * determine whether it should apply its functionality against the subject.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class EventFactory {
    use CoreEventAware;

    /**
     * Creates a new CMS event object for a given event name and subject. The following arguments must be given:
     * subject      object  The subject of the event. This is the core object you are going to manipulate.
     * eventClass   string  The Event class name. If you do not provide it Joomla\CMS\Events\<eventNameWithoutOnPrefix>
     *                      will be used.
     *
     * @param   string  $eventName  The name of the event, e.g. onTableBeforeLoad
     * @param   array   $arguments  Additional arguments to pass to the event
     *
     * @return  EventInterface
     *
     * @since   4.0.0
     * @throws  \BadMethodCallException  If you do not provide a subject argument
     */
    public static function create(string $eventName, array $arguments = [])
    {
        // Get the class name from the arguments, if specified
        $eventClassName = '';

        if (isset($arguments['eventClass'])) {
            $eventClassName = $arguments['eventClass'];

            unset($arguments['eventClass']);
        }

        /**
         * If the class name isn't set/found determine it from the event name, e.g. TableBeforeLoadEvent from
         * the onTableBeforeLoad event name.
         */
        if (empty($eventClassName) || !class_exists($eventClassName, true)) {
            $bareName = strpos($eventName, 'on') === 0 ? substr($eventName, 2) : $eventName;
            $parts = Normalise::fromCamelCase($bareName, true);
            $eventClassName = __NAMESPACE__ . '\\' . ucfirst(array_shift($parts)) . '\\';
            $eventClassName .= implode('', $parts);
            $eventClassName .= 'Event';
        }

        // Make sure a non-empty subject argument exists and that it is an object
        if (!isset($arguments['subject']) || empty($arguments['subject']) || !\is_object($arguments['subject'])) {
            throw new \BadMethodCallException("No subject given for the $eventName event");
        }

        // Create and return the event object
        if (class_exists($eventClassName, true)) {
            return new $eventClassName($eventName, $arguments);
        }

        /**
         * The detection code above failed. This is to be expected, it was written back when we only
         * had the Table events. It does not address most other core events. So, let's use our
         * fancier detection instead.
         */
        $eventClassName = self::getEventClassByEventName($eventName);

        if (!empty($eventClassName) && ($eventClassName !== Event::class)) {
            return new $eventClassName($eventName, $arguments);
        }

        return new GenericEvent($eventName, $arguments);
    }
}
