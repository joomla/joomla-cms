<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Association\AssociationExtensionInterface;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelperInterface;
use Joomla\CMS\Dispatcher\DispatcherFactory;
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\Service\Provider\Component;
use Joomla\CMS\MVC\Factory\MVCFactoryFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
use Joomla\Component\Content\Administrator\Helper\AssociationsHelper;
use Joomla\Component\Content\Site\Service\Category;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The content service provider.
 *
 * @since  __DEPLOY_VERSION__
 */
return new class implements ServiceProviderInterface
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
		$container->set(Categories::class, ['' => new Category]);
		$container->set(AssociationExtensionInterface::class, new AssociationsHelper);

		$container->set(MVCFactoryFactoryInterface::class, new MVCFactoryFactory('\\Joomla\\Component\\Content'));
		$container->set(
			DispatcherFactoryInterface::class,
			new DispatcherFactory('\\Joomla\\Component\\Content', $container->get(MVCFactoryFactoryInterface::class))
		);
		$container->set(ComponentHelperInterface::class, new ContentHelper);
		$container->registerServiceProvider(new Component);
	}
};
