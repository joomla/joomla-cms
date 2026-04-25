<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Http;

use Joomla\CMS\Version;
use Joomla\Http\Http;
use Joomla\Http\HttpFactory as FrameworkHttpFactory;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Default factory for creating Http client objects.
 *
 * @since  __DEPLOY_VERSION__
 */
class HttpClientFactory implements HttpFactoryInterface
{
    /**
     * The Joomla version object used to build the default user agent string.
     *
     * @var    Version
     * @since  __DEPLOY_VERSION__
     */
    private Version $version;

    /**
     * The application configuration used to populate default HTTP options.
     *
     * @var    Registry
     * @since  __DEPLOY_VERSION__
     */
    private Registry $config;

    /**
     * Constructor.
     *
     * @param   Version   $version  The Joomla version object.
     * @param   Registry  $config   The application configuration.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(Version $version, Registry $config)
    {
        $this->version = $version;
        $this->config  = $config;
    }

    /**
     * Method to get an instance of a Http client with sensible Joomla defaults.
     *
     * The factory pre-populates the userAgent option using the Joomla version string
     * when no userAgent has been provided in the options.  Proxy settings from the
     * application configuration are also applied automatically when a proxy is enabled.
     *
     * @param   array|\ArrayAccess  $options   Client options.
     * @param   array|string|null   $adapters  Adapter (string) or queue of adapters (array) to use for communication.
     *
     * @return  Http
     *
     * @throws  \RuntimeException
     * @since   __DEPLOY_VERSION__
     */
    public function createHttp(array|\ArrayAccess $options = [], array|string|null $adapters = null): Http
    {
        // Set default userAgent if nothing else is set
        if (!isset($options['userAgent'])) {
            $options['userAgent'] = $this->version->getUserAgent('Joomla', true, false);
        }

        // Set the proxy configuration, if enabled
        if ($this->config->get('proxy_enable')) {
            if (!isset($options['proxy.enabled'])) {
                $options['proxy.enabled'] = true;
            }

            if (!isset($options['proxy.host'])) {
                $options['proxy.host'] = $this->config->get('proxy_host');
            }

            if (!isset($options['proxy.port'])) {
                $options['proxy.port'] = $this->config->get('proxy_port');
            }

            if (!isset($options['proxy.user'])) {
                $options['proxy.user'] = $this->config->get('proxy_user');
            }

            if (!isset($options['proxy.password'])) {
                $options['proxy.password'] = $this->config->get('proxy_pass');
            }
        }

        return (new FrameworkHttpFactory())->getHttp($options, $adapters);
    }
}
