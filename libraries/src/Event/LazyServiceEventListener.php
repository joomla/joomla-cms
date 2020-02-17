<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event;

use Joomla\DI\ContainerAwareTrait;
use Joomla\Event\EventInterface;

\defined('JPATH_PLATFORM') or die;

/**
 * Decorator for an event listener to be pulled from the service container.
 *
 * @since  4.0.0
 */
final class LazyServiceEventListener
{
	use ContainerAwareTrait;

	/**
	 * The ID of the service from the container to be used
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	private $serviceId;

	/**
	 * The method from the service to be called
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	private $method;

	/**
	 * Constructor.
	 *
	 * @param   string  $serviceId  The ID of the service from the container to be used
	 * @param   string  $method     The method from the service to be called if necessary
	 *                              (if left empty, the service must be a callable; i.e. have an `__invoke()` method on a class)
	 *
	 * @since   4.0.0
	 * @throws  \InvalidArgumentException if the service ID is empty
	 */
	public function __construct(string $serviceId, string $method = '')
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
	 * @since   4.0.0
	 * @throws  \InvalidArgumentException if the constructor's $method parameter is empty when not executing a callable service
	 * @throws  \RuntimeException if the service cannot be executed
	 */
	public function __invoke(EventInterface $event)
	{
		if (!$this->container)
		{
			throw new \RuntimeException(
				sprintf(
					'The container has not been set in %s for %s, ensure you call the `setContainer()` method first',
					self::class,
					!empty($this->serviceId) ? ('the "' . $this->serviceId . '" service') : 'an unknown service'
				)
			);
		}

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
