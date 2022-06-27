<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Result;

use InvalidArgumentException;

/**
 * This Trait partially implements the ResultAwareInterface for type checking.
 *
 * Events using this Trait (and the ResultAware trait) will expect event handlers to set results
 * of a Boolean type.
 *
 * @since  4.2.0
 */
trait ResultTypeBooleanAware
{
    /**
     * Can the result attribute values also be NULL?
     *
     * @var    boolean
     * @since  4.2.0
     */
    protected $resultIsNullable = false;

    /**
     * Checks the type of the data being appended to the result argument.
     *
     * @param   mixed  $data  The data to type check
     *
     * @return  void
     * @throws  InvalidArgumentException
     *
     * @internal
     * @since   4.2.0
     */
    public function typeCheckResult($data): void
    {
        if ($this->resultIsNullable && $data === null) {
            return;
        }

        if (!is_bool($data)) {
            throw new InvalidArgumentException(sprintf('Event %s only accepts Boolean results.', $this->getName()));
        }
    }
}
