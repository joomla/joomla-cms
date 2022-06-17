<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 * @copyright (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Guidedtours\Administrator\Extension\GuidedtoursComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The Guidedtours service provider.
 *
 * @since __DEPLOY_VERSION__
 */
return new class implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container $container The DI container.
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function register(Container $container)
	{
		$container->registerServiceProvider(new CategoryFactory('\\Joomla\\Component\\Guidedtours'));
		$container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\Guidedtours'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\Guidedtours'));
		$container->registerServiceProvider(new RouterFactory('\\Joomla\\Component\\Guidedtours'));
		$container->set(
			ComponentInterface::class,
			function (Container $container) {
				$component = new GuidedtoursComponent($container->get(ComponentDispatcherFactoryInterface::class));
				$component->setRegistry($container->get(Registry::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));
				$component->setRouterFactory($container->get(RouterFactoryInterface::class));

				return $component;
			}
		);
	}
};
