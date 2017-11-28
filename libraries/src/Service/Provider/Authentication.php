<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\Authentication\Password\Argon2iHandler as BaseArgon2iHandler;
use Joomla\Authentication\Password\BCryptHandler as BaseBCryptHandler;
use Joomla\CMS\Authentication\Password\Argon2iHandler;
use Joomla\CMS\Authentication\Password\BCryptHandler;
use Joomla\CMS\Authentication\Password\ChainedHandler;
use Joomla\CMS\Authentication\Password\MD5Handler;
use Joomla\CMS\Authentication\Password\PHPassHandler;
use Joomla\CMS\Authentication\Password\SHA256Handler;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the authentication dependencies
 *
 * @since  4.0
 */
class Authentication implements ServiceProviderInterface
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
		$container->alias('password.handler.argon2i', Argon2iHandler::class)
			->alias(BaseArgon2iHandler::class, Argon2iHandler::class)
			->share(
				Argon2iHandler::class,
				function (Container $container)
				{
					return new Argon2iHandler;
				},
				true
			);

		$container->alias('password.handler.chained', ChainedHandler::class)
			->share(
				ChainedHandler::class,
				function (Container $container)
				{
					$handler = new ChainedHandler;

					// Load the chain with supported core handlers
					$handler->addHandler($container->get(BCryptHandler::class));

					if (Argon2iHandler::isSupported())
					{
						$handler->addHandler($container->get(Argon2iHandler::class));
					}

					$handler->addHandler($container->get(PHPassHandler::class));
					$handler->addHandler($container->get(SHA256Handler::class));
					$handler->addHandler($container->get(MD5Handler::class));

					return $handler;
				},
				true
			);

		// The Joomla default is BCrypt so alias this service
		$container->alias('password.handler.default', BCryptHandler::class)
			->alias(BaseBCryptHandler::class, BCryptHandler::class)
			->alias('password.handler.bcrypt', BCryptHandler::class)
			->share(
				BCryptHandler::class,
				function (Container $container)
				{
					return new BCryptHandler;
				},
				true
			);

		$container->alias('password.handler.md5', MD5Handler::class)
			->share(
				MD5Handler::class,
				function (Container $container)
				{
					return new MD5Handler;
				},
				true
			);

		$container->alias('password.handler.phpass', PHPassHandler::class)
			->share(
				PHPassHandler::class,
				function (Container $container)
				{
					return new PHPassHandler;
				},
				true
			);

		$container->alias('password.handler.sha256', SHA256Handler::class)
			->share(
				SHA256Handler::class,
				function (Container $container)
				{
					return new SHA256Handler;
				},
				true
			);
	}
}
