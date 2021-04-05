<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApiApplication;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the application's API router dependency
 *
 * @since  4.0.0
 */
class ApiRouter implements ServiceProviderInterface
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
		$container->alias('ApiRouter', 'Joomla\CMS\Router\ApiRouter')
			->share(
				'Joomla\CMS\Router\ApiRouter',
				function (Container $container)
				{
					return new \Joomla\CMS\Router\ApiRouter($container->get(ApiApplication::class));
				},
				true
			);
	}
}
