<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Installation\Application\InstallationApplication;
use Joomla\CMS\Session\SessionFactory;
use Joomla\CMS\Session\Storage\JoomlaStorage;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
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

				// Generate a session name.
				$name = ApplicationHelper::getHash($config->get('session_name', InstallationApplication::class));

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
		array $options): SessionInterface
	{
		$input = $app->input;

		if (method_exists($app, 'afterSessionStart'))
		{
			$dispatcher->addListener(SessionEvents::START, [$app, 'afterSessionStart']);
		}

		$session = new \Joomla\CMS\Session\Session($storage, $dispatcher, $options);
		$session->addValidator(new AddressValidator($input, $session));
		$session->addValidator(new ForwardedValidator($input, $session));

		return $session;
	}
}
