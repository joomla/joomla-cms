<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\DispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactoryFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
use Joomla\Component\Finder\Administrator\Extension\FinderComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The finder service provider.
 *
 * @since  4.0.0
 */
return new class implements ServiceProviderInterface
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
		$container->registerServiceProvider(new MVCFactoryFactory('\\Joomla\\Component\\Finder'));
		$container->registerServiceProvider(new DispatcherFactory('\\Joomla\\Component\\Finder'));
		$container->registerServiceProvider(new RouterFactory('\\Joomla\\Component\\Finder'));

		$container->set(
			ComponentInterface::class,
			function (Container $container)
			{
				$component = new FinderComponent($container->get(DispatcherFactoryInterface::class));
				$component->setMvcFactoryFactory($container->get(MVCFactoryFactoryInterface::class));
				$component->setRouterFactory($container->get(RouterFactoryInterface::class));
				$component->setRegistry($container->get(Registry::class));

				return $component;
			}
		);
	}
};
