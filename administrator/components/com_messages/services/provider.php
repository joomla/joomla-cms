<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Messages\Administrator\Extension\MessagesComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The messages service provider.
 *
 * @since  4.0.0
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
	 * @since   4.0.0
	 */
	public function register(Container $container)
	{
		$container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\Messages'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\Messages'));

		$container->set(
			ComponentInterface::class,
			function (Container $container)
			{
				$component = new MessagesComponent($container->get(ComponentDispatcherFactoryInterface::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));
				$component->setRegistry($container->get(Registry::class));

				return $component;
			}
		);
	}
};
