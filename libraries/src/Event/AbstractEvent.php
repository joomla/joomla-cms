<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event;

use BadMethodCallException;
use Joomla\Event\Event;
use Joomla\Event\Event as BaseEvent;
use Joomla\String\Normalise;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This class implements the base Event object used system-wide to offer orthogonality. Core objects such as Models,
 * Controllers, etc create such events on-the-fly and dispatch them through the application's Dispatcher (colloquially
 * known as the "Joomla! plugin system"). This way a suitable plugin, typically a "system" plugin, can modify the
 * behaviour of any internal class, providing system-wide services such as tags, content versioning, comments or even
 * low-level services such as the implementation of created/modified/locked behaviours, record hit counter etc.
 *
 * You can create a new Event with something like this:
 *
 * $event = AbstractEvent::create('onModelBeforeSomething', $myModel, $arguments);
 *
 * You can access the subject object from your event Listener using $event['subject']. It is up to your listener to
 * determine whether it should apply its functionality against the subject.
 *
 * This AbstractEvent class implements a mutable event which is allowed to change its arguments at runtime. This is
 * generally unadvisable. It's best to use AbstractImmutableEvent instead and constrict all your interaction to the
 * subject class.
 *
 * @since  4.0.0
 */
abstract class AbstractEvent extends BaseEvent
{
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
     * @return  static
     *
     * @since   4.0.0
     * @throws  BadMethodCallException  If you do not provide a subject argument
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
            $bareName       = strpos($eventName, 'on') === 0 ? substr($eventName, 2) : $eventName;
            $parts          = Normalise::fromCamelCase($bareName, true);
            $eventClassName = __NAMESPACE__ . '\\' . ucfirst(array_shift($parts)) . '\\';
            $eventClassName .= implode('', $parts);
            $eventClassName .= 'Event';
        }

        // Make sure a non-empty subject argument exists and that it is an object
        if (!isset($arguments['subject']) || empty($arguments['subject']) || !\is_object($arguments['subject'])) {
            throw new BadMethodCallException("No subject given for the $eventName event");
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

    /**
     * Constructor. Overridden to go through the argument setters.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @since   4.0.0
     */
    public function __construct(string $name, array $arguments = [])
    {
        parent::__construct($name, $arguments);

        $this->arguments = [];

        foreach ($arguments as $argumentName => $value) {
            $this->setArgument($argumentName, $value);
        }
    }

    /**
     * Get an event argument value. It will use a getter method if one exists. The getters have the signature:
     *
     * get<ArgumentName>($value): mixed
     *
     * where:
     *
     * $value  is the value currently stored in the $arguments array of the event
     * It returns the value to return to the caller.
     *
     * @param   string  $name     The argument name.
     * @param   mixed   $default  The default value if not found.
     *
     * @return  mixed  The argument value or the default value.
     *
     * @since   4.0.0
     */
    public function getArgument($name, $default = null)
    {
        $methodName = 'get' . ucfirst($name);

        $value = parent::getArgument($name, $default);

        if (method_exists($this, $methodName)) {
            return $this->{$methodName}($value);
        }

        return $value;
    }

    /**
     * Add argument to event. It will use a setter method if one exists. The setters have the signature:
     *
     * set<ArgumentName>($value): mixed
     *
     * where:
     *
     * $value  is the value being set by the user
     * It returns the value to return to set in the $arguments array of the event.
     *
     * @param   string  $name   Argument name.
     * @param   mixed   $value  Value.
     *
     * @return  $this
     *
     * @since   4.0.0
     */
    public function setArgument($name, $value)
    {
        $methodName = 'set' . ucfirst($name);

        if (method_exists($this, $methodName)) {
            $value = $this->{$methodName}($value);
        }

        return parent::setArgument($name, $value);
    }
}
