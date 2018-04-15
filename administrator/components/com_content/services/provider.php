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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\DispatcherFactory;
use Joomla\CMS\Dispatcher\DispatcherFactoryInterface;
use Joomla\CMS\Extension\Service\Provider\Component;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
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
	 * The extension name to use
	 *
	 * @type   string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $extension_name = 'com_content';

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

		$factory = new MVCFactoryFactory(ComponentHelper::getComponent($this->extension_name)->namespace);
		$factory->setFormFactory($container->get(\Joomla\CMS\Form\FormFactoryInterface::class));
		$container->set(MVCFactoryFactoryInterface::class, $factory);

		$container->set(
			DispatcherFactoryInterface::class,
			new DispatcherFactory(
				ComponentHelper::getComponent($this->extension_name)->namespace,
				$container->get(MVCFactoryFactoryInterface::class)
			)
		);
		$container->registerServiceProvider(new Component);
	}
};
