<?php
/**
 * Returns the service provider class for com_scheduler.
 *
 * @package    Joomla.Administrator
 * @subpackage com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Cronjobs\Administrator\Extension\CronjobsComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The cronjobs service provider.
 * Returns an instance of the Component's Service Provider Interface
 * used to register the components initializers into it's DI container
 * created by Joomla.
 *
 * @since  __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
	 */
	public function register(Container $container)
	{
		/*
		* Register the MVCFactory and ComponentDispatcherFactory providers to map
		* 'MVCFactoryInterface' and 'ComponentDispatcherFactoryInterface' to their
		* initializers and register them with the component's DI container.
		*/
		$container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\Cronjobs'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\Cronjobs'));

		$container->set(
			ComponentInterface::class,
			function (Container $container) {
				$component = new CronjobsComponent($container->get(ComponentDispatcherFactoryInterface::class));

				$component->setRegistry($container->get(Registry::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));

				return $component;
			}
		);
	}
};
