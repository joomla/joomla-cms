<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\CMS\Http\HttpClientFactory;
use Joomla\CMS\Http\HttpFactoryInterface;
use Joomla\CMS\Version;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for the HTTP client factory dependency.
 *
 * @since  __DEPLOY_VERSION__
 */
class Http implements ServiceProviderInterface
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
        $container->alias('http.factory', HttpFactoryInterface::class)
            ->alias(HttpClientFactory::class, HttpFactoryInterface::class)
            ->share(
                HttpFactoryInterface::class,
                function (Container $container) {
                    return new HttpClientFactory(new Version(), $container->get('config'));
                },
                true
            );
    }
}
