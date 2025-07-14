<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache;

use Joomla\CMS\Factory;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Public cache handler
 *
 * @since  1.7.0
 * @mixin  Cache
 * @note   As of 4.0 this class will be abstract
 */
class CacheController
{
    /**
     * Cache object
     *
     * @var    Cache
     * @since  1.7.0
     */
    public $cache;

    /**
     * Array of options
     *
     * @var    array
     * @since  1.7.0
     */
    public $options;

    /**
     * Constructor
     *
     * @param   array  $options  Array of options
     *
     * @since   1.7.0
     */
    public function __construct($options)
    {
        $this->cache   = new Cache($options);
        $this->options = &$this->cache->_options;

        // Overwrite default options with given options
        foreach ($options as $option => $value) {
            if ($value !== null) {
                $this->options[$option] = $value;
            }
        }
    }

    /**
     * Magic method to proxy CacheController method calls to Cache
     *
     * @param   string  $name       Name of the function
     * @param   array   $arguments  Array of arguments for the function
     *
     * @return  mixed
     *
     * @since   1.7.0
     */
    public function __call($name, $arguments)
    {
        return \call_user_func_array([$this->cache, $name], $arguments);
    }

    /**
     * Returns a reference to a cache adapter object, always creating it
     *
     * @param   string  $type     The cache object type to instantiate; default is output.
     * @param   array   $options  Array of options
     *
     * @return  CacheController
     *
     * @since       1.7.0
     * @throws      \RuntimeException
     *
     * @deprecated  4.2 will be removed in 6.0
     *              Use the cache controller factory instead
     *              Example: Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController($type, $options);
     */
    public static function getInstance($type = 'output', $options = [])
    {
        @trigger_error(
            \sprintf(
                '%s() is deprecated. The cache controller should be fetched from the factory.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        try {
            return Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController($type, $options);
        } catch (\RuntimeException) {
            $type  = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $type));
            $class = 'JCacheController' . ucfirst($type);

            if (!class_exists($class)) {
                // Search for the class file in the Cache include paths.
                $path = Path::find(self::addIncludePath(), strtolower($type) . '.php');

                if ($path !== false) {
                    \JLoader::register($class, $path);
                }

                // The class should now be loaded
                if (!class_exists($class)) {
                    throw new \RuntimeException('Unable to load Cache Controller: ' . $type, 500);
                }

                // Only trigger a deprecation notice if the file and class are found
                @trigger_error(
                    'Support for including cache controllers using path lookup is deprecated and will be removed in 5.0.'
                        . ' Use a custom cache controller factory instead.',
                    E_USER_DEPRECATED
                );
            }

            return new $class($options);
        }
    }

    /**
     * Add a directory where Cache should search for controllers. You may either pass a string or an array of directories.
     *
     * @param   array|string  $path  A path to search.
     *
     * @return  array  An array with directory elements
     *
     * @since       1.7.0
     *
     * @deprecated  4.2 will be removed in 6.0
     *              Use the cache controller factory instead
     *              Example: Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController($type, $options);
     */
    public static function addIncludePath($path = '')
    {
        static $paths;

        if (!isset($paths)) {
            $paths = [];
        }

        if (!empty($path) && !\in_array($path, $paths)) {
            // Only trigger a deprecation notice when adding a lookup path
            @trigger_error(
                'Support for including cache controllers using path lookup is deprecated and will be removed in 5.0.'
                    . ' Use a custom cache controller factory instead.',
                E_USER_DEPRECATED
            );

            array_unshift($paths, Path::clean($path));
        }

        return $paths;
    }
}
