<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Association\AssociationExtensionInterface;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the service based components.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class Component implements ServiceProviderInterface
{
	/**
	 * Creates the ComponentInterface instance.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComponentInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public abstract function createComponentClass(Container $container): ComponentInterface;

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
			ComponentInterface::class,
			function (Container $container)
			{
				$component = $this->createComponentClass($container);

				if ($container->has(Categories::class))
				{
					$component->setCategories($container->get(Categories::class));
				}

				if ($container->has(DispatcherFactoryInterface::class))
				{
					$component->setDispatcherFactory($container->get(DispatcherFactoryInterface::class));
				}

				if ($container->has(MVCFactoryFactoryInterface::class))
				{
					$component->setMvcFactory($container->get(MVCFactoryFactoryInterface::class));
				}

				if ($component instanceof AssociationServiceInterface)
				{
					$component->setAssociationExtension($container->get(AssociationExtensionInterface::class));
				}

				return $component;
			}
		);
	}
}
