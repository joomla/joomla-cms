<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Controller;

use Joomla\Controller\ControllerInterface;
use Joomla\Router\ResolvedRoute;

/**
 * Resolves a controller for the given route.
 *
 * @since  __DEPLOY_VERSION__
 */
class ControllerResolver implements ControllerResolverInterface
{
	/**
	 * Resolve the controller for a route
	 *
	 * @param   ResolvedRoute  $route  The route to resolve the controller for
	 *
	 * @return  callable
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException
	 */
	public function resolve(ResolvedRoute $route): callable
	{
		$controller = $route->getController();

		// Try to resolve a callable defined as an array
		if (is_array($controller))
		{
			if (isset($controller[0]) && is_string($controller[0]) && isset($controller[1]))
			{
				if (!class_exists($controller[0]))
				{
					throw new \InvalidArgumentException(sprintf('Cannot resolve controller for URI `%s`', $route->getUri()));
				}

				try
				{
					$controller[0] = $this->instantiateController($controller[0]);
				}
				catch (\ArgumentCountError $error)
				{
					// On PHP 7.1, we can catch missing argument errors to provide a more useful error message
					throw new \InvalidArgumentException(
						sprintf(
							'Controller `%s` has required constructor arguments, cannot instantiate the class', $controller[0]
						),
						0,
						$error
					);
				}
			}

			if (!is_callable($controller))
			{
				throw new \InvalidArgumentException(sprintf('Cannot resolve controller for URI `%s`', $route->getUri()));
			}

			return $controller;
		}

		// Try to resolve an invokable object
		if (is_object($controller))
		{
			if (!is_callable($controller))
			{
				throw new \InvalidArgumentException(sprintf('Cannot resolve controller for URI `%s`', $route->getUri()));
			}

			return $controller;
		}

		// Try to resolve a known function
		if (function_exists($controller))
		{
			return $controller;
		}

		// Try to resolve a class name if it implements our ControllerInterface
		if (is_string($controller) && interface_exists(ControllerInterface::class))
		{
			if (!class_exists($controller))
			{
				throw new \InvalidArgumentException(sprintf('Cannot resolve controller for URI `%s`', $route->getUri()));
			}

			try
			{
				return [$this->instantiateController($controller), 'execute'];
			}
			catch (\ArgumentCountError $error)
			{
				// On PHP 7.1, we can catch missing argument errors to provide a more useful error message
				throw new \InvalidArgumentException(
					sprintf(
						'Controller `%s` has required constructor arguments, cannot instantiate the class', $controller
					),
					0,
					$error
				);
			}
		}

		// Unsupported resolution
		throw new \InvalidArgumentException(sprintf('Cannot resolve controller for URI `%s`', $route->getUri()));
	}

	/**
	 * Instantiate a controller class
	 *
	 * @param   string  $class  The class to instantiate
	 *
	 * @return  object  Controller class instance
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function instantiateController(string $class)
	{
		return new $class;
	}
}
