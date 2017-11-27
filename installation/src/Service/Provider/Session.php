<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Service\Provider;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Storage\JoomlaStorage;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Session\Handler\FilesystemHandler;
use Joomla\Session\Validator\AddressValidator;
use Joomla\Session\Validator\ForwardedValidator;

/**
 * Service provider for the application's session dependency
 *
 * @since  4.0
 */
class Session implements ServiceProviderInterface
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
					$app = Factory::getApplication();

					// Initialize the options for the Session object.
					$options = array(
						'name'   => md5(get_class($app)),
						'expire' => 900
					);

					// Set up the storage handler
					$handler = new FilesystemHandler(JPATH_INSTALLATION . '/sessions');

					$input = $app->input;

					$storage = new JoomlaStorage($input, $handler);

					$dispatcher = $container->get('Joomla\Event\DispatcherInterface');
					$dispatcher->addListener('onAfterSessionStart', array($app, 'afterSessionStart'));

					$session = new \Joomla\CMS\Session\Session($storage, $dispatcher, $options);
					$session->addValidator(new AddressValidator($input, $session));
					$session->addValidator(new ForwardedValidator($input, $session));

					return $session;
				},
				true
			);
	}
}
