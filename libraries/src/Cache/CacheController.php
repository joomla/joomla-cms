<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache;

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
}
