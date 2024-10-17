<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

use Joomla\Event\AbstractEvent;
use BadMethodCallException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This class implements the immutable base Event object used system-wide to offer orthogonality.
 * Note that it's implementation is very similar to \Joomla\Event\EventImmutable but it also contains the same custom
 * setter logic for constructors as in \Joomla\CMS\Event\AbstractEvent
 *
 * @see    \Joomla\CMS\Event\AbstractEvent
 * @since  4.0.0
 */
class AbstractImmutableEvent extends AbstractEvent
{
    /**
     * A flag to see if the constructor has been already called.
     *
     * @var    boolean
     * @since  4.0.0
     */
    private $constructed = false;

    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @since   4.0.0
     * @throws  \BadMethodCallException
     */
    public function __construct(string $name, array $arguments = [])
    {
        if ($this->constructed) {
            throw new \BadMethodCallException(
                \sprintf('Cannot reconstruct the AbstractImmutableEvent %s.', $this->name)
            );
        }

        $this->constructed = true;

        parent::__construct($name);

        // Same setter logic as in \Joomla\CMS\Event\AbstractEvent::setArgument
        foreach ($arguments as $argumentName => $value) {
            // Look for the method for the value pre-processing/validation
            $ucfirst     = ucfirst($name);
            $methodName1 = 'onSet' . $ucfirst;
            $methodName2 = 'set' . $ucfirst;

            if (method_exists($this, $methodName1)) {
                $value = $this->{$methodName1}($value);
            } elseif (method_exists($this, $methodName2)) {
                @trigger_error(
                    sprintf(
                        'Use method "%s" for value pre-processing is deprecated, and will not work in Joomla 6. Use "%s" instead. Event %s',
                        $methodName2,
                        $methodName1,
                        \get_class($this)
                    ),
                    E_USER_DEPRECATED
                );

                $value = $this->{$methodName2}($value);
            }

            $this->arguments[$argumentName] = $value;
        }
    }

    /**
     * Set the value of an event argument.
     *
     * @param   string  $name   The argument name.
     * @param   mixed   $value  The argument value.
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \BadMethodCallException
     */
    public function offsetSet($name, mixed $value): void
    {
        // B/C check for plugins which use $event['result'] = $result;
        if ($name === 'result') {
            $this->arguments[$name] = $value;

            @trigger_error(
                'Setting a result in an immutable event is deprecated, and will not work in Joomla 6. Event ' . $this->getName(),
                E_USER_DEPRECATED
            );

            return;
        }

        throw new \BadMethodCallException(
            \sprintf(
                'Cannot set the argument %s of the immutable event %s.',
                $name,
                $this->name
            )
        );
    }

    /**
     * Remove an event argument.
     *
     * @param   string  $name  The argument name.
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \BadMethodCallException
     */
    public function offsetUnset($name): void
    {
        throw new \BadMethodCallException(
            \sprintf(
                'Cannot remove the argument %s of the immutable event %s.',
                $name,
                $this->name
            )
        );
    }
}
