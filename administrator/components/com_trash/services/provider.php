<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_trash
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The trash service provider.
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
		$container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\Trash'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\Trash'));

		$container->set(
			ComponentInterface::class,
			function (Container $container)
			{
				$component = new MVCComponent($container->get(ComponentDispatcherFactoryInterface::class));

				$component->setMVCFactory($container->get(MVCFactoryInterface::class));

				return $component;
			}
		);
	}
};
