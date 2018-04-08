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
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Extension\Service\Provider\Component;
use Joomla\CMS\Extension\Service\Provider\DispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactoryFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\Component\Content\Administrator\Helper\AssociationsHelper;
use Joomla\Component\Content\Administrator\Service\HTML\AdministratorService;
use Joomla\Component\Content\Administrator\Service\HTML\Icon;
use Joomla\Component\Content\Site\Service\Category;
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
		/**
		 * @var Registry $registry
		 */
		$registry = $container->get(Registry::class);
		$registry->register('contentadministrator', new AdministratorService);
		$registry->register('contenticon', new Icon($container->get(SiteApplication::class)));

		// The layout joomla.content.icons does need a general icon service
		$registry->register('icon', $registry->getService('contenticon'));

		$container->set(Categories::class, ['' => new Category]);
		$container->set(AssociationExtensionInterface::class, new AssociationsHelper);

		$container->registerServiceProvider(new MVCFactoryFactory('\\Joomla\\Component\\Content'));
		$container->registerServiceProvider(new DispatcherFactory('\\Joomla\\Component\\Content'));
		$container->registerServiceProvider(new Component);
	}
};
