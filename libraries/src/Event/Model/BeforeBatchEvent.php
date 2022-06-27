<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Model;

use BadMethodCallException;
use Joomla\CMS\Event\AbstractImmutableEvent;

/**
 * Event class for modifying a table object before a batch event is applied
 *
 * @since  4.0.0
 */
class BeforeBatchEvent extends AbstractImmutableEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  BadMethodCallException
     *
     * @since   4.0.0
     */
    public function __construct($name, array $arguments = array())
    {
        if (!\array_key_exists('src', $arguments)) {
            throw new BadMethodCallException("Argument 'src' is required for event $name");
        }

        if (!\array_key_exists('type', $arguments)) {
            throw new BadMethodCallException("Argument 'type' is required for event $name");
        }

        parent::__construct($name, $arguments);
    }
}
