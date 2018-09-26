<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\WebAsset\WebAssetRegistry;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the application's WebAsset dependency
 *
 * @since  __DEPLOY_VERSION__
 */
class WebAsset implements ServiceProviderInterface
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
		$container->alias('webasset', WebAssetRegistry::class)
			->alias('WebAsset', WebAssetRegistry::class)
			->alias('WebAssetRegistry', WebAssetRegistry::class)
			->share(
				WebAssetRegistry::class,
				function (Container $container)
				{
					return new WebAssetRegistry;
				},
				true
			);
	}
}
