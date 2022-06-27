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

                    return new Registry(new \JConfig());
                },
                true
            );
    }
}
