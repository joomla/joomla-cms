<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\CMS\Console\CheckJoomlaUpdatesCommand;
use Joomla\CMS\Console\CoreUpdateChannelCommand;
use Joomla\CMS\Console\ExtensionDiscoverCommand;
use Joomla\CMS\Console\ExtensionDiscoverInstallCommand;
use Joomla\CMS\Console\ExtensionDiscoverListCommand;
use Joomla\CMS\Console\ExtensionInstallCommand;
use Joomla\CMS\Console\ExtensionRemoveCommand;
use Joomla\CMS\Console\ExtensionsListCommand;
use Joomla\CMS\Console\FinderIndexCommand;
use Joomla\CMS\Console\GetConfigurationCommand;
use Joomla\CMS\Console\MaintenanceDatabaseCommand;
use Joomla\CMS\Console\SessionGcCommand;
use Joomla\CMS\Console\SessionMetadataGcCommand;
use Joomla\CMS\Console\SetConfigurationCommand;
use Joomla\CMS\Console\SiteDownCommand;
use Joomla\CMS\Console\SiteUpCommand;
use Joomla\CMS\Console\TasksListCommand;
use Joomla\CMS\Console\TasksRunCommand;
use Joomla\CMS\Console\TasksStateCommand;
use Joomla\CMS\Console\UpdateCoreCommand;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Session\MetadataManager;
use Joomla\Database\Command\ExportCommand;
use Joomla\Database\Command\ImportCommand;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for the application's console services
 *
 * @since  4.0.0
 */
class Console implements ServiceProviderInterface
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
            SessionGcCommand::class,
            function (Container $container) {
                /*
                 * The command will need the same session handler that web apps use to run correctly,
                 * since this is based on an option we need to inject the container
                 */
                $command = new SessionGcCommand();
                $command->setContainer($container);

                return $command;
            },
            true
        );

        $container->share(
            SessionMetadataGcCommand::class,
            function (Container $container) {
                return new SessionMetadataGcCommand($container->get('session'), $container->get(MetadataManager::class));
            },
            true
        );

        $container->share(
            ExportCommand::class,
            function (Container $container) {
                return new ExportCommand($container->get(DatabaseInterface::class));
            },
            true
        );

        $container->share(
            ImportCommand::class,
            function (Container $container) {
                return new ImportCommand($container->get(DatabaseInterface::class));
            },
            true
        );

        $container->share(
            SiteDownCommand::class,
            function (Container $container) {
                return new SiteDownCommand();
            },
            true
        );

        $container->share(
            SiteUpCommand::class,
            function (Container $container) {
                return new SiteUpCommand();
            },
            true
        );

        $container->share(
            SetConfigurationCommand::class,
            function (Container $container) {
                return new SetConfigurationCommand();
            },
            true
        );

        $container->share(
            GetConfigurationCommand::class,
            function (Container $container) {
                return new GetConfigurationCommand();
            },
            true
        );

        $container->share(
            ExtensionsListCommand::class,
            function (Container $container) {
                return new ExtensionsListCommand($container->get(DatabaseInterface::class));
            },
            true
        );

        $container->share(
            CheckJoomlaUpdatesCommand::class,
            function (Container $container) {
                return new CheckJoomlaUpdatesCommand();
            },
            true
        );

        $container->share(
            ExtensionRemoveCommand::class,
            function (Container $container) {
                return new ExtensionRemoveCommand($container->get(DatabaseInterface::class));
            },
            true
        );

        $container->share(
            ExtensionInstallCommand::class,
            function (Container $container) {
                return new ExtensionInstallCommand();
            },
            true
        );

        $container->share(
            ExtensionDiscoverCommand::class,
            function (Container $container) {
                return new ExtensionDiscoverCommand();
            },
            true
        );

        $container->share(
            ExtensionDiscoverInstallCommand::class,
            function (Container $container) {
                return new ExtensionDiscoverInstallCommand($container->get(DatabaseInterface::class));
            },
            true
        );

        $container->share(
            ExtensionDiscoverListCommand::class,
            function (Container $container) {
                return new ExtensionDiscoverListCommand($container->get(DatabaseInterface::class));
            },
            true
        );

        $container->share(
            UpdateCoreCommand::class,
            function (Container $container) {
                return new UpdateCoreCommand($container->get(DatabaseInterface::class));
            },
            true
        );

        $container->share(
            FinderIndexCommand::class,
            function (Container $container) {
                $command = new FinderIndexCommand($container->get(DatabaseInterface::class));
                $command->setLanguage($container->get(LanguageFactoryInterface::class)->createLanguage(
                    $container->get('config')->get('language'),
                    $container->get('config')->get('debug_lang')
                ));

                return $command;
            },
            true
        );

        $container->share(
            TasksListCommand::class,
            function (Container $container) {
                return new TasksListCommand();
            },
            true
        );

        $container->share(
            TasksRunCommand::class,
            function (Container $container) {
                return new TasksRunCommand();
            }
        );

        $container->share(
            TasksStateCommand::class,
            function (Container $container) {
                return new TasksStateCommand();
            }
        );

        $container->share(
            MaintenanceDatabaseCommand::class,
            function (Container $container) {
                return new MaintenanceDatabaseCommand();
            },
            true
        );

        $container->share(
            CoreUpdateChannelCommand::class,
            function (Container $container) {
                return new CoreUpdateChannelCommand($container->get(DatabaseInterface::class));
            }
        );
    }
}
