<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\DispatcherFactory;
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\MVC\Factory\MVCFactoryFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The menu module service provider.
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
		$container->set(MVCFactoryFactoryInterface::class, new MVCFactoryFactory('\\Joomla\\Module\\Menu'));

		$container->set(
			DispatcherFactoryInterface::class,
			new DispatcherFactory('\\Joomla\\Module\\Menu', $container->get(MVCFactoryFactoryInterface::class))
		);
		$container->registerServiceProvider(new Module);
	}
};
