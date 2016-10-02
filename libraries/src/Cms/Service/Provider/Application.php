<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Cms\Service\Provider;

defined('JPATH_PLATFORM') or die;

use JFactory;
use JLog;
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
		$container->share(
			'JApplicationAdministrator',
			function (Container $container)
			{
				$app = new \JApplicationAdministrator(null, null, null, $container);

				// The session service provider needs JFactory::$application, set it if still null
				if (JFactory::$application === null)
				{
					JFactory::$application = $app;
				}

				$app->setDispatcher($container->get('Joomla\Event\DispatcherInterface'));
				$app->setLogger(JLog::createDelegatedLogger());
				$app->setSession($container->get('Joomla\Session\SessionInterface'));

				return $app;
			},
			true
		);

		$container->share(
			'JApplicationSite',
			function (Container $container)
			{
				$app = new \JApplicationSite(null, null, null, $container);

				// The session service provider needs JFactory::$application, set it if still null
				if (JFactory::$application === null)
				{
					JFactory::$application = $app;
				}

				$app->setDispatcher($container->get('Joomla\Event\DispatcherInterface'));
				$app->setLogger(JLog::createDelegatedLogger());
				$app->setSession($container->get('Joomla\Session\SessionInterface'));

				return $app;
			},
			true
		);
	}
}
