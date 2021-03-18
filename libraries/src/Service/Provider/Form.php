<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormFactory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the form dependency
 *
 * @since  4.0
 */
class Form implements ServiceProviderInterface
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
		$container->alias('form.factory', FormFactoryInterface::class)
			->alias(FormFactory::class, FormFactoryInterface::class)
			->share(
				FormFactoryInterface::class,
				function (Container $container)
				{
					return new FormFactory;
				},
				true
			);
	}
}
