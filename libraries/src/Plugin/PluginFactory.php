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
	private $dispatcher = null;
	private $loadedPlugins = array();

	public function __construct(DispatcherInterface $dispatcher)
	{
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
		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$name = preg_replace('/[^A-Z0-9_\.-]/i', '', $name);

		$path = JPATH_PLUGINS . '/' . $type . '/' . $name . '/' . $name . '.php';

		if (isset($this->loadedPlugins[$path]))
		{
			return $this->loadedPlugins[$path];
		}

		if (!file_exists($path))
		{
			$this->loadedPlugins[$path] = false;

			return false;
		}

		require_once $path;

		$className = 'Plg' . str_replace('-', '', $type) . $name;

		if (!class_exists($className))
		{
			$this->loadedPlugins[$path] = false;

			return false;
		}


		$plugin = PluginHelper::getPlugin($type, $name);

		// Instantiate and register the plugin.
		$this->loadedPlugins[$path] = new $className($this->dispatcher, (array) $plugin);

		return $this->loadedPlugins[$path];
	}
}
