<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Decorate Composer ClassLoader for Joomla!
 *
 * For backward compatibility due to class aliasing in the CMS, the loadClass() method was modified to call
 * the JLoader::applyAliasFor() method.
 *
 * @author  Johan Janssens
 * @since   3.4
 */
class JClassLoader
{
    /**
     * The composer class loader
     *
     * @var \Composer\Autoload\ClassLoader
     */
    private $loader;

    public function __construct(\Composer\Autoload\ClassLoader $loader)
    {
        $this->loader = $loader;
    }

    public function loadClass($class)
    {
        if($result = $this->loader->loadClass($class)) {
            \JLoader::applyAliasFor($class);
        }


        return $result;
    }
}
