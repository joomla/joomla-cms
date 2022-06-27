<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\CMS\Form\FormFactory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the form dependency
 *
 * @since  4.0.0
 */
class Form implements ServiceProviderInterface
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
        $container->alias('form.factory', FormFactoryInterface::class)
            ->alias(FormFactory::class, FormFactoryInterface::class)
            ->share(
                FormFactoryInterface::class,
                function (Container $container) {
                    $factory = new FormFactory();
                    $factory->setDatabase($container->get(DatabaseInterface::class));

                    return $factory;
                },
                true
            );
    }
}
