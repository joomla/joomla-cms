<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for the application's config dependency
 *
 * @since  4.0.0
 */
class Config implements ServiceProviderInterface
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
        $container->alias('config', 'JConfig')
            ->share(
                'JConfig',
                function (Container $container) {
                    if (!is_file(JPATH_CONFIGURATION . '/configuration.php')) {
                        return new Registry();
                    }

                    \JLoader::register('JConfig', JPATH_CONFIGURATION . '/configuration.php');

                    if (!class_exists('JConfig')) {
                        throw new \RuntimeException('Configuration class does not exist.');
                    }

                    $config = new Registry(new \JConfig());
                    $envMap = $container->get('config.env-map');

                    // Load environment variables
                    foreach (array_intersect_key($_ENV, $envMap) as $envName => $envValue) {
                        if ($envName === 'JOOMLA_LOG_PRIORITIES') {
                            $envValue = json_decode($envValue, true) ?: [];
                        } elseif ($envName === 'JOOMLA_DEBUG') {
                            $envValue = !!$envValue;
                        }

                        $config->set($envMap[$envName], match ($envValue) {
                            'true', '(true)' => true,
                            'false', '(false)' => false,
                            default => $envValue,
                        });
                    }

                    return $config;
                },
                true
            )
            ->share(
                'config.env-map',
                function (Container $container) {
                    return [
                        // Site settings.
                        'JOOMLA_SITE_NAME'                    => 'sitename',
                        'JOOMLA_SITE_EDITOR'                  => 'editor',
                        'JOOMLA_SITE_CAPTCHA'                 => 'captcha',
                        'JOOMLA_SITE_ACCESS'                  => 'access',
                        'JOOMLA_SITE_FRONT_EDITING'           => 'frontediting',
                        'JOOMLA_SITE_OFFLINE'                 => 'offline',
                        'JOOMLA_SITE_OFFLINE_MESSAGE'         => 'offline_message',
                        'JOOMLA_SITE_OFFLINE_MESSAGE_DISPLAY' => 'display_offline_message',
                        'JOOMLA_SITE_OFFLINE_IMAGE'           => 'offline_image',
                        'JOOMLA_SITE_LIST_LIMIT'              => 'list_limit',
                        'JOOMLA_SITE_FEED_LIMIT'              => 'feed_limit',
                        'JOOMLA_SITE_FEED_EMAIL'              => 'feed_email',
                        'JOOMLA_SITE_BEHIND_LOADBALANCER'     => 'behind_loadbalancer',

                        // Database settings.
                        'JOOMLA_DB_TYPE'                   => 'dbtype',
                        'JOOMLA_DB_HOST'                   => 'host',
                        'JOOMLA_DB_USER'                   => 'user',
                        'JOOMLA_DB_PASSWORD'               => 'password',
                        'JOOMLA_DB_NAME'                   => 'db',
                        'JOOMLA_DB_PREFIX'                 => 'dbprefix',
                        'JOOMLA_DB_ENCRYPTION'             => 'dbencryption',
                        'JOOMLA_DB_SSL_VERIFY_SERVER_CERT' => 'dbsslverifyservercert',
                        'JOOMLA_DB_SSL_KEY'                => 'dbsslkey',
                        'JOOMLA_DB_SSL_CERT'               => 'dbsslcert',
                        'JOOMLA_DB_SSL_CA'                 => 'dbsslca',
                        'JOOMLA_DB_SSL_CIPHER'             => 'dbsslcipher',

                        // Server settings
                        'JOOMLA_FORCE_SSL'       => 'force_ssl',
                        'JOOMLA_LIVE_SITE'       => 'live_site',
                        'JOOMLA_SECRET'          => 'secret',
                        'JOOMLA_GZIP'            => 'gzip',
                        'JOOMLA_ERROR_REPORTING' => 'error_reporting',
                        'JOOMLA_HELPURL'         => 'helpurl',
                        'JOOMLA_LOG_PATH'        => 'log_path',
                        'JOOMLA_TMP_PATH'        => 'tmp_path',

                        // Locale settings.
                        'JOOMLA_TIMEZONE' => 'offset',

                        // Session setting.
                        'JOOMLA_SESSION_LIFETIME'              => 'lifetime',
                        'JOOMLA_SESSION_HANDLER'               => 'session_handler',
                        'JOOMLA_SESSION_SHARED'                => 'shared_session',
                        'JOOMLA_SESSION_METADATA'              => 'session_metadata',
                        'JOOMLA_SESSION_METADATA_FOR_GUEST'    => 'session_metadata_for_guest',
                        'JOOMLA_SESSION_FILESYSTEM_PATH'       => 'session_filesystem_path',
                        'JOOMLA_SESSION_MEMCACHED_SERVER_HOST' => 'session_memcached_server_host',
                        'JOOMLA_SESSION_MEMCACHED_SERVER_PORT' => 'session_memcached_server_port',
                        'JOOMLA_SESSION_MEMCACHED_SERVER_ID'   => 'session_memcached_server_id',
                        'JOOMLA_SESSION_REDIS_PERSIST'         => 'session_redis_persist',
                        'JOOMLA_SESSION_REDIS_SERVER_AUTH'     => 'session_redis_server_auth',
                        'JOOMLA_SESSION_REDIS_SERVER_DB'       => 'session_redis_server_db',
                        'JOOMLA_SESSION_REDIS_SERVER_HOST'     => 'session_redis_server_host',
                        'JOOMLA_SESSION_REDIS_SERVER_PORT'     => 'session_redis_server_port',

                        // Mail settings.
                        'JOOMLA_MAIL_ONLINE'       => 'mailonline',
                        'JOOMLA_MAIL_MAILER'       => 'mailer',
                        'JOOMLA_MAIL_FROM'         => 'mailfrom',
                        'JOOMLA_MAIL_FROMNAME'     => 'fromname',
                        'JOOMLA_MAIL_REPLYTO'      => 'replyto',
                        'JOOMLA_MAIL_REPLYTO_NAME' => 'replytoname',
                        'JOOMLA_MAIL_MASSMAIL_OFF' => 'massmailoff',
                        'JOOMLA_SENDMAIL'          => 'sendmail',
                        'JOOMLA_SMTP_AUTH'         => 'smtpauth',
                        'JOOMLA_SMTP_USER'         => 'smtpuser',
                        'JOOMLA_SMTP_PASS'         => 'smtppass',
                        'JOOMLA_SMTP_HOST'         => 'smtphost',
                        'JOOMLA_SMTP_SECURE'       => 'smtpsecure',
                        'JOOMLA_SMTP_PORT'         => 'smtpport',

                        // Cache settings.
                        'JOOMLA_CACHING'                     => 'caching',
                        'JOOMLA_CACHE_HANDLER'               => 'cache_handler',
                        'JOOMLA_CACHE_TIME'                  => 'cachetime',
                        'JOOMLA_CACHE_PLATFORM_PREFIX'       => 'cache_platformprefix',
                        'JOOMLA_CACHE_MEMCACHED_PERSIST'     => 'memcached_persist',
                        'JOOMLA_CACHE_MEMCACHED_COMPRESS'    => 'memcached_compress',
                        'JOOMLA_CACHE_MEMCACHED_SERVER_HOST' => 'memcached_server_host',
                        'JOOMLA_CACHE_MEMCACHED_SERVER_PORT' => 'memcached_server_port',
                        'JOOMLA_CACHE_REDIS_PERSIST'         => 'redis_persist',
                        'JOOMLA_CACHE_REDIS_SERVER_HOST'     => 'redis_server_host',
                        'JOOMLA_CACHE_REDIS_SERVER_PORT'     => 'redis_server_port',
                        'JOOMLA_CACHE_REDIS_SERVER_AUTH'     => 'redis_server_auth',
                        'JOOMLA_CACHE_REDIS_SERVER_DB'       => 'redis_server_db',

                        // Log Settings
                        'JOOMLA_LOG_CATEGORIES'    => 'log_categories',
                        'JOOMLA_LOG_CATEGORY_MODE' => 'log_category_mode',
                        'JOOMLA_LOG_DEPRECATED'    => 'log_deprecated',
                        'JOOMLA_LOG_EVERYTHING'    => 'log_everything',
                        'JOOMLA_LOG_PRIORITIES'    => 'log_priorities',

                        // CORS settings.
                        'JOOMLA_CORS'               => 'cors',
                        'JOOMLA_CORS_ALLOW_ORIGIN'  => 'cors_allow_origin',
                        'JOOMLA_CORS_ALLOW_METHODS' => 'cors_allow_methods',
                        'JOOMLA_CORS_ALLOW_HEADERS' => 'cors_allow_headers',

                        // Proxy Settings
                        'JOOMLA_PROXY_ENABLE' => 'proxy_enable',
                        'JOOMLA_PROXY_HOST'   => 'proxy_host',
                        'JOOMLA_PROXY_PORT'   => 'proxy_port',
                        'JOOMLA_PROXY_USER'   => 'proxy_user',
                        'JOOMLA_PROXY_PASS'   => 'proxy_pass',

                        // Debug settings.
                        'JOOMLA_DEBUG'            => 'debug',
                        'JOOMLA_DEBUG_LANG'       => 'debug_lang',
                        'JOOMLA_DEBUG_LANG_CONST' => 'debug_lang_const',

                        // Meta settings.
                        'JOOMLA_META_DESC'           => 'MetaDesc',
                        'JOOMLA_META_AUTHOR'         => 'MetaAuthor',
                        'JOOMLA_META_VERSION'        => 'MetaVersion',
                        'JOOMLA_META_RIGHTS'         => 'MetaRights',
                        'JOOMLA_META_ROBOTS'         => 'robots',
                        'JOOMLA_SITENAME_PAGETITLES' => 'sitename_pagetitles',

                        // SEO settings.
                        'JOOMLA_SEF'              => 'sef',
                        'JOOMLA_SEF_REWRITE'      => 'sef_rewrite',
                        'JOOMLA_SEF_SUFFIX'       => 'sef_suffix',
                        'JOOMLA_SEF_UNICODESLUGS' => 'unicodeslugs',

                        // Cookie Settings
                        'JOOMLA_COOKIE_DOMAIN' => 'cookie_domain',
                        'JOOMLA_COOKIE_PATH'   => 'cookie_path',
                    ];
                }
            );
    }
}
