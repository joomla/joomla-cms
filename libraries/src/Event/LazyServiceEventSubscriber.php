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
final class LazyServiceEventSubscriber implements PluginInterface
{
    /**
     * The service container
     *
     * @var    ContainerInterface
     * @since  __DEPLOY_VERSION__
     */
    private $container;

    /**
     * Listener class name
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private $class;

    /**
     * List of subscribed events
     *
     * @var array
     * @since  __DEPLOY_VERSION__
     */
    private $subscribedEvents = [];

    /**
     * @var SubscriberInterface
     * @since  __DEPLOY_VERSION__
     */
    private $instance;

    /**
     * Constructor.
     *
     * @param \Psr\Container\ContainerInterface $container  Container
     * @param string                            $class      Listener class name
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(ContainerInterface $container, string $class)
    {
        $this->container = $container;
        $this->class     = $class;

        if (!is_subclass_of($class, SubscriberInterface::class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not implement %s', $class, SubscriberInterface::class));
        }

        $this->subscribedEvents = $class::getSubscribedEvents();
    }

    /**
     * Retrieve the instance of Subscriber.
     *
     * @return \Joomla\Event\SubscriberInterface
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getSubscriberInstance(): SubscriberInterface
    {
        if (!$this->instance) {
            $this->instance = $this->container->get($this->class);
        }

        return $this->instance;
    }

    /**
     * Returns an array of events the subscriber will listen to.
     *
     * @return array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getSubscribedEvents(): array
    {
        return $this->subscribedEvents;
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
     */
    public function __call(string $eventName, array $arguments): mixed
    {
        if (!\array_key_exists($eventName, $this->subscribedEvents)) {
            throw new \InvalidArgumentException(sprintf('Event "%s" not supported by "%s".', $eventName, $this->class));
        }

        $instance = $this->instance ?: $this->getSubscriberInstance();

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
