<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

use Psr\Container\ContainerInterface;

/**
 * Decorator for an event listener to be pulled from the service container.
 *
 * @since  __DEPLOY_VERSION__
 */
final class LazyServiceEventListener
{
	/**
	 * The service container to load the service from
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $container;

	/**
	 * The ID of the service from the container to be used
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $serviceId;

	/**
	 * The method from the service to be called
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $method;

	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container  The service container to load the service from when it shall be executed
	 * @param   string              $serviceId  The ID of the service from the container to be used
	 * @param   string              $method     The method from the service to be called if necessary. If left empty, the service must be a callable;
	 *                                          (i.e. have an `__invoke()` method on a class)
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException if the service ID is empty
	 */
	public function __construct(ContainerInterface $container, string $serviceId, string $method = '')
	{
		if (empty($serviceId))
		{
			throw new \InvalidArgumentException(
				sprintf(
					'The $serviceId parameter cannot be empty in %s',
					self::class
				)
			);
		}

		$this->container = $container;
		$this->serviceId = $serviceId;
		$this->method    = $method;
	}

	/**
	 * Load a service from the container to listen to an event.
	 *
	 * @param   EventInterface  $event  The event to process
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException if the constructor's $method parameter is empty when not executing a callable service
	 * @throws  \RuntimeException if the service cannot be executed
	 */
	public function __invoke(EventInterface $event): void
	{
		if (!$this->container->has($this->serviceId))
		{
			throw new \RuntimeException(
				sprintf(
					'The "%s" service has not been registered to the service container',
					$this->serviceId
				)
			);
		}

		$service = $this->container->get($this->serviceId);

		// If the service is callable on its own, just execute it
		if (\is_callable($service))
		{
			\call_user_func($service, $event);

			return;
		}

		if (empty($this->method))
		{
			throw new \InvalidArgumentException(
				sprintf(
					'The $method argument is required when creating a "%s" to call a method from the "%s" service.',
					self::class,
					$this->serviceId
				)
			);
		}

		if (!method_exists($service, $this->method))
		{
			throw new \RuntimeException(
				sprintf(
					'The "%s" method does not exist on "%s" (from service "%s")',
					$this->method,
					\get_class($service),
					$this->serviceId
				)
			);
		}

		\call_user_func([$service, $this->method], $event);
	}
}
