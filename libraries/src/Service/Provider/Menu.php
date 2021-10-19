<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Menu\MenuFactory;
use Joomla\CMS\Menu\MenuFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the application's menu dependency
 *
 * @since  4.0.0
 */
class Menu implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function register(Container $container)
	{
		$container->alias('menu.factory', MenuFactoryInterface::class)
			->alias(MenuFactory::class, MenuFactoryInterface::class)
			->share(
				MenuFactoryInterface::class,
				function (Container $container)
				{
					return new MenuFactory;
				},
				true
			);
	}
}
