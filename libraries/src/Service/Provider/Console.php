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

use Joomla\CMS\Console\SessionGcCommand;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the application's console services
 *
 * @since  4.0
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
	 * @since   4.0
	 */
	public function register(Container $container)
	{
		$container->share(
			SessionGcCommand::class,
			function (Container $container)
			{
				return new SessionGcCommand($container->get('session'));
			},
			true
		);
	}
}
