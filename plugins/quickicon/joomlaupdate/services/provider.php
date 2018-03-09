<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Quickicon\Joomlaupdate\Plugin\Joomlaupdate;

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
		// @Todo This needs to be changed to a proper factory
		$plugin = \Joomla\CMS\Plugin\PluginHelper::getPlugin('quickicon', 'joomlaupdate');

		$dispatcher = $container->get(DispatcherInterface::class);
		$container->set('plugin', new Joomlaupdate($dispatcher, \Joomla\CMS\Factory::getDocument(), (array)$plugin));
	}
};
