<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event;

use BadMethodCallException;

/**
 * This class implements the immutable base Event object used system-wide to offer orthogonality.
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
     * @throws  BadMethodCallException
     */
    public function __construct(string $name, array $arguments = [])
    {
        if ($this->constructed) {
            throw new BadMethodCallException(
                sprintf('Cannot reconstruct the AbstractImmutableEvent %s.', $this->name)
            );
        }

        $this->constructed = true;

        parent::__construct($name, $arguments);
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
     * @throws  BadMethodCallException
     */
    public function offsetSet($name, $value)
    {
        throw new BadMethodCallException(
            sprintf(
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
     * @throws  BadMethodCallException
     */
    public function offsetUnset($name)
    {
        throw new BadMethodCallException(
            sprintf(
                'Cannot remove the argument %s of the immutable event %s.',
                $name,
                $this->name
            )
        );
    }
}
