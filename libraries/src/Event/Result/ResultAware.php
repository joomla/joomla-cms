<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Result;

use Joomla\Event\Event as BaseEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This Trait partially implements the ResultAwareInterface for mutable and immutable events.
 *
 * You must additionally implement the typeCheckResult method or use one of the ResultType*Aware
 * traits in your Event handler.
 *
 * @since  4.2.0
 */
trait ResultAware
{
    /**
     * Disallow setting the result argument directly with setArgument() instead of going through addResult().
     *
     * You should set this to true ONLY for event names which did NOT exist before Joomla 4.2.0
     * or if you are a third party developer introducing new event names for use only in your software.
     *
     * @var    boolean
     * @since  4.2.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Using setResult() for the result argument will always be disallowed.
     */
    protected $preventSetArgumentResult = false;

    /**
     * Appends data to the result array of the event.
     *
     * @param   mixed  $data  What to add to the result array.
     *
     * @return  void
     * @since   4.2.0
     */
    public function addResult($data): void
    {
        // Ensure this trait is applied to an Event object.
        if (!($this instanceof BaseEvent)) {
            throw new \LogicException(\sprintf('Event class ‘%s‘ must implement %s.', \get_class($this), BaseEvent::class));
        }

        // Ensure the Event object fully implements the ResultAwareInterface.
        if (!($this instanceof ResultAwareInterface)) {
            throw new \LogicException(\sprintf('Event class ‘%s‘ must implement %s.', \get_class($this), ResultAwareInterface::class));
        }

        // Make sure the data type is correct
        $this->typeCheckResult($data);

        // Append the result. We use the arguments property directly to allow this to work on immutable events.
        $this->arguments['result'] ??= [];
        $this->arguments['result'][] = $data;
    }

    /**
     * Handle setting the result argument directly.
     *
     * This method serves a dual purpose: backwards compatibility and enforcing the use of addResult.
     *
     * When $this->preventSetArgumentResult is false it acts as a backwards compatibility shim for
     * event handlers expecting generic event classes instead of the concrete Events implemented in
     * this package. This allows the migration to concrete event classes throughout the lifetime of
     * Joomla 4.x.
     *
     * When $this->preventSetArgumentResult is false (which will always be the case on Joomla 5.0)
     * it will throw a BadMethodCallException if the developer tries to call setArgument('result', ...)
     * instead of going through the addResult() method.
     *
     * @param   array  $value  The new result array.
     *
     * @return  array
     * @since   4.2.0
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *                Use counterpart with onSet prefix
     */
    protected function setResult(array $value)
    {
        if ($this->preventSetArgumentResult) {
            throw new \BadMethodCallException('You are not allowed to set the result argument directly. Use addResult() instead.');
        }

        // Always assume that the last element of the array is the result the handler is trying to append.
        $latestValue = array_pop($value);

        $this->addResult($latestValue);

        return $this->arguments['result'];
    }

    /**
     * Handle setting the result argument directly.
     *
     * This method serves a dual purpose: backwards compatibility and enforcing the use of addResult.
     *
     * When $this->preventSetArgumentResult is false it acts as a backwards compatibility shim for
     * event handlers expecting generic event classes instead of the concrete Events implemented in
     * this package. This allows the migration to concrete event classes throughout the lifetime of
     * Joomla 4.x.
     *
     * When $this->preventSetArgumentResult is false (which will always be the case on Joomla 5.0)
     * it will throw a BadMethodCallException if the developer tries to call setArgument('result', ...)
     * instead of going through the addResult() method.
     *
     * @param   array  $value  The new result array.
     *
     * @return  array
     * @since   4.4.0
     */
    protected function onSetResult(array $value)
    {
        return $this->setResult($value);
    }
}
