<?php
/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Console\CheckJoomlaUpdatesCommand;
use Joomla\CMS\Console\ExtensionDiscoverCommand;
use Joomla\CMS\Console\ExtensionDiscoverInstallCommand;
use Joomla\CMS\Console\ExtensionDiscoverListCommand;
use Joomla\CMS\Console\ExtensionInstallCommand;
use Joomla\CMS\Console\ExtensionRemoveCommand;
use Joomla\CMS\Console\ExtensionsListCommand;
use Joomla\CMS\Console\FinderIndexCommand;
use Joomla\CMS\Console\GetConfigurationCommand;
use Joomla\CMS\Console\SessionGcCommand;
use Joomla\CMS\Console\SessionMetadataGcCommand;
use Joomla\CMS\Console\SetConfigurationCommand;
use Joomla\CMS\Console\SiteDownCommand;
use Joomla\CMS\Console\SiteUpCommand;
use Joomla\CMS\Console\TasksListCommand;
use Joomla\CMS\Console\TasksRunCommand;
use Joomla\CMS\Console\TasksStateCommand;
use Joomla\CMS\Console\UpdateCoreCommand;
use Joomla\CMS\Session\MetadataManager;
use Joomla\Database\Command\ExportCommand;
use Joomla\Database\Command\ImportCommand;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

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
			function (Container $container)
			{
				/*
				 * The command will need the same session handler that web apps use to run correctly,
				 * since this is based on an option we need to inject the container
				 */
				$command = new SessionGcCommand;
				$command->setContainer($container);

				return $command;
			},
			true
		);

		$container->share(
			SessionMetadataGcCommand::class,
			function (Container $container)
			{
				return new SessionMetadataGcCommand($container->get('session'), $container->get(MetadataManager::class));
			},
			true
		);

		$container->share(
			ExportCommand::class,
			function (Container $container)
			{
				return new ExportCommand($container->get('db'));
			},
			true
		);

		$container->share(
			ImportCommand::class,
			function (Container $container)
			{
				return new ImportCommand($container->get('db'));
			},
			true
		);

		$container->share(
			SiteDownCommand::class,
			function (Container $container)
			{
				return new SiteDownCommand;
			},
			true
		);

		$container->share(
			SiteUpCommand::class,
			function (Container $container)
			{
				return new SiteUpCommand;
			},
			true
		);

		$container->share(
			SetConfigurationCommand::class,
			function (Container $container)
			{
				return new SetConfigurationCommand;
			},
			true
		);

		$container->share(
			GetConfigurationCommand::class,
			function (Container $container)
			{
				return new GetConfigurationCommand;
			},
			true
		);

		$container->share(
			ExtensionsListCommand::class,
			function (Container $container)
			{
				return new ExtensionsListCommand($container->get('db'));
			},
			true
		);

		$container->share(
			CheckJoomlaUpdatesCommand::class,
			function (Container $container)
			{
				return new CheckJoomlaUpdatesCommand;
			},
			true
		);

		$container->share(
			ExtensionRemoveCommand::class,
			function (Container $container)
			{
				return new ExtensionRemoveCommand;
			},
			true
		);

		$container->share(
			ExtensionInstallCommand::class,
			function (Container $container)
			{
				return new ExtensionInstallCommand;
			},
			true
		);

		$container->share(
			ExtensionDiscoverCommand::class,
			function (Container $container)
			{
				return new ExtensionDiscoverCommand;
			},
			true
		);

		$container->share(
			ExtensionDiscoverInstallCommand::class,
			function (Container $container)
			{
				return new ExtensionDiscoverInstallCommand($container->get('db'));
			},
			true
		);

		$container->share(
			ExtensionDiscoverListCommand::class,
			function (Container $container)
			{
				return new ExtensionDiscoverListCommand($container->get('db'));
			},
			true
		);

		$container->share(
			UpdateCoreCommand::class,
			function (Container $container)
			{
				return new UpdateCoreCommand($container->get('db'));
			},
			true
		);

		$container->share(
			FinderIndexCommand::class,
			function (Container $container)
			{
				return new FinderIndexCommand($container->get('db'));
			},
			true
		);

		$container->share(
			TasksListCommand::class,
			function (Container $container)
			{
				return new TasksListCommand;
			},
			true
		);

		$container->share(
			TasksRunCommand::class,
			function (Container $container)
			{
				return new TasksRunCommand;
			}
		);

		$container->share(
			TasksStateCommand::class,
			function (Container $container)
			{
				return new TasksStateCommand;
			}
		);
	}
}
