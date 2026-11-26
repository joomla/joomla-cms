<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\CMS\Mail\MailerFactory;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

\defined('_JEXEC') or die;

/**
 * Service provider for the mailer dependency
 *
 * @since  4.4.0
 */
class Mailer implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function register(Container $container)
    {
        $container->share(
            MailerFactoryInterface::class,
            function (Container $container) {
                return new MailerFactory($container->get('config'));
            },
            true
        );
    }
}
