<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Service
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Service\Provider;

use Joomla\Event\DispatcherInterface;
use Joomla\Session\SessionInterface;
use Joomla\CMS\Error\Renderer\JsonRenderer;
use Joomla\CMS\Factory;
use Joomla\CMS\Installation\Application\InstallationApplication;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * Application service provider
 *
 * @since  4.0.0
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
     * @since   4.0.0
     */
    public function register(Container $container)
    {
        $container->share(
            InstallationApplication::class,
            function (Container $container) {
                $app = new InstallationApplication(null, $container->get('config'), null, $container);

                // The session service provider needs Factory::$application, set it if still null
                if (Factory::$application === null) {
                    Factory::$application = $app;
                }

                $app->setDispatcher($container->get(DispatcherInterface::class));
                $app->setLogger($container->get(LoggerInterface::class));
                $app->setSession($container->get(SessionInterface::class));

                return $app;
            },
            true
        );

        // Inject a custom JSON error renderer
        $container->share(
            JsonRenderer::class,
            fn(Container $container) => new \Joomla\CMS\Installation\Error\Renderer\JsonRenderer()
        );
    }
}
