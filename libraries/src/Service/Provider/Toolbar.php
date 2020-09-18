<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Toolbar\ContainerAwareToolbarFactory;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the application's toolbar dependency
 *
 * @since  4.0
 */
class Toolbar implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function register(Container $container)
	{
		$container->alias('toolbar.factory', ToolbarFactoryInterface::class)
			->alias(ContainerAwareToolbarFactory::class, ToolbarFactoryInterface::class)
			->share(
				ToolbarFactoryInterface::class,
				function (Container $container)
				{
					$factory = new ContainerAwareToolbarFactory;
					$factory->setContainer($container);

					return $factory;
				},
				true
			);
	}
}
