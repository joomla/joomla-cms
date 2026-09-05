<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Service
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Service\Provider;

use Joomla\CMS\Installation\Router\InstallationRouter;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Router service provider
 *
 * @since  __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
     */
    public function register(Container $container): void
    {
        $container->alias('InstallationRouter', InstallationRouter::class)
            ->alias('JRouterInstallation', InstallationRouter::class)
            ->share(
                InstallationRouter::class,
                function (Container $container) {
                    return new InstallationRouter();
                },
                true
            );
    }
}
