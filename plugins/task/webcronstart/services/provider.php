<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Task.webcronstart
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Task\Webcronstart\Extension\Webcronstart;

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
		$container->set(
			PluginInterface::class,
			function (Container $container)
			{
				$plugin     = PluginHelper::getPlugin('task', 'webcronstart');
				$dispatcher = $container->get(DispatcherInterface::class);

				$webcron = new Webcronstart(
					$dispatcher,
					(array) $plugin,
				);

				return $webcron;
			}
		);
	}
};