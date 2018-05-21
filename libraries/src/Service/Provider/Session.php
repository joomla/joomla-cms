<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use InvalidArgumentException;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Storage\JoomlaStorage;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Session\Handler;
use Joomla\Session\SessionEvents;
use Joomla\Session\Storage\RuntimeStorage;
use Joomla\Session\Validator\AddressValidator;
use Joomla\Session\Validator\ForwardedValidator;
use Memcached;
use Redis;
use RuntimeException;

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
					$config = $container->get('config');
					$app    = Factory::getApplication();

					// Generate a session name.
					$name = ApplicationHelper::getHash($config->get('session_name', get_class($app)));

					// Calculate the session lifetime.
					$lifetime = (($config->get('lifetime')) ? $config->get('lifetime') * 60 : 900);

					// Initialize the options for the Session object.
					$options = array(
						'name'   => $name,
						'expire' => $lifetime
					);

					if ($app->isClient('site') && $config->get('force_ssl') == 2)
					{
						$options['force_ssl'] = true;
					}

					if ($app->isClient('administrator') && $config->get('force_ssl') >= 1)
					{
						$options['force_ssl'] = true;
					}

					// Set up the storage handler
					$handlerType = $config->get('session_handler', 'filesystem');

					switch ($handlerType)
					{
						case 'apcu':
							if (!Handler\ApcuHandler::isSupported())
							{
								throw new RuntimeException('APCu is not supported on this system.');
							}

							$handler = new Handler\ApcuHandler;

							break;

						case 'database':
							$handler = new Handler\DatabaseHandler($container->get(DatabaseDriver::class));

							break;

						case 'filesystem':
						case 'none':
							// Try to use a custom configured path, fall back to the path in the PHP runtime configuration
							$path = $config->get('session_filesystem_path', ini_get('session.save_path'));

							// If we still have no path, as a last resort fall back to the system's temporary directory
							if (empty($path))
							{
								$path = sys_get_temp_dir();
							}

							$handler = new Handler\FilesystemHandler($path);

							break;

						case 'memcached':
							if (!Handler\MemcachedHandler::isSupported())
							{
								throw new RuntimeException('Memcached is not supported on this system.');
							}

							$host = $config->get('session_memcached_server_host', 'localhost');
							$port = $config->get('session_memcached_server_port', 11211);

							$memcached = new Memcached($config->get('session_memcached_server_id', 'joomla_cms'));
							$memcached->addServer($host, $port);

							$handler = new Handler\MemcachedHandler($memcached, ['ttl' => $lifetime]);

							ini_set('session.save_path', "$host:$port");
							ini_set('session.save_handler', 'memcached');

							break;

						case 'redis':
							if (!Handler\RedisHandler::isSupported())
							{
								throw new RuntimeException('Redis is not supported on this system.');
							}

							$redis = new Redis;
							$host = $config->get('session_redis_server_host', '127.0.0.1');

							// Use default port if connecting over a socket whatever the config value
							$port = $host[0] === '/' ? $config->get('session_redis_server_port', 6379) : 6379;

							if ($config->get('session_redis_persist', true))
							{
								$redis->pconnect(
									$host,
									$port
								);
							}
							else
							{
								$redis->connect(
									$host,
									$port
								);
							}

							if (!empty($config->get('session_redis_server_auth', '')))
							{
								$redis->auth($config->get('session_redis_server_auth', null));
							}

							$db = (int) $config->get('session_redis_server_db', 0);

							if ($db !== 0)
							{
								$redis->select($db);
							}

							$handler = new Handler\RedisHandler($redis, ['ttl' => $lifetime]);

							break;

						case 'wincache':
							if (!Handler\WincacheHandler::isSupported())
							{
								throw new RuntimeException('Wincache is not supported on this system.');
							}

							$handler = new Handler\WincacheHandler;

							break;

						default:
							throw new InvalidArgumentException(sprintf('The "%s" session handler is not recognised.', $handlerType));
					}

					$input = $app->input;

					if ($app->isClient('cli'))
					{
						$storage = new RuntimeStorage;
					}
					else
					{
						$storage = new JoomlaStorage($input, $handler);
					}

					$dispatcher = $container->get('Joomla\Event\DispatcherInterface');

					if (method_exists($app, 'afterSessionStart'))
					{
						$dispatcher->addListener(SessionEvents::START, array($app, 'afterSessionStart'));
					}

					$session = new \Joomla\CMS\Session\Session($storage, $dispatcher, $options);
					$session->addValidator(new AddressValidator($input, $session));
					$session->addValidator(new ForwardedValidator($input, $session));

					return $session;
				},
				true
			);
	}
}
