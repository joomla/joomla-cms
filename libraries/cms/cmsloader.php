<?php
/**
 * @package     Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Static class to handle loading of CMS libraries.
 *
 * @package  Joomla.Libraries
 * @since    2.5
 */
abstract class JCmsLoader extends JLoader
{
	/**
	 * Method to setup the autoloaders for the Joomla CMS. Since the SPL autoloaders are
	 * called in a queue we will add our explicit, class-registration based loader first, then
	 * fall back on the autoloader based on conventions. This will allow people to register a
	 * class in a specific location and override platform libraries as was previously possible.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function setup()
	{
		spl_autoload_register(array('JCmsLoader', '_autoload'));
	}

	/**
	 * Autoload a Joomla library class based on name.
	 *
	 * @param   string   $class  The class to be loaded.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	private static function _autoload($class)
	{
		// Only attempt autoloading if we are dealing with a Joomla Platform class.
		if ($class[0] == 'J') {
			// Split the class name (without the J) into parts separated by camelCase.
			$parts = preg_split('/(?<=[a-z])(?=[A-Z])/x', substr($class, 1));

			// If there is only one part we want to duplicate that part for generating the path.
			$parts = (count($parts) === 1) ? array($parts[0], $parts[0]) : $parts;

			// Generate the path based on the class name parts.
			$path = JPATH_PLATFORM . '/cms/' . implode('/', array_map('strtolower', $parts)) . '.php';

			// Load the file if it exists.
			if (file_exists($path)) {
				include $path;
			}
		}
	}
}
