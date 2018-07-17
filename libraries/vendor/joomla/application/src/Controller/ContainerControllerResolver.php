<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Controller;

use Psr\Container\ContainerInterface;

/**
 * Controller resolver which supports creating controllers from a PSR-11 compatible container
 *
 * Controllers must be registered in the container using their FQCN as a service key
 *
 * @since  __DEPLOY_VERSION__
 */
class ContainerControllerResolver extends ControllerResolver
{
	/**
	 * The container to search for controllers in
	 *
	 * @var    ContainerInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $container;

	/**
	 * Constructor
	 *
	 * @param   ContainerInterface  $container  The container to search for controllers in
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
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
		if ($this->container->has($class))
		{
			return $this->container->get($class);
		}

		return parent::instantiateController($class);
	}
}
