<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

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
					$app->setLogger(Log::createDelegatedLogger());
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
					$app->setLogger(Log::createDelegatedLogger());
					$app->setSession($container->get('Joomla\Session\SessionInterface'));

					return $app;
				},
				true
			);
	}
}
