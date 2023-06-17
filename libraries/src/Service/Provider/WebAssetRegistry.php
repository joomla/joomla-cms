<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\CMS\WebAsset\WebAssetRegistry as Registry;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for the application's WebAsset dependency
 *
 * @since  4.0.0
 */
class WebAssetRegistry implements ServiceProviderInterface
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
        $container->alias('webassetregistry', Registry::class)
            ->share(
                Registry::class,
                function (Container $container) {
                    $registry = new Registry();

                    // Add Core registry files
                    $registry
                        ->addRegistryFile('media/vendor/joomla.asset.json')
                        ->addRegistryFile('media/system/joomla.asset.json')
                        ->addRegistryFile('media/legacy/joomla.asset.json');

                    return $registry;
                },
                true
            );
    }
}
