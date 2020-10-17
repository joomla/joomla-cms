<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Installation\Application\InstallationApplication;
use Joomla\CMS\Session\EventListener\MetadataManagerListener;
use Joomla\CMS\Session\MetadataManager;
use Joomla\CMS\Session\SessionFactory;
use Joomla\CMS\Session\Storage\JoomlaStorage;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\Exception\DependencyResolutionException;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\LazyServiceEventListener;
use Joomla\Event\Priority;
use Joomla\Registry\Registry;
use Joomla\Session\SessionEvents;
use Joomla\Session\SessionInterface;
use Joomla\Session\Storage\RuntimeStorage;
use Joomla\Session\StorageInterface;
use Joomla\Session\Validator\AddressValidator;
use Joomla\Session\Validator\ForwardedValidator;

/**
 * Service provider for the application's session dependency
 *
 * @since  4.0
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
	 * @since   4.0
	 */
	public function register(Container $container)
	{
		$container->share(
			'session.web.administrator',
			function (Container $container)
			{
				/** @var Registry $config */
				$config = $container->get('config');
				$app    = Factory::getApplication();

				// Generate a session name.
				$name = ApplicationHelper::getHash($config->get('session_name', AdministratorApplication::class));

				// Calculate the session lifetime.
				$lifetime = $config->get('lifetime') ? $config->get('lifetime') * 60 : 900;

				// Initialize the options for the Session object.
				$options = [
					'name'   => $name,
					'expire' => $lifetime,
				];

				if ($config->get('force_ssl') >= 1)
				{
					$options['force_ssl'] = true;
				}

				return $this->buildSession(
					new JoomlaStorage($app->input, $container->get('session.factory')->createSessionHandler($options)),
					$app,
					$container->get(DispatcherInterface::class),
					$options
				);
			},
			true
		);

		$container->share(
			'session.web.installation',
			function (Container $container)
			{
				/** @var Registry $config */
				$config = $container->get('config');
				$app    = Factory::getApplication();

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

				return $this->buildSession(
					new JoomlaStorage($app->input, $container->get('session.factory')->createSessionHandler($options)),
					$app,
					$container->get(DispatcherInterface::class),
					$options
				);
			},
			true
		);

		$container->share(
			'session.web.site',
			function (Container $container)
			{
				/** @var Registry $config */
				$config = $container->get('config');
				$app    = Factory::getApplication();

				// Generate a session name.
				$name = ApplicationHelper::getHash($config->get('session_name', SiteApplication::class));

				// Calculate the session lifetime.
				$lifetime = $config->get('lifetime') ? $config->get('lifetime') * 60 : 900;

				// Initialize the options for the Session object.
				$options = [
					'name'   => $name,
					'expire' => $lifetime,
				];

				if ($config->get('force_ssl') == 2)
				{
					$options['force_ssl'] = true;
				}

				return $this->buildSession(
					new JoomlaStorage($app->input, $container->get('session.factory')->createSessionHandler($options)),
					$app,
					$container->get(DispatcherInterface::class),
					$options
				);
			},
			true
		);

		$container->share(
			'session.cli',
			function (Container $container)
			{
				/** @var Registry $config */
				$config = $container->get('config');
				$app    = Factory::getApplication();

				// Generate a session name.
				$name = ApplicationHelper::getHash($config->get('session_name', ConsoleApplication::class));

				// Calculate the session lifetime.
				$lifetime = $config->get('lifetime') ? $config->get('lifetime') * 60 : 900;

				// Initialize the options for the Session object.
				$options = [
					'name'   => $name,
					'expire' => $lifetime,
				];

				// Unlike the web apps, we will only toggle the force SSL setting based on it being enabled and not based on client
				if ($config->get('force_ssl') >= 1)
				{
					$options['force_ssl'] = true;
				}

				return $this->buildSession(new RuntimeStorage, $app, $container->get(DispatcherInterface::class), $options);
			},
			true
		);

		$container->alias(SessionFactory::class, 'session.factory')
			->share(
				'session.factory',
				function (Container $container)
				{
					$factory = new SessionFactory;
					$factory->setContainer($container);

					return $factory;
				},
				true
			);

		$container->alias(MetadataManager::class, 'session.metadata_manager')
			->share(
				'session.metadata_manager',
				function (Container $container)
				{
					/*
					 * Normally we should inject the application as a dependency via $container->get() however there is not
					 * a 'app' or CMSApplicationInterface::class key for the primary application of the request so we need to
					 * rely on the application having been injected to the global Factory otherwise we cannot build the service
					 */
					if (!Factory::$application)
					{
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
				function (Container $container)
				{
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
	 * Build the root session service
	 *
	 * @param   StorageInterface         $storage     The session storage engine.
	 * @param   CMSApplicationInterface  $app         The application instance.
	 * @param   DispatcherInterface      $dispatcher  The event dispatcher.
	 * @param   array                    $options     The configured session options.
	 *
	 * @return  SessionInterface
	 *
	 * @since   4.0
	 */
	private function buildSession(StorageInterface $storage, CMSApplicationInterface $app, DispatcherInterface $dispatcher,
		array $options
	): SessionInterface
	{
		$input = $app->input;

		if (method_exists($app, 'afterSessionStart'))
		{
			$dispatcher->addListener(SessionEvents::START, [$app, 'afterSessionStart'], Priority::HIGH);
		}

		$session = new \Joomla\CMS\Session\Session($storage, $dispatcher, $options);
		$session->addValidator(new AddressValidator($input, $session));
		$session->addValidator(new ForwardedValidator($input, $session));

		return $session;
	}
}
