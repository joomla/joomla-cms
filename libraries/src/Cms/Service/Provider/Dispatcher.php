<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\Dispatcher as EventDispatcher;

/**
 * Service provider for the application's dispatcher dependency
 *
 * @since  4.0
 */
class Dispatcher implements ServiceProviderInterface
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
		$container->alias('dispatcher', 'Joomla\Event\DispatcherInterface')
			->alias('Joomla\Event\Dispatcher', 'Joomla\Event\DispatcherInterface')
			->share(
				'Joomla\Event\DispatcherInterface',
				function (Container $container)
				{
					return new EventDispatcher;
				},
				true
			);
	}
}
