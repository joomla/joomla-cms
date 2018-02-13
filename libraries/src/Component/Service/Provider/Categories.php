<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the components's categories dependency
 *
 * @since   __DEPLOY_VERSION__
 */
class Categories implements ServiceProviderInterface
{
	/**
	 * The options for the the categories.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Categories constructor.
	 *
	 * @param  array  $options  The options for the categories
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
		$container->share(
			'categories',
			function (Container $container)
			{
				return new \Joomla\CMS\Categories\Categories($this->options);
			},
			true
		);
	}
}
