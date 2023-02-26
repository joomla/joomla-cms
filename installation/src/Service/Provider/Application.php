<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Service
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Service\Provider;

use Joomla\CMS\Error\Renderer\JsonRenderer;
use Joomla\CMS\Factory;
use Joomla\CMS\Installation\Application\InstallationApplication;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

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

                $app->setDispatcher($container->get('Joomla\Event\DispatcherInterface'));
                $app->setLogger($container->get(LoggerInterface::class));
                $app->setSession($container->get('Joomla\Session\SessionInterface'));

                return $app;
            },
            true
        );

        // Inject a custom JSON error renderer
        $container->share(
            JsonRenderer::class,
            function (Container $container) {
                return new \Joomla\CMS\Installation\Error\Renderer\JsonRenderer();
            }
        );
    }
}
