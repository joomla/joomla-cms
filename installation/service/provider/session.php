<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Input\Input;
use Joomla\Session\Handler\FilesystemHandler;

/**
 * Service provider for the application's session dependency
 *
 * @since  4.0
 */
class InstallationServiceProviderSession implements ServiceProviderInterface
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
		$container->alias('session', 'Joomla\Session\SessionInterface')
			->alias('JSession', 'Joomla\Session\SessionInterface')
			->alias('Joomla\Session\Session', 'Joomla\Session\SessionInterface')
			->share(
				'Joomla\Session\SessionInterface',
				function (Container $container)
				{
					$app = JFactory::getApplication();

					// Generate a session name.
					$name = JApplicationHelper::getHash($app->get('session_name', get_class($app)));

					// Calculate the session lifetime.
					$lifetime = (($app->get('lifetime')) ? $app->get('lifetime') * 60 : 900);

					// Initialize the options for the Session object.
					$options = array(
						'name'   => $name,
						'expire' => $lifetime
					);

					// Set up the storage handler
					$handler = new FilesystemHandler(JPATH_INSTALLATION . '/sessions');

					$storage = new JSessionStorageJoomla($handler, array(), JFactory::getApplication()->input);

					$dispatcher = $container->get('Joomla\Event\DispatcherInterface');
					$dispatcher->addListener('onAfterSessionStart', array($app, 'afterSessionStart'));

					// TODO - Migrate JInput to Framework Input package
					return new JSession(new Input($_REQUEST), $storage, null, $options);
				},
				true
			);
	}
}
