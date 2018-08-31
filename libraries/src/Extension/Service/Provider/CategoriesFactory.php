<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Categories\CategoriesFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the service categories.
 *
 * @since  __DEPLOY_VERSION__
 */
class CategoriesFactory implements ServiceProviderInterface
{
	/**
	 * The namespace to create the categories from.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	private $namespace;

	/**
	 * The namespace must be like:
	 * Joomla\Component\Content
	 *
	 * @param   string  $namespace  The namespace
	 *
	 * @since   4.0.0
	 */
	public function __construct($namespace)
	{
		$this->namespace = $namespace;
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
			CategoriesFactoryInterface::class,
			function (Container $container)
			{
				return new \Joomla\CMS\Categories\CategoriesFactory($this->namespace);
			}
		);
	}
}
