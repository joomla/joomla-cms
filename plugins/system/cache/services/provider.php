<?php
/**
 * @package         Joomla.Plugin
 * @subpackage      System.cache
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Cache\Extension\Cache;

return new class implements ServiceProviderInterface {
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function register(Container $container)
	{
		$container->set(
			PluginInterface::class,
			function (Container $container)
			{
				$plugin                 = PluginHelper::getPlugin('system', 'cache');
				$dispatcher             = $container->get(DispatcherInterface::class);
				$documentFactory        = $container->get('document.factory');
				$cacheControllerFactory = $container->get(CacheControllerFactoryInterface::class);

				return new Cache($dispatcher, (array) $plugin, $documentFactory, $cacheControllerFactory);
			}
		);
	}
};
