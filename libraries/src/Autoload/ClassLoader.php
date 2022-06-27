<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Autoload;

use Composer\Autoload\ClassLoader as ComposerClassLoader;

/**
 * Decorate Composer ClassLoader for Joomla!
 *
 * For backward compatibility due to class aliasing in the CMS, the loadClass() method was modified to call
 * the JLoader::applyAliasFor() method.
 *
 * @since  3.4
 */
class ClassLoader
{
    /**
     * The Composer class loader
     *
     * @var    ComposerClassLoader
     * @since  3.4
     */
    private $loader;

    /**
     * Constructor
     *
     * @param   ComposerClassLoader  $loader  Composer autoloader
     *
     * @since   3.4
     */
    public function __construct(ComposerClassLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Loads the given class or interface.
     *
     * @param   string  $class  The name of the class
     *
     * @return  boolean|null  True if loaded, null otherwise
     *
     * @since   3.4
     */
    public function loadClass($class)
    {
        if ($result = $this->loader->loadClass($class)) {
            \JLoader::applyAliasFor($class);
        }

        return $result;
    }
}
