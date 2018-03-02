<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the component's dispatcher dependency.
 *
 * @since  __DEPLOY_VERSION__
 */
class Dispatcher implements ServiceProviderInterface
{
	/**
	 * The component namespace.
	 *
	 * @var string
	 */
	private $componentNamespace;

	/**
	 * Dispatcher constructor.
	 *
	 * @param   string  $componentNamespace  The component namespace
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($componentNamespace)
	{
		$this->componentNamespace = $componentNamespace;
	}

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function register(Container $container)
	{
		$container->set(
			'site.dispatcher',
			function (Container $container)
			{
				$className = '\\' . trim($this->componentNamespace, '\\') . '\\Site\\Dispatcher\\Dispatcher';

				$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);
				return new $className($app, $app->input);
			}
		);

		$container->set(
			'administrator.dispatcher',
			function (Container $container)
			{
				$className =  '\\' . trim($this->componentNamespace, '\\') . '\\Administrator\\Dispatcher\\Dispatcher';

				$app = $container->get(\Joomla\CMS\Application\AdministratorApplication::class);
				return new $className($app, $app->input);
			}
		);
	}
}
