<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\CMS\Application\ApiApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Router\AdministratorRouter;
use Joomla\CMS\Router\ApiRouter;
use Joomla\CMS\Router\SiteRouter;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for the application's API router dependency
 *
 * @since  4.0.0
 */
class Router implements ServiceProviderInterface
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
        $container->alias('SiteRouter', SiteRouter::class)
            ->alias('JRouterSite', SiteRouter::class)
            ->share(
                SiteRouter::class,
                function (Container $container) {
                    return new SiteRouter($container->get(SiteApplication::class));
                },
                true
            );

        $container->alias('AdministratorRouter', AdministratorRouter::class)
            ->alias('JRouterAdministrator', AdministratorRouter::class)
            ->share(
                AdministratorRouter::class,
                function (Container $container) {
                    return new AdministratorRouter();
                },
                true
            );

        $container->alias('ApiRouter', ApiRouter::class)
            ->share(
                ApiRouter::class,
                function (Container $container) {
                    return new ApiRouter($container->get(ApiApplication::class));
                },
                true
            );
    }
}
