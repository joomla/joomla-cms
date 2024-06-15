<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Decorator for lazy subscribers be pulled from the service container when subscribed event dispatched.
 *
 * @since  __DEPLOY_VERSION__
 */
interface LazySubscriberInterface
{
    /**
     * Retrieve the instance of Subscriber.
     *
     * @return object
     *
     * @throws \Throwable
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getSubscriberInstance(): object;

    /**
     * Returns an array of events and the listeners, the subscriber will listen to.
     *
     * The array keys are event names and the value can be:
     *
     *   - The method name (of the subscriber instance) to call (priority defaults to 0)
     *   - An array composed of the method name (of the subscriber instance) to call and the priority
     *   - A callable instance to call
     *   - An array composed of the callable instance to call and the priority
     *
     *  For instance:
     *
     *   ['eventName' => 'methodName']
     *   ['eventName' => ['methodName', $priority]]
     *   ['eventName' => $callable]
     *   ['eventName' => [$callable, $priority]]
     *
     * @return array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getEventsAndListeners(): array;
}
