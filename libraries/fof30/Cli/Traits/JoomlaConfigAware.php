<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Cli\Traits;

defined('_JEXEC') || die;

/**
 * Allows the CLI application to use the Joomla Global Configuration parameters as its own configuration.
 *
 * @package FOF30\Cli\Traits
 */
trait JoomlaConfigAware
{
	/**
	 * Method to load the application configuration, returning it as an object or array
	 *
	 * This can be overridden in subclasses if you don't want to fetch config from a PHP class file.
	 *
	 * @param   string|null  $file       The filepath to the file containing the configuration class. Default: Joomla's
	 *                                   configuration.php
	 * @param   string       $className  The name of the PHP class holding the configuration. Default: JConfig
	 *
	 * @return  mixed  Either an array or object to be loaded into the configuration object.
	 */
	protected function fetchConfigurationData($file = null, $className = 'JConfig')
	{
		// Set the configuration file name.
		if (empty($file))
		{
			$file = JPATH_BASE . '/configuration.php';
		}

		// Import the configuration file.
		if (!is_file($file))
		{
			return [];
		}

		include_once $file;

		// Instantiate the configuration object.
		if (!class_exists('JConfig'))
		{
			return [];
		}

		return new $className();
	}

}
