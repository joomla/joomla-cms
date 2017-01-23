<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Application;

use Joomla\Registry\Registry;

/**
 * Trait for application classes which can automatically retrieve the global configuration
 *
 * @since  4.0
 */
trait Autoconfigurable
{
	/**
	 * Method to load a PHP configuration class file based on convention and return the instantiated data object.
	 *
	 * @param   string  $file   The path and filename of the configuration file. If not provided, configuration.php
	 *                          in JPATH_CONFIGURATION will be used.
	 * @param   string  $class  The class name to instantiate.
	 *
	 * @return  mixed   Either an array or object to be loaded into the configuration object.
	 *
	 * @since   4.0
	 * @throws  \RuntimeException
	 */
	protected function fetchConfigurationData($file = '', $class = 'JConfig')
	{
		// Instantiate variables.
		$config = [];

		if (empty($file))
		{
			$file = JPATH_CONFIGURATION . '/configuration.php';

			// Applications can choose not to have any configuration data by not implementing this method and not having a config file.
			if (!file_exists($file))
			{
				$file = '';
			}
		}

		if (!empty($file))
		{
			\JLoader::register($class, $file);

			if (!class_exists($class))
			{
				throw new \RuntimeException('Configuration class does not exist.');
			}

			$config = new $class;
		}

		return $config;
	}

	/**
	 * Retrieve the application configuration object.
	 *
	 * @return  Registry
	 *
	 * @since   4.0
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Load an object or array into the application configuration object.
	 *
	 * @param   mixed  $data  Either an array or object to be loaded into the configuration object.
	 *
	 * @return  $this
	 *
	 * @since   4.0
	 */
	public function loadConfiguration($data)
	{
		// Load the data into the configuration object.
		if (is_array($data))
		{
			$this->getConfig()->loadArray($data);
		}
		elseif (is_object($data))
		{
			$this->getConfig()->loadObject($data);
		}

		return $this;
	}
}
