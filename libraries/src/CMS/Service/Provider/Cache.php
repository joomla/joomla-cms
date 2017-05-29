<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\Cache\Adapter;
use Joomla\Cache\AbstractCacheItemPool;
use Joomla\Cache\CacheItemPoolInterface as JoomlaCacheItemPoolInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Psr\Cache\CacheItemPoolInterface as PsrCacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Cache service provider
 *
 * @since  __DEPLOY_VERSION__
 */
class Cache implements ServiceProviderInterface
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
		$container->alias('cache.storage', PsrCacheItemPoolInterface::class)
			->alias(JoomlaCacheItemPoolInterface::class, PsrCacheItemPoolInterface::class)
			->alias(AbstractCacheItemPool::class, PsrCacheItemPoolInterface::class)
			->alias(CacheInterface::class, PsrCacheItemPoolInterface::class)
			->share(
				PsrCacheItemPoolInterface::class,
				function (Container $container)
				{
					$config = \JFactory::getConfig();

					// Set up the storage handler
					$handlerType = $config->get('cache_handler');

					switch ($handlerType)
					{
						case 'apc':
							if (!Adapter\Apc::isSupported())
							{
								throw new \RuntimeException('APC is not supported on this system.');
							}

							$handler = new Adapter\Apc;

							break;

						case 'apcu':
							if (!Adapter\Apcu::isSupported())
							{
								throw new \RuntimeException('APCu is not supported on this system.');
							}

							$handler = new Adapter\Apcu;

							break;

						case 'file':
							$path = $config->get('cache_path', JPATH_CACHE);

							$handler = new Adapter\File(['file.path' => $path]);

							break;

						case 'memcached':
							if (!Adapter\Memcached::isSupported())
							{
								throw new \RuntimeException('Memcached is not supported on this system.');
							}

							if ($config->get('memcached_persist', true))
							{
								$memcached = new \Memcached(\JFactory::getApplication()->getSession()->getId());
							}
							else
							{
								$memcached = new \Memcached;
							}

							$memcachedtest = $memcached->addServer(
								$config->get('memcached_server_host', 'localhost'),
								$config->get('memcached_server_port', 11211)
							);

							if ($memcachedtest == false)
							{
								throw new \RuntimeException('Could not connect to Memcached server');
							}

							$memcached->setOption(\Memcached::OPT_COMPRESSION, (bool) $config->get('memcached_compress', false));

							$handler = new Adapter\Memcached($memcached);

							break;

						case 'none':
							$handler = new Adapter\None;

							break;

						case 'redis':
							if (!Adapter\Redis::isSupported())
							{
								throw new \RuntimeException('Redis is not supported on this system.');
							}

							$server = [
								'host' => $config->get('redis_server_host', 'localhost'),
								'port' => $config->get('redis_server_port', 6379),
								'auth' => $config->get('redis_server_auth', null),
								'db'   => (int) $config->get('redis_server_db', null)
							];

							$redis = new \Redis;

							if ($config->get('redis_persist', true))
							{
								$connection = $redis->pconnect($server['host'], $server['port']);
							}
							else
							{
								$connection = $redis->connect($server['host'], $server['port']);
							}

							$auth = (!empty($server['auth'])) ? $redis->auth($server['auth']) : true;

							if ($connection === false)
							{
								throw new \RuntimeException('Could not connect to Redis server');
							}

							if ($auth === false)
							{
								throw new \RuntimeException('Could not authenticate to Redis server');
							}

							if ($redis->select($server['db']) === false)
							{
								throw new \RuntimeException('Failed to select Redis database');
							}

							$handler = new Adapter\Redis($redis);

							break;

						case 'runtime':
							$handler = new Adapter\Runtime;

							break;

						case 'wincache':
							if (!Adapter\Wincache::isSupported())
							{
								throw new \RuntimeException('Wincache is not supported on this system.');
							}

							$handler = new Adapter\Wincache;

							break;

						case 'xcache':
							if (!Adapter\XCache::isSupported())
							{
								throw new \RuntimeException('XCache is not supported on this system.');
							}

							$handler = new Adapter\XCache;

							break;

						default:
							throw new \InvalidArgumentException(sprintf('The "%s" cache adapter is not recognised.', $handlerType));
					}

					return $handler;
				},
				true
			);
	}
}
