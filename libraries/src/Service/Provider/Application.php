<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\Console\Application as BaseConsoleApplication;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Session\Session;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Session\Storage\RuntimeStorage;
use Psr\Log\LoggerInterface;

/**
 * Application service provider
 *
 * @since  4.0
 */
class Application implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function register(Container $container)
	{
		$container->alias(AdministratorApplication::class, 'JApplicationAdministrator')
			->share(
				'JApplicationAdministrator',
				function (Container $container)
				{
					$app = new AdministratorApplication(null, null, null, $container);

					// The session service provider needs Factory::$application, set it if still null
					if (Factory::$application === null)
					{
						Factory::$application = $app;
					}

					$app->setDispatcher($container->get('Joomla\Event\DispatcherInterface'));
					$app->setLogger($container->get(LoggerInterface::class));
					$app->setSession($container->get('Joomla\Session\SessionInterface'));

					return $app;
				},
				true
			);

		$container->alias(SiteApplication::class, 'JApplicationSite')
			->share(
				'JApplicationSite',
				function (Container $container)
				{
					$app = new SiteApplication(null, null, null, $container);

					// The session service provider needs Factory::$application, set it if still null
					if (Factory::$application === null)
					{
						Factory::$application = $app;
					}

					$app->setDispatcher($container->get('Joomla\Event\DispatcherInterface'));
					$app->setLogger($container->get(LoggerInterface::class));
					$app->setSession($container->get('Joomla\Session\SessionInterface'));

					return $app;
				},
				true
			);

		$container->alias(ConsoleApplication::class, BaseConsoleApplication::class)
			->share(
				BaseConsoleApplication::class,
				function (Container $container)
				{
					$app = new ConsoleApplication;

					$dispatcher = $container->get('Joomla\Event\DispatcherInterface');

					$session = new Session(new RuntimeStorage);
					$session->setDispatcher($dispatcher);

					$app->setContainer($container);
					$app->setDispatcher($dispatcher);
					$app->setLogger($container->get(LoggerInterface::class));
					$app->setSession($session);

					return $app;
				},
				true
			);
	}
}
