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
	 * The options
	 *
	 * @var  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $options;

	/**
	 * Categories constructor. The options are for the default section only.
	 *
	 * @param   array  $options  The options
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(array $options)
	{
		$this->options = $options;
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
				return new \Joomla\CMS\Categories\CategoriesFactory(['' => $this->options]);
			}
		);
	}
}
