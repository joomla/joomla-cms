<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\Component;
use Joomla\CMS\MVC\Factory\MVCFactoryFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
use Joomla\Component\Content\Site\Service\Category;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Content component loader.
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentComponentServiceProvider implements ServiceProviderInterface
{
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
		$container->set('categories', ['' => new Category]);
		$container->set(MVCFactoryFactoryInterface::class, new MVCFactoryFactory('\\Joomla\\Component\\Content'));
		$container->registerServiceProvider(new Component);
	}
}
