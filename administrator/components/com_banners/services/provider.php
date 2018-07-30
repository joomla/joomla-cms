<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\DispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactoryFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
use Joomla\Component\Banners\Administrator\Extension\BannersComponent;
use Joomla\Component\Banners\Site\Service\Category;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The banners service provider.
 *
 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function register(Container $container)
	{
		$container->set(Categories::class, ['' => new Category]);

		$container->registerServiceProvider(new MVCFactoryFactory('\\Joomla\\Component\\Banners'));
		$container->registerServiceProvider(new DispatcherFactory('\\Joomla\\Component\\Banners'));

		$container->set(
			ComponentInterface::class,
			function (Container $container)
			{
				$component = new BannersComponent($container->get(DispatcherFactoryInterface::class));

				$component->setRegistry($container->get(Registry::class));
				$component->setMvcFactoryFactory($container->get(MVCFactoryFactoryInterface::class));
				$component->setCategories($container->get(Categories::class));

				return $component;
			}
		);
	}
};
