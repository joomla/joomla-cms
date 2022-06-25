<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\Authentication\Password\Argon2idHandler as BaseArgon2idHandler;
use Joomla\Authentication\Password\Argon2iHandler as BaseArgon2iHandler;
use Joomla\Authentication\Password\BCryptHandler as BaseBCryptHandler;
use Joomla\CMS\Authentication\Password\Argon2idHandler;
use Joomla\CMS\Authentication\Password\Argon2iHandler;
use Joomla\CMS\Authentication\Password\BCryptHandler;
use Joomla\CMS\Authentication\Password\ChainedHandler;
use Joomla\CMS\Authentication\Password\MD5Handler;
use Joomla\CMS\Authentication\Password\PHPassHandler;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the authentication dependencies
 *
 * @since  4.0.0
 */
class Authentication implements ServiceProviderInterface
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
        $container->alias('password.handler.argon2i', Argon2iHandler::class)
            ->alias(BaseArgon2iHandler::class, Argon2iHandler::class)
            ->share(
                Argon2iHandler::class,
                function (Container $container) {
                    return new Argon2iHandler();
                },
                true
            );

        $container->alias('password.handler.argon2id', Argon2idHandler::class)
            ->alias(BaseArgon2idHandler::class, Argon2idHandler::class)
            ->share(
                Argon2idHandler::class,
                function (Container $container) {
                    return new Argon2idHandler();
                },
                true
            );

        $container->alias('password.handler.chained', ChainedHandler::class)
            ->share(
                ChainedHandler::class,
                function (Container $container) {
                    $handler = new ChainedHandler();

                    // Load the chain with supported core handlers
                    $handler->addHandler($container->get(BCryptHandler::class));

                    if (Argon2iHandler::isSupported()) {
                        $handler->addHandler($container->get(Argon2iHandler::class));
                    }

                    if (Argon2idHandler::isSupported()) {
                        $handler->addHandler($container->get(Argon2idHandler::class));
                    }

                    $handler->addHandler($container->get(PHPassHandler::class));
                    $handler->addHandler($container->get(MD5Handler::class));

                    return $handler;
                },
                true
            );

        // The Joomla default is BCrypt so alias this service
        $container->alias('password.handler.default', BCryptHandler::class)
            ->alias(BaseBCryptHandler::class, BCryptHandler::class)
            ->alias('password.handler.bcrypt', BCryptHandler::class)
            ->share(
                BCryptHandler::class,
                function (Container $container) {
                    return new BCryptHandler();
                },
                true
            );

        $container->alias('password.handler.md5', MD5Handler::class)
            ->share(
                MD5Handler::class,
                function (Container $container) {
                    @trigger_error(
                        sprintf(
                            'The "%1$s" class service is deprecated, use the "%2$s" service for the active password handler instead.',
                            MD5Handler::class,
                            'password.handler.default'
                        ),
                        E_USER_DEPRECATED
                    );

                    return new MD5Handler();
                },
                true
            );

        $container->alias('password.handler.phpass', PHPassHandler::class)
            ->share(
                PHPassHandler::class,
                function (Container $container) {
                    @trigger_error(
                        sprintf(
                            'The "%1$s" class service is deprecated, use the "%2$s" service for the active password handler instead.',
                            PHPassHandler::class,
                            'password.handler.default'
                        ),
                        E_USER_DEPRECATED
                    );

                    return new PHPassHandler();
                },
                true
            );
    }
}
