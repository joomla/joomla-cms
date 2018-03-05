<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the component's dispatcher dependency
 *
 * @since  __DEPLOY_VERSION__
 */
class Component implements ServiceProviderInterface
{
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
			'component',
			function (Container $container)
			{
				$component = new \Joomla\CMS\Extension\Component;

				if ($container->has('categories'))
				{
					$component->setCategories($container->get('categories'));
				}

				if ($container->has(MVCFactoryFactoryInterface::class))
				{
					$component->setMvcFactory($container->get(MVCFactoryFactoryInterface::class));
				}

				return $component;
			}
		);
	}
}
