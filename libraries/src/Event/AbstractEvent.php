<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

use Joomla\Event\Event;
use Joomla\String\Normalise;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
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
abstract class AbstractEvent extends Event
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
     * @throws  \BadMethodCallException  If you do not provide a subject argument
     */
    public static function create(string $eventName, array $arguments = [])
    {
        // Make sure a non-empty subject argument exists and that it is an object
        if (empty($arguments['subject']) || !\is_object($arguments['subject'])) {
            throw new \BadMethodCallException("No subject given for the $eventName event");
        }

        // Get the class name from the arguments, if specified
        $eventClassName = '';

        if (isset($arguments['eventClass'])) {
            $eventClassName = $arguments['eventClass'];

            unset($arguments['eventClass']);
        }

        if (!$eventClassName) {
            // Look for known class name.
            $eventClassName = self::getEventClassByEventName($eventName);

            if ($eventClassName === Event::class) {
                $eventClassName = '';
            }
        }

        /**
         * If the class name isn't set/found determine it from the event name, e.g. Table\BeforeLoadEvent from
         * the onTableBeforeLoad event name.
         */
        if (!$eventClassName || !class_exists($eventClassName, true)) {
            $bareName       = str_starts_with($eventName, 'on') ? substr($eventName, 2) : $eventName;
            $parts          = Normalise::fromCamelCase($bareName, true);
            $eventClassName = __NAMESPACE__ . '\\' . ucfirst(array_shift($parts)) . '\\';
            $eventClassName .= implode('', $parts);
            $eventClassName .= 'Event';
        }

        // Create and return the event object
        if (class_exists($eventClassName, true)) {
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

        foreach ($arguments as $argumentName => $value) {
            $this->setArgument($argumentName, $value);
        }
    }

    /**
     * Get an event argument value.
     * It will use a pre-processing method if one exists. The method has the signature:
     *
     * onGet<ArgumentName>($value): mixed
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
        // B/C check for numeric access to named argument, eg $event->getArgument('0').
        if (is_numeric($name)) {
            if (key($this->arguments) != 0) {
                $argNames = array_keys($this->arguments);
                $name     = $argNames[$name] ?? '';
            }

            @trigger_error(
                \sprintf(
                    'Numeric access to named event arguments is deprecated, and will not work in Joomla 6. Event %s argument %s',
                    \get_class($this),
                    $name
                ),
                E_USER_DEPRECATED
            );
        }

        // Look for the method for the value pre-processing/validation
        $ucfirst     = ucfirst($name);
        $methodName1 = 'onGet' . $ucfirst;
        $methodName2 = 'get' . $ucfirst;

        $value = parent::getArgument($name, $default);

        if (method_exists($this, $methodName1)) {
            return $this->{$methodName1}($value);
        }

        if (method_exists($this, $methodName2)) {
            @trigger_error(
                \sprintf(
                    'Use method "%s" for value pre-processing is deprecated, and will not work in Joomla 6. Use "%s" instead. Event %s',
                    $methodName2,
                    $methodName1,
                    \get_class($this)
                ),
                E_USER_DEPRECATED
            );

            return $this->{$methodName2}($value);
        }

        return $value;
    }

    /**
     * Add argument to event.
     * It will use a pre-processing method if one exists. The method has the signature:
     *
     * onSet<ArgumentName>($value): mixed
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
        // B/C check for numeric access to named argument, eg $event->setArgument('0', $value).
        if (is_numeric($name)) {
            if (key($this->arguments) != 0) {
                $argNames = array_keys($this->arguments);
                $name     = $argNames[$name] ?? '';
            }

            @trigger_error(
                \sprintf(
                    'Numeric access to named event arguments is deprecated, and will not work in Joomla 6. Event %s argument %s',
                    \get_class($this),
                    $name
                ),
                E_USER_DEPRECATED
            );
        }

        // Look for the method for the value pre-processing/validation
        $ucfirst     = ucfirst($name);
        $methodName1 = 'onSet' . $ucfirst;
        $methodName2 = 'set' . $ucfirst;

        if (method_exists($this, $methodName1)) {
            $value = $this->{$methodName1}($value);
        } elseif (method_exists($this, $methodName2)) {
            @trigger_error(
                \sprintf(
                    'Use method "%s" for value pre-processing is deprecated, and will not work in Joomla 6. Use "%s" instead. Event %s',
                    $methodName2,
                    $methodName1,
                    \get_class($this)
                ),
                E_USER_DEPRECATED
            );

            $value = $this->{$methodName2}($value);
        }

        return parent::setArgument($name, $value);
    }
}
