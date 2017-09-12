<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Plugin;

defined('_JEXEC') or die;

use Joomla\Event\DispatcherInterface;

/**
 * Default factory for creating Plugin objects
 *
 * @since  __DEPLOY_VERSION__
 */
class PluginFactory implements PluginFactoryInterface
{
	/**
	 * Event Dispatcher
	 *
	 * @var    DispatcherInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $dispatcher = null;

	/**
	 * Root folder of the plugins
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $rootFolder = null;

	/**
	 * The loaded plugins.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $loadedPlugins = array();

	/**
	 * Constructor
	 *
	 * @param   string               $rootFolder  The root folder to look for plugins
	 * @param   DispatcherInterface  $dispatcher  The dispatcher
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($rootFolder, DispatcherInterface $dispatcher)
	{
		$this->rootFolder = $rootFolder;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Method to get an instance of a plugin.
	 *
	 * The plugins are cached, means a second call with the same
	 * parameters, returns the same plugin object as on the first call.
	 *
	 * @param   string  $name  The name of the plugin
	 * @param   string  $type  The name of the type
	 *
	 * @return  CMSPlugin  Plugin instance.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getPlugin($name, $type)
	{
		// Cleanup the parameters
		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$name = preg_replace('/[^A-Z0-9_\.-]/i', '', $name);

		// The path of the plugins
		$path = $this->rootFolder . '/' . $type . '/' . $name . '/' . $name . '.php';

		// Check if the plugin is already loaded
		if (isset($this->loadedPlugins[$path]))
		{
			return $this->loadedPlugins[$path];
		}

		// Check if the path exists
		if (!file_exists($path))
		{
			$this->loadedPlugins[$path] = false;

			return false;
		}

		// Include the plugin
		require_once $path;

		// Compile the class name
		$className = 'Plg' . str_replace('-', '', $type) . $name;

		// Check if the class exists
		if (!class_exists($className))
		{
			$this->loadedPlugins[$path] = false;

			return false;
		}

		// Get the plugin object
		$plugin = PluginHelper::getPlugin($type, $name);

		// Instantiate and register the plugin.
		$this->loadedPlugins[$path] = new $className($this->dispatcher, (array) $plugin);

		return $this->loadedPlugins[$path];
	}
}
