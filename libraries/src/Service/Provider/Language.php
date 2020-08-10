<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\CachingLanguageFactory;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the language dependency
 *
 * @since  4.0
 */
class Language implements ServiceProviderInterface
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
		$container->alias('language.factory', LanguageFactoryInterface::class)
			->alias(CachingLanguageFactory::class, LanguageFactoryInterface::class)
			->share(
				LanguageFactoryInterface::class,
				function (Container $container)
				{
					return new CachingLanguageFactory;
				},
				true
			);
	}
}
