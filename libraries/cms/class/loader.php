<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Class
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Composer\Autoload\ClassLoader;

/**
 * Decorate Composer ClassLoader for Joomla!
 *
 * For backward compatibility due to class aliasing in the CMS, the loadClass() method was modified to call
 * the JLoader::applyAliasFor() method.
 *
 * @since  3.4
 */
class JClassLoader
{
	/**
	 * The composer class loader
	 *
	 * @var    ClassLoader
	 * @since  3.4
	 */
	private $loader;

	/**
	 * Constructor
	 *
	 * @param   ClassLoader  $loader  Composer autoloader
	 *
	 * @since   3.4
	 */
	public function __construct(ClassLoader $loader)
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
		// Namespaced class
		if ($this->loadFromJloader($class))
		{
			return true;
		}

		// None namespaced class
		if ($this->loadFromJloader('\\' . $class))
		{
			return true;
		}

		if ($result = $this->loader->loadClass($class))
		{
			JLoader::applyAliasFor($class);
		}

		return $result;
	}

	/**
	 * Loads the class from JLoader when possible.
	 *
	 * @param   string  $class  The name of the class
	 *
	 * @return  boolean  True if loaded, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function loadFromJloader($class)
	{
		// Check if JLoader is able to load the class
		if (!key_exists(strtolower($class), JLoader::getClassList()))
		{
			return false;
		}

		// Load the class
		JLoader::load($class);

		JLoader::applyAliasFor($class);
		return true;
	}
}
