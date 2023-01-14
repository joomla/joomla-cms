<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache;

\defined('_JEXEC') or die;

/**
 * Default factory for creating CacheController objects
 *
 * @since  4.0.0
 */
class CacheControllerFactory implements CacheControllerFactoryInterface
{
    /**
     * Method to get an instance of a cache controller.
     *
     * @param   string  $type     The cache object type to instantiate
     * @param   array   $options  Array of options
     *
     * @return  CacheController
     *
     * @since   4.0.0
     * @throws  \RuntimeException
     */
    public function createCacheController($type = 'output', $options = []): CacheController
    {
        if (!$type)
        {
            $type = 'output';
        }

        $type = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $type));

        $class = __NAMESPACE__ . '\\Controller\\' . ucfirst($type) . 'Controller';

        // The class should now be loaded
        if (!class_exists($class))
        {
            throw new \RuntimeException('Unable to load Cache Controller: ' . $type, 500);
        }

        return new $class($options);
    }
}
