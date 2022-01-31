<?php
/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Log\Log;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * Service provider for the application's PSR-3 logger dependency
 *
 * @since  4.0.0
 */
class Logger implements ServiceProviderInterface
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
		$container->alias('logger', LoggerInterface::class)
			->share(
				LoggerInterface::class,
				function (Container $container)
				{
					return Log::createDelegatedLogger();
				},
				true
			);
	}
}
