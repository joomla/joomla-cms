<?php

declare(strict_types=1);

namespace Joomla\CMS\Service;

use Joomla\CMS\Form\CachingFormFactory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\User\CachingUserFactory;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Registers caching decorator factories for Forms and Users.
 *
 * Default behavior (BC-safe):
 *   - Adds opt-in services 'caching.form.factory' and 'caching.user.factory'
 *     without changing existing bindings.
 *
 * Optional behavior:
 *   - If $replaceDefaults is true, replaces the default bindings of
 *     FormFactoryInterface and UserFactoryInterface with the caching decorators.
 *
 * Usage:
 *   // BC-safe, opt-in only:
 *   $container->registerServiceProvider(new CachingFactoriesProvider());
 *
 *   // Replace defaults globally (still no interface changes):
 *   $container->registerServiceProvider(new CachingFactoriesProvider(true));
 */
final class CachingFactoriesProvider implements ServiceProviderInterface
{
    public function __construct(private bool $replaceDefaults = false)
    {
    }

    public function register(Container $container): void
    {
        // ---- Opt-in services (always provided) ------------------------------

        // caching.form.factory: a CachingFormFactory that wraps the current default form factory
        $container->share('caching.form.factory', function (Container $c) {

            // Resolve whatever is currently bound for the interface
            $inner = $c->get(FormFactoryInterface::class);
            return new CachingFormFactory($inner);
        });
        // caching.user.factory: a CachingUserFactory that wraps the current default user factory
        $container->share('caching.user.factory', function (Container $c) {

            $inner = $c->get(UserFactoryInterface::class);
            return new CachingUserFactory($inner);
        });
        // ---- Optional: replace defaults (no BC break; interfaces unchanged) --

        if ($this->replaceDefaults) {
            // Override the interface bindings with the caching decorators
            $container->share(FormFactoryInterface::class, function (Container $c) {

                // Wrap the original concrete (obtain via previous binding)
                $inner = $c->get('core.form.factory') ?? $c->get('caching.form.factory');
                // If 'core.form.factory' is not registered, fall back to opt-in service
                if ($inner instanceof CachingFormFactory) {
                    return $inner;
                }
                return new CachingFormFactory($inner);
            });
            $container->share(UserFactoryInterface::class, function (Container $c) {

                $inner = $c->get('core.user.factory') ?? $c->get('caching.user.factory');
                if ($inner instanceof CachingUserFactory) {
                    return $inner;
                }
                return new CachingUserFactory($inner);
            });
        }
    }
}
