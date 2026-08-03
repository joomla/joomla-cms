<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension\Service\Provider;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for the service MVC factory with autowiring.
 *
 * @since  __DEPLOY_VERSION__
 */
class AutowireFactory extends MVCFactory
{
    /**
     * MVCFactory constructor.
     *
     * @param   string  $namespace  The namespace
     *
     * @since   4.0.0
     */
    public function __construct(private string $namespace)
    {
    }

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
        $container->set(
            MVCFactoryInterface::class,
            function (Container $container) {

                $localContainer = new \Joomla\DI\Container($container);
                $localContainer->set('scalar.namespace', $this->namespace);

                return new \Joomla\CMS\MVC\Factory\AutowireFactory($localContainer);
            }
        );
    }
}
