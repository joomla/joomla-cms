<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Form;

/**
 * Caching decorator for the Form factory.
 *
 * - No interface/signature changes (BC-safe).
 * - Reuses the same Form instance for a given (name, options) key within a request.
 * - Allows bypassing cache via ['fresh' => true] in $options.
 *
 * Register via DI to override the default binding of FormFactoryInterface:
 * $container->share(FormFactoryInterface::class, function (\Joomla\DI\Container $c) {
 *     $inner = $c->get('core.form.factory'); // whatever concrete is bound as
 *     return new CachingFormFactory($inner);
 * });
 */
final class CachingFormFactory implements FormFactoryInterface
{
    public function __construct(private FormFactoryInterface $inner)
    {
    }

    /** @var array<string, Form> */
    private array $cache = [];
    /**
         * {@inheritdoc}
         */
    public function createForm(string $name, array $options = []): Form
    {
        // Allow callers to opt out of caching explicitly.
        if (!empty($options['fresh'])) {
            // Do not store in cache when 'fresh' is requested.
            $opts = $options;
            unset($opts['fresh']);
            return $this->inner->createForm($name, $opts);
        }

        $key = $this->makeKey($name, $options);
        return $this->cache[$key] ??= $this->inner->createForm($name, $this->normalizedOptions($options));
    }

    /**
     * Removes a cached Form for the given name/options combination.
     * Useful when a caller knows the underlying XML or dynamic fields changed mid-request.
     */
    public function invalidate(string $name, array $options = []): void
    {
        $key = $this->makeKey($name, $options);
        unset($this->cache[$key]);
    }

    /**
     * Clears all cached Form instances (per-request scope).
     */
    public function invalidateAll(): void
    {
        $this->cache = [];
    }

    /**
     * Build a stable cache key from name + options.
     * Excludes volatile/nonce-like options that shouldn't affect identity.
     */
    private function makeKey(string $name, array $options): string
    {
        $opts = $this->normalizedOptions($options);
        // Remove flags that should not influence identity:
        unset(
            $opts['fresh'], // our local bypass flag
            $opts['debug'], // debugging shouldn't split cache entries
            $opts['timestamp']    // any time-based hint
        );
        // Sort for deterministic encoding.
        ksort($opts);
        return $name . '|' . md5(json_encode($opts, JSON_THROW_ON_ERROR));
    }

    /**
     * Normalize options to ensure deterministic keys and pass-through.
     */
    private function normalizedOptions(array $options): array
    {
        // Ensure consistent types/casing if needed. Adjust as your concrete factory expects.
        return $options;
    }
}
