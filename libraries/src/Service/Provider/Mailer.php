<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Mail\PHPMailer\MailerFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the application's mailer dependency
 *
 * @since  4.0
 */
class Mailer implements ServiceProviderInterface
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
		$container->alias('mailer.factory', MailerFactoryInterface::class)
			->alias(MailerFactory::class, MailerFactoryInterface::class)
			->share(
				MailerFactoryInterface::class,
				function (Container $container)
				{
					$factory = new MailerFactory;
					$factory->setContainer($container);

					return $factory;
				},
				true
			);
	}
}
