<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Psr\Container\ContainerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Decorator for lazy subscribers be pulled from the service container when subscribed event dispatched.
 *
 * @since  __DEPLOY_VERSION__
 */
final class LazyServiceSubscriber implements LazySubscriberInterface, PluginInterface
{
    /**
     * The service container
     *
     * @var    ContainerInterface
     * @since  __DEPLOY_VERSION__
     */
    private $container;

    /**
     * Subscriber class name, and id in the container
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private $class;

    /**
     * List of events, and listeners.
     *
     * @var array
     * @since  __DEPLOY_VERSION__
     */
    private $eventsAndListeners = [];

    /**
     * Subscriber instance.
     *
     * @var object
     * @since  __DEPLOY_VERSION__
     */
    private $instance;

    /**
     * Constructor.
     *
     * @param \Psr\Container\ContainerInterface $container  Container
     * @param string                            $class      Subscriber class name, and id in the container
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(ContainerInterface $container, string $class)
    {
        $this->container = $container;
        $this->class     = $class;

        if (!is_subclass_of($class, SubscriberInterface::class)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not implement %s', $class, SubscriberInterface::class));
        }

        $this->eventsAndListeners = $class::getSubscribedEvents();
    }

    /**
     * Retrieve the instance of Subscriber.
     *
     * @return object
     *
     * @throws \Throwable
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getSubscriberInstance(): object
    {
        if (!$this->instance) {
            $this->instance = $this->container->get($this->class);
        }

        return $this->instance;
    }

    /**
     * Returns an array of events and the listeners, the subscriber will listen to.
     *
     * @return array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getEventsAndListeners(): array
    {
        return $this->eventsAndListeners;
    }

    /**
     * Method to call the event listeners.
     *
     * @param string $eventName
     * @param array  $arguments
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __call(string $eventName, array $arguments): mixed
    {
        $instance = $this->instance ?: $this->getSubscriberInstance();

        if (!\is_callable([$instance, $eventName])) {
            throw new \InvalidArgumentException(sprintf('Event "%s" not supported by %s.', $eventName, $this->class));
        }

        return \call_user_func_array([$instance, $eventName], $arguments);
    }

    /**
     * A dummy to complete the interface.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function registerListeners()
    {
    }

    /**
     * A dummy to complete the interface.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setDispatcher(DispatcherInterface $dispatcher)
    {
    }
}
