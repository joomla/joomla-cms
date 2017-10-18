<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\Authentication\Password\Argon2iHandler;
use Joomla\Authentication\Password\BCryptHandler;
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
			->share(
				Argon2iHandler::class,
				function (Container $container)
				{
					return new Argon2iHandler;
				},
				true
			);

		// The Joomla default is BCrypt so alias this service
		$container->alias('password.handler.default', BCryptHandler::class)
			->alias('password.handler.bcrypt', BCryptHandler::class)
			->share(
				BCryptHandler::class,
				function (Container $container)
				{
					return new BCryptHandler;
				},
				true
			);
	}
}
