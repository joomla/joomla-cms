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
                        'JOOMLA_SITE_SITENAME'                => 'sitename',
                        'JOOMLA_SITE_EDITOR'                  => 'editor',
                        'JOOMLA_SITE_CAPTCHA'                 => 'captcha',
                        'JOOMLA_SITE_LIST_LIMIT'              => 'list_limit',
                        'JOOMLA_SITE_ACCESS'                  => 'access',
                        'JOOMLA_SITE_FRONT_EDITING'           => 'frontediting',
                        'JOOMLA_SITE_OFFLINE'                 => 'offline',
                        'JOOMLA_SITE_OFFLINE_MESSAGE'         => 'offline_message',
                        'JOOMLA_SITE_OFFLINE_MESSAGE_DISPLAY' => 'display_offline_message',
                        'JOOMLA_SITE_OFFLINE_IMAGE'           => 'offline_image',

                        // Debug settings.
                        'JOOMLA_DEBUG'            => 'debug',
                        'JOOMLA_DEBUG_LANG'       => 'debug_lang',
                        'JOOMLA_DEBUG_LANG_CONST' => 'debug_lang_const',

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

                        // CORS settings.
                        'JOOMLA_CORS'               => 'cors',
                        'JOOMLA_CORS_ALLOW_ORIGIN'  => 'cors_allow_origin',
                        'JOOMLA_CORS_ALLOW_METHODS' => 'cors_allow_methods',
                        'JOOMLA_CORS_ALLOW_HEADERS' => 'cors_allow_headers',

                        // Mail settings.
                        'JOOMLA_MAIL_ONLINE'   => 'mailonline',
                        'JOOMLA_MAIL_MAILER'   => 'mailer',
                        'JOOMLA_MAIL_FROM'     => 'mailfrom',
                        'JOOMLA_MAIL_FROMNAME' => 'fromname',
                        'JOOMLA_SENDMAIL'      => 'sendmail',
                        'JOOMLA_SMTP_AUTH'     => 'smtpauth',
                        'JOOMLA_SMTP_USER'     => 'smtpuser',
                        'JOOMLA_SMTP_PASS'     => 'smtppass',
                        'JOOMLA_SMTP_HOST'     => 'smtphost',
                        'JOOMLA_SMTP_SECURE'   => 'smtpsecure',
                        'JOOMLA_SMTP_PORT'     => 'smtpport',

                        // Cache settings.
                        'JOOMLA_CACHING'               => 'caching',
                        'JOOMLA_CACHE_HANDLER'         => 'cache_handler',
                        'JOOMLA_CACHE_TIME'            => 'cachetime',
                        'JOOMLA_CACHE_PLATFORM_PREFIX' => 'cache_platformprefix',

                        // Meta settings.
                        'JOOMLA_META_DESC'    => 'MetaDesc',
                        'JOOMLA_META_AUTHOR'  => 'MetaAuthor',
                        'JOOMLA_META_VERSION' => 'MetaVersion',
                        'JOOMLA_META_ROBOTS'  => 'robots',

                        // SEO settings.
                        'JOOMLA_SEF'              => 'sef',
                        'JOOMLA_SEF_REWRITE'      => 'sef_rewrite',
                        'JOOMLA_SEF_SUFFIX'       => 'sef_suffix',
                        'JOOMLA_SEF_UNICODESLUGS' => 'unicodeslugs',

                        // Session setting.
                        'JOOMLA_SESSION_LIFETIME' => 'lifetime',
                        'JOOMLA_SESSION_HANDLER'  => 'session_handler',
                        'JOOMLA_SESSION_SHARED'   => 'shared_session',
                        'JOOMLA_SESSION_METADATA' => 'session_metadata',
                    ];
                }
            );
    }
}
