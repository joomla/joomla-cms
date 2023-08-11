<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Result;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines an Event which has an append-only array argument named 'result'.
 *
 * This is used for Events whose handlers are expected to return something when called, similar to
 * how many plugin events worked in earlier versions of Joomla.
 *
 * This interface is partially implemented by the ResultAware trait. The typeCheckResult method is
 * implemented by the various ResultType*Aware traits. Your event needs to use both the ResultAware
 * trait and one of the ResultType*Aware traits. For example, if your event returns boolean results
 * you need to use the ResultAware and ResultTypeBooleanAware traits in your event.
 *
 * @since 4.2.0
 */
interface ResultAwareInterface
{
    /**
     * Appends data to the result array of the event.
     *
     * @param   mixed  $data  What to add to the result array.
     *
     * @return  void
     * @since   4.2.0
     */
    public function addResult($data): void;

    /**
     * Checks the type of the data being appended to the result argument.
     *
     * @param   mixed  $data  The data to type check
     *
     * @return  void
     * @throws  \InvalidArgumentException
     *
     * @internal
     * @since   4.2.0
     */
    public function typeCheckResult($data): void;
}
