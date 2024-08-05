<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Input as CMSInput;
use Joomla\CMS\Installation\Application\InstallationApplication;
use Joomla\CMS\Session\EventListener\MetadataManagerListener;
use Joomla\CMS\Session\MetadataManager;
use Joomla\CMS\Session\SessionFactory;
use Joomla\CMS\Session\SessionManager;
use Joomla\CMS\Session\Storage\JoomlaStorage;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\Exception\DependencyResolutionException;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\LazyServiceEventListener;
use Joomla\Registry\Registry;
use Joomla\Session\HandlerInterface;
use Joomla\Session\SessionEvents;
use Joomla\Session\Storage\RuntimeStorage;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for the application's session dependency
 *
 * @since  4.0.0
 */
class Session implements ServiceProviderInterface
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
            'session.web.administrator',
            function (Container $container) {
                /** @var Registry $config */
                $config = $container->get('config');
                $input  = $container->get(CMSInput::class);

                // Generate a session name.
                $name = $this->generateSessionName($config, AdministratorApplication::class);

                // Calculate the session lifetime.
                $lifetime = $config->get('lifetime') ? $config->get('lifetime') * 60 : 900;

                // Initialize the options for the Session object.
                $options = [
                    'name'   => $name,
                    'expire' => $lifetime,
                ];

                if ($config->get('force_ssl') >= 1) {
                    $options['force_ssl'] = true;
                }

                $handler = $container->get('session.factory')->createSessionHandler($options);

                if (!$container->has('session.handler')) {
                    $this->registerSessionHandlerAsService($container, $handler);
                }

                $options['cookie_domain'] = $config->get('cookie_domain', '');
                $options['cookie_path']   = $config->get('cookie_path', '/');

                return new \Joomla\CMS\Session\Session(
                    new JoomlaStorage($input, $handler, $options),
                    $container->get(DispatcherInterface::class),
                    $options
                );
            },
            true
        );

        $container->share(
            'session.web.installation',
            function (Container $container) {
                /** @var Registry $config */
                $config = $container->get('config');
                $input  = $container->get(CMSInput::class);

                /**
                 * Session handler for the session is always filesystem so it doesn't flip to the database after
                 * configuration.php has been written to
                 */
                $config->set('session_handler', 'filesystem');

                /**
                 * Generate a session name - unlike all the other apps we don't have either a secret or a session name
                 * (that's not the app name) until we complete installation which then leads to us dropping things like
                 * language preferences after installation as the app refreshes.
                 */
                $name = md5(serialize(JPATH_ROOT . InstallationApplication::class));

                // Calculate the session lifetime.
                $lifetime = $config->get('lifetime') ? $config->get('lifetime') * 60 : 900;

                // Initialize the options for the Session object.
                $options = [
                    'name'   => $name,
                    'expire' => $lifetime,
                ];

                $handler = $container->get('session.factory')->createSessionHandler($options);

                if (!$container->has('session.handler')) {
                    $this->registerSessionHandlerAsService($container, $handler);
                }

                $options['cookie_domain'] = $config->get('cookie_domain', '');
                $options['cookie_path']   = $config->get('cookie_path', '/');

                return new \Joomla\CMS\Session\Session(
                    new JoomlaStorage($input, $handler, $options),
                    $container->get(DispatcherInterface::class),
                    $options
                );
            },
            true
        );

        $container->share(
            'session.web.site',
            function (Container $container) {
                /** @var Registry $config */
                $config = $container->get('config');
                $input  = $container->get(CMSInput::class);

                // Generate a session name.
                $name = $this->generateSessionName($config, SiteApplication::class);

                // Calculate the session lifetime.
                $lifetime = $config->get('lifetime') ? $config->get('lifetime') * 60 : 900;

                // Initialize the options for the Session object.
                $options = [
                    'name'   => $name,
                    'expire' => $lifetime,
                ];

                if ($config->get('force_ssl') == 2) {
                    $options['force_ssl'] = true;
                }

                $handler = $container->get('session.factory')->createSessionHandler($options);

                if (!$container->has('session.handler')) {
                    $this->registerSessionHandlerAsService($container, $handler);
                }

                $options['cookie_domain'] = $config->get('cookie_domain', '');
                $options['cookie_path']   = $config->get('cookie_path', '/');

                return new \Joomla\CMS\Session\Session(
                    new JoomlaStorage($input, $handler, $options),
                    $container->get(DispatcherInterface::class),
                    $options
                );
            },
            true
        );

        $container->share(
            'session.cli',
            function (Container $container) {
                /** @var Registry $config */
                $config = $container->get('config');

                // Generate a session name.
                $name = $this->generateSessionName($config, ConsoleApplication::class);

                // Calculate the session lifetime.
                $lifetime = $config->get('lifetime') ? $config->get('lifetime') * 60 : 900;

                // Initialize the options for the Session object.
                $options = [
                    'name'   => $name,
                    'expire' => $lifetime,
                ];

                // Unlike the web apps, we will only toggle the force SSL setting based on it being enabled and not based on client
                if ($config->get('force_ssl') >= 1) {
                    $options['force_ssl'] = true;
                }

                $handler = $container->get('session.factory')->createSessionHandler($options);

                if (!$container->has('session.handler')) {
                    $this->registerSessionHandlerAsService($container, $handler);
                }

                return new \Joomla\CMS\Session\Session(
                    new RuntimeStorage(),
                    $container->get(DispatcherInterface::class),
                    $options
                );
            },
            true
        );

        $container->alias(SessionFactory::class, 'session.factory')
            ->share(
                'session.factory',
                function (Container $container) {
                    $factory = new SessionFactory();
                    $factory->setContainer($container);

                    return $factory;
                },
                true
            );

        $container->alias(SessionManager::class, 'session.manager')
            ->share(
                'session.manager',
                function (Container $container) {
                    if (!$container->has('session.handler')) {
                        throw new DependencyResolutionException(
                            'The "session.handler" service has not been created, make sure you have created the "session" service first.'
                        );
                    }

                    return new SessionManager($container->get('session.handler'));
                },
                true
            );

        $container->alias(MetadataManager::class, 'session.metadata_manager')
            ->share(
                'session.metadata_manager',
                function (Container $container) {
                    /*
                     * Normally we should inject the application as a dependency via $container->get() however there is not
                     * a 'app' or CMSApplicationInterface::class key for the primary application of the request so we need to
                     * rely on the application having been injected to the global Factory otherwise we cannot build the service
                     */
                    if (!Factory::$application) {
                        throw new DependencyResolutionException(
                            sprintf(
                                'Creating the "session.metadata_manager" service requires %s::$application be initialised.',
                                Factory::class
                            )
                        );
                    }

                    return new MetadataManager(Factory::$application, $container->get(DatabaseInterface::class));
                },
                true
            );

        $container->alias(MetadataManagerListener::class, 'session.event_listener.metadata_manager')
            ->share(
                'session.event_listener.metadata_manager',
                function (Container $container) {
                    return new MetadataManagerListener($container->get(MetadataManager::class), $container->get('config'));
                },
                true
            );

        $listener = new LazyServiceEventListener($container, 'session.event_listener.metadata_manager', 'onAfterSessionStart');

        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $container->get(DispatcherInterface::class);
        $dispatcher->addListener(SessionEvents::START, $listener);
    }

    /**
     * This is a straight up clone of \Joomla\CMS\Application\ApplicationHelper::getHash but instead getting the secret
     * directly from the DIC rather than via the application - as we haven't actually set the app up yet in Factory
     *
     * @param   Registry  $config       The application configuration.
     * @param   string    $seedDefault  The default seed for the secret if there isn't a session name configured
     *                                  globally. This is the relevant application's classname
     *
     * @return  string
     *
     * @since   4.0.0
     */
    private function generateSessionName(Registry $config, string $seedDefault): string
    {
        return md5($config->get('secret') . $config->get('session_name', $seedDefault));
    }

    /**
     * Registers the session handler as a service
     *
     * @param   Container                 $container       The container to register the service to.
     * @param   \SessionHandlerInterface  $sessionHandler  The session handler.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function registerSessionHandlerAsService(Container $container, \SessionHandlerInterface $sessionHandler): void
    {
        // Alias the session handler to the core SessionHandlerInterface for improved autowiring and discoverability
        $container->alias(\SessionHandlerInterface::class, 'session.handler')
            ->share(
                'session.handler',
                $sessionHandler,
                true
            );

        // If the session handler implements the extended interface, register an alias for that as well
        if ($sessionHandler instanceof HandlerInterface) {
            $container->alias(HandlerInterface::class, 'session.handler');
        }
    }
}
