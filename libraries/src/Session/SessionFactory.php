<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Session;

use Joomla\Database\DatabaseInterface;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
use Joomla\Session\Handler;
use Joomla\Session\HandlerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Factory for creating session API objects
 *
 * @since  4.0.0
 */
class SessionFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Create a session handler based on the application configuration.
     *
     * @param   array  $options  The options used to instantiate the SessionInterface instance.
     *
     * @return  HandlerInterface
     *
     * @since   4.0.0
     */
    public function createSessionHandler(array $options): HandlerInterface
    {
        $resolver = new OptionsResolver();
        $this->configureSessionHandlerOptions($resolver);

        $options = $resolver->resolve($options);

        /** @var Registry $config */
        $config = $this->getContainer()->get('config');

        $handlerType = $config->get('session_handler', 'filesystem');

        switch ($handlerType) {
            case 'apcu':
                if (!Handler\ApcuHandler::isSupported()) {
                    throw new \RuntimeException('APCu is not supported on this system.');
                }

                return new Handler\ApcuHandler();

            case 'database':
                return new Handler\DatabaseHandler($this->getContainer()->get(DatabaseInterface::class));

            case 'filesystem':
            case 'none':
                // Try to use a custom configured path, fall back to the path in the PHP runtime configuration
                $path = $config->get('session_filesystem_path', \ini_get('session.save_path'));

                // If we still have no path, as a last resort fall back to the system's temporary directory
                if (empty($path)) {
                    $path = sys_get_temp_dir();
                }

                return new Handler\FilesystemHandler($path);

            case 'memcached':
                if (!Handler\MemcachedHandler::isSupported()) {
                    throw new \RuntimeException('Memcached is not supported on this system.');
                }

                $host = $config->get('session_memcached_server_host', 'localhost');
                $port = $config->get('session_memcached_server_port', 11211);

                $memcached = new \Memcached($config->get('session_memcached_server_id', 'joomla_cms'));
                $memcached->addServer($host, $port);

                ini_set('session.save_path', "$host:$port");
                ini_set('session.save_handler', 'memcached');

                return new Handler\MemcachedHandler($memcached, ['ttl' => $options['expire']]);

            case 'redis':
                if (!Handler\RedisHandler::isSupported()) {
                    throw new \RuntimeException('Redis is not supported on this system.');
                }

                $redis = new \Redis();
                $host  = $config->get('session_redis_server_host', '127.0.0.1');

                // Use default port if connecting over a socket whatever the config value
                $port = $host[0] === '/' ? 0 : $config->get('session_redis_server_port', 6379);

                if ($config->get('session_redis_persist', true)) {
                    $redis->pconnect(
                        $host,
                        $port
                    );
                } else {
                    $redis->connect(
                        $host,
                        $port
                    );
                }

                if (!empty($config->get('session_redis_server_auth', ''))) {
                    $redis->auth($config->get('session_redis_server_auth', null));
                }

                $db = (int) $config->get('session_redis_server_db', 0);

                if ($db !== 0) {
                    $redis->select($db);
                }

                return new Handler\RedisHandler($redis, ['ttl' => $options['expire']]);

            default:
                throw new \InvalidArgumentException(\sprintf('The "%s" session handler is not recognised.', $handlerType));
        }
    }

    /**
     * Resolve the options for the session handler.
     *
     * @param   OptionsResolver  $resolver  The options resolver.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configureSessionHandlerOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'force_ssl' => false,
            ]
        );

        $resolver->setRequired(['name', 'expire']);

        $resolver->setAllowedTypes('name', ['string']);
        $resolver->setAllowedTypes('expire', ['int']);
        $resolver->setAllowedTypes('force_ssl', ['bool']);
    }
}
