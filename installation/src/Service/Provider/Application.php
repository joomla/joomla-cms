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
use Joomla\CMS\Installation\Application\CliInstallationApplication;
use Joomla\CMS\Installation\Application\InstallationApplication;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\Priority;
use Joomla\Input\Input as CMSInput;
use Joomla\Session\SessionEvents;
use Joomla\Session\SessionInterface;
use Psr\Log\LoggerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
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
                $app = new InstallationApplication($container->get(CMSInput::class), $container->get('config'), null, $container);
                $app->setDispatcher($container->get('Joomla\Event\DispatcherInterface'));
                $app->setLogger($container->get(LoggerInterface::class));
                $app->setSession($container->get(SessionInterface::class));

                // Ensure that session purging is configured now we have a dispatcher
                $app->getDispatcher()->addListener(SessionEvents::START, [$app, 'afterSessionStart'], Priority::HIGH);

                return $app;
            },
            true
        );

        $container->share(
            CliInstallationApplication::class,
            function (Container $container) {
                $lang = $container->get(LanguageFactoryInterface::class)->createLanguage('en-GB', false);

                $app = new CliInstallationApplication(null, null, $container->get('config'), $lang);

                $app->setDispatcher($container->get('Joomla\Event\DispatcherInterface'));
                $app->setLogger($container->get(LoggerInterface::class));
                $app->setSession($container->get(SessionInterface::class));

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
