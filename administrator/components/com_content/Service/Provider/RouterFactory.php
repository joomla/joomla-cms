<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Categories\Categories;
use Joomla\Database\DatabaseInterface;

/**
 * Service provider for the service dispatcher factory.
 *
 * @since  __DEPLOY_VERSION__
 */
class RouterFactory implements ServiceProviderInterface
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
			RouterFactoryInterface::class,
			function (Container $container)
			{
				return new \Joomla\Component\Content\Site\Router\RouterFactory(
					$container->get(Categories::class)[''],
					$container->get(DatabaseInterface::class)
				);
			}
		);
	}
}
