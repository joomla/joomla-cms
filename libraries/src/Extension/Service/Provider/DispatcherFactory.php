<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the service dispatcher factory.
 *
 * @since  __DEPLOY_VERSION__
 */
class DispatcherFactory implements ServiceProviderInterface
{
	/**
	 * The module namespace
	 *
	 * @var  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $namespace;

	/**
	 * DispatcherFactory constructor.
	 *
	 * @param   string  $namespace  The namespace
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function register(Container $container)
	{
		$container->set(
			DispatcherFactoryInterface::class,
			function (Container $container)
			{
				$mvcFactoryFactory = null;

				if ($container->has(MVCFactoryFactoryInterface::class))
				{
					$mvcFactoryFactory = $container->get(MVCFactoryFactoryInterface::class);
				}

				return new \Joomla\CMS\Dispatcher\DispatcherFactory($this->namespace, $mvcFactoryFactory);
			}
		);
	}
}
