<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Pathway\SitePathway;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for the application's pathway dependency
 *
 * @since  4.0.0
 */
class Pathway implements ServiceProviderInterface
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
        $container->alias('SitePathway', SitePathway::class)
            ->alias('JPathwaySite', SitePathway::class)
            ->alias('pathway.site', SitePathway::class)
            ->share(
                SitePathway::class,
                function (Container $container) {
                    return new SitePathway($container->get(SiteApplication::class));
                },
                true
            );

        $container->alias('Pathway', \Joomla\CMS\Pathway\Pathway::class)
            ->alias('JPathway', \Joomla\CMS\Pathway\Pathway::class)
            ->alias('pathway', \Joomla\CMS\Pathway\Pathway::class)
            ->share(
                \Joomla\CMS\Pathway\Pathway::class,
                function (Container $container) {
                    return new \Joomla\CMS\Pathway\Pathway();
                },
                true
            );
    }
}
