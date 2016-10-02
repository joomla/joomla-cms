<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Service\Provider;

defined('JPATH_PLATFORM') or die;

use InvalidArgumentException;
use JApplicationHelper;
use JFactory;
use Joomla\Cms\Session\Storage\JoomlaStorage;
use Joomla\Cms\Session\Validator\AddressValidator;
use Joomla\Cms\Session\Validator\ForwardedValidator;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Session\Handler;
use JSession;
use Memcache;
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
					$config = JFactory::getConfig();

					// Generate a session name.
					$name = JApplicationHelper::getHash($config->get('session_name', get_class(JFactory::getApplication())));

					// Calculate the session lifetime.
					$lifetime = (($config->get('lifetime')) ? $config->get('lifetime') * 60 : 900);

					// Initialize the options for the Session object.
					$options = array(
						'name'   => $name,
						'expire' => $lifetime
					);

					switch (JFactory::getApplication()->getClientId())
					{
						case 0:
							if ($config->get('force_ssl') == 2)
							{
								$options['force_ssl'] = true;
							}

							break;

						case 1:
							if ($config->get('force_ssl') >= 1)
							{
								$options['force_ssl'] = true;
							}

							break;
					}

					// Set up the storage handler
					$handlerType = $config->get('session_handler', 'filesystem');

					switch ($handlerType)
					{
						case 'apc':
							if (!Handler\ApcHandler::isSupported())
							{
								throw new RuntimeException('APC is not supported on this system.');
							}

							$handler = new Handler\ApcHandler;

							break;

						case 'apcu':
							if (!Handler\ApcuHandler::isSupported())
							{
								throw new RuntimeException('APCu is not supported on this system.');
							}

							$handler = new Handler\ApcuHandler;

							break;

						case 'database':
							$handler = new Handler\DatabaseHandler(JFactory::getDbo());

							break;

						case 'filesystem':
						case 'none':
							$path = $config->get('session_filesystem_path', '');

							// If no path is given, fall back to the system's temporary directory
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

							$handler = new Handler\MemcachedHandler($memcached, array('ttl' => $lifetime));

							ini_set('session.save_path', "$host:$port");
							ini_set('session.save_handler', 'memcached');

							break;

						case 'memcache':
							if (!Handler\MemcacheHandler::isSupported())
							{
								throw new RuntimeException('Memcache is not supported on this system.');
							}

							$host = $config->get('session_memcache_server_host', 'localhost');
							$port = $config->get('session_memcache_server_port', 11211);

							$memcache = new Memcache($config->get('session_memcache_server_id', 'joomla_cms'));
							$memcache->addserver($host, $port);

							$handler = new Handler\MemcacheHandler($memcache, array('ttl' => $lifetime));

							ini_set('session.save_path', "$host:$port");
							ini_set('session.save_handler', 'memcache');

							break;

						case 'redis':
							if (!Handler\RedisHandler::isSupported())
							{
								throw new RuntimeException('Redis is not supported on this system.');
							}

							$redis = new Redis;
							$redis->connect(
								$config->get('session_redis_server_host', '127.0.0.1'),
								$config->get('session_redis_server_port', 6379)
							);

							$handler = new Handler\RedisHandler($redis, array('ttl' => $lifetime));

							break;

						case 'wincache':
							if (!Handler\WincacheHandler::isSupported())
							{
								throw new RuntimeException('Wincache is not supported on this system.');
							}

							$handler = new Handler\WincacheHandler;

							break;

						case 'xcache':
							if (!Handler\XCacheHandler::isSupported())
							{
								throw new RuntimeException('XCache is not supported on this system.');
							}

							$handler = new Handler\XCacheHandler;

							break;

						default:
							throw new InvalidArgumentException(sprintf('The "%s" session handler is not recognised.', $handlerType));
					}

					$input = JFactory::getApplication()->input;

					$storage = new JoomlaStorage($input, $handler, array('cookie_lifetime' => $lifetime));

					$dispatcher = $container->get('Joomla\Event\DispatcherInterface');
					$dispatcher->addListener('onAfterSessionStart', array(JFactory::getApplication(), 'afterSessionStart'));

					$session = new JSession($storage, $dispatcher, $options);
					$session->addValidator(new AddressValidator($input, $session));
					$session->addValidator(new ForwardedValidator($input, $session));

					return $session;
				},
				true
			);
	}
}
