<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\CMS\Captcha\CaptchaRegistry as Registry;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for the application's CaptchaRegistry dependency
 *
 * @since  5.0.0
 */
class CaptchaRegistry implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function register(Container $container)
    {
        $container->alias('captcharegistry', Registry::class)
            ->share(
                Registry::class,
                function (Container $container) {
                    $dispatcher = $container->get(DispatcherInterface::class);
                    $registry   = new Registry();
                    $registry->setDispatcher($dispatcher);

                    PluginHelper::importPlugin('captcha', null, true, $dispatcher);
                    $registry->initRegistry();

                    return $registry;
                },
                true
            );
    }
}
