<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Error\Renderer\JsonRenderer;
use Joomla\CMS\Factory;
use Joomla\CMS\Installation\Application\InstallationApplication;
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
		$container->share(
			'InstallationApplicationWeb',
			function (Container $container)
			{
				$config = null;

				// Load the global configuration file if available
				if (file_exists(JPATH_CONFIGURATION . '/configuration.php'))
				{
					$config = Factory::getConfig();
				}

				$app = new InstallationApplication(null, $config, null, $container);

				// The session service provider needs JFactory::$application, set it if still null
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

		// Inject a custom JSON error renderer
		$container->share(
			JsonRenderer::class,
			function (Container $container)
			{
				return new \Joomla\CMS\Installation\Error\Renderer\JsonRenderer;
			}
		);
	}
}
