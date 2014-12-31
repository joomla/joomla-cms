<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Composer\Autoload;

defined('_JEXEC') or die;

// We have to manually require the base ClassLoader as the autoloader isn't loaded yet, but only if it doesn't exist
if (!class_exists('Composer\\Autoload\\ClassLoader'))
{
	require_once __DIR__ . '/vendor/composer/ClassLoader.php';
}

/**
 * Extended Composer ClassLoader for Joomla!
 *
 * For backward compatibility due to class aliasing in the CMS, the loadClass() method was modified to call
 * the JLoader::applyAliasFor() method.
 *
 * @author  Nicholas Dionysopoulos
 * @since   3.4
 */
class ClassLoaderJoomla extends ClassLoader
{
	/**
	 * Loads the given class or interface.
	 *
	 * @param   string  $class  The name of the class
	 *
	 * @return  bool|null True if loaded, null otherwise
	 *
	 * @since   3.4
	 */
	public function loadClass($class)
	{
		if ($file = $this->findFile($class))
		{
			includeFile($file);

			\JLoader::applyAliasFor($class);

			return true;
		}
	}
}
