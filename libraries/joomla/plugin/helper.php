<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Plugin helper class
 *
 * @package     Joomla.Platform
 * @subpackage  Plugin
 * @since       11.1
 */
abstract class JPluginHelper
{
	/**
	 * A persistent cache of the loaded plugins.
	 *
	 * @var    array
	 * @since  11.3
	 */
	protected static $plugins = null;

	/**
	 * Get the path to a layout from a Plugin
	 *
	 * @param   string  $type    Plugin type
	 * @param   string  $name    Plugin name
	 * @param   string  $layout  Layout name
	 *
	 * @return  string  Layout path
	 *
	 * @since   12.2
	 */
	public static function getLayoutPath($type, $name, $layout = 'default')
	{
		$template = JFactory::getApplication()->getTemplate();
		$defaultLayout = $layout;

		if (strpos($layout, ':') !== false)
		{
			// Get the template and file name from the string
			$temp = explode(':', $layout);
			$template = ($temp[0] == '_') ? $template : $temp[0];
			$layout = $temp[1];
			$defaultLayout = ($temp[1]) ? $temp[1] : 'default';
		}

		// Build the template and base path for the layout
		$tPath = JPATH_THEMES . '/' . $template . '/html/plg_' . $type . '_' . $name . '/' . $layout . '.php';
		$bPath = JPATH_BASE . '/plugins/' . $type . '/' . $name . '/tmpl/' . $defaultLayout . '.php';
		$dPath = JPATH_BASE . '/plugins/' . $type . '/' . $name . '/tmpl/default.php';

		// If the template has a layout override use it
		if (file_exists($tPath))
		{
			return $tPath;
		}
		elseif (file_exists($bPath))
		{
			return $bPath;
		}
		else
		{
			return $dPath;
		}
	}

	/**
	 * Get the plugin data of a specific type if no specific plugin is specified
	 * otherwise only the specific plugin data is returned.
	 *
	 * @param   string  $type    The plugin type, relates to the sub-directory in the plugins directory.
	 * @param   string  $plugin  The plugin name.
	 *
	 * @return  mixed  An array of plugin data objects, or a plugin data object.
	 *
	 * @since   11.1
	 */
	public static function getPlugin($type, $plugin = null)
	{
		$result = array();
		$plugins = self::_load();

		// Find the correct plugin(s) to return.
		if (!$plugin)
		{
			foreach ($plugins as $p)
			{
				// Is this the right plugin?
				if ($p->type == $type)
				{
					$result[] = $p;
				}
			}
		}
		else
		{
			foreach ($plugins as $p)
			{
				// Is this plugin in the right group?
				if ($p->type == $type && $p->name == $plugin)
				{
					$result = $p;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Checks if a plugin is enabled.
	 *
	 * @param   string  $type    The plugin type, relates to the sub-directory in the plugins directory.
	 * @param   string  $plugin  The plugin name.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public static function isEnabled($type, $plugin = null)
	{
		$result = self::getPlugin($type, $plugin);

		return (!empty($result));
	}

	/**
	 * Loads all the plugin files for a particular type if no specific plugin is specified
	 * otherwise only the specific plugin is loaded.
	 *
	 * @param   string            $type        The plugin type, relates to the sub-directory in the plugins directory.
	 * @param   string            $plugin      The plugin name.
	 * @param   boolean           $autocreate  Autocreate the plugin.
	 * @param   JEventDispatcher  $dispatcher  Optionally allows the plugin to use a different dispatcher.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public static function importPlugin($type, $plugin = null, $autocreate = true, JEventDispatcher $dispatcher = null)
	{
		static $loaded = array();

		// Check for the default args, if so we can optimise cheaply
		$defaults = false;

		if (is_null($plugin) && $autocreate == true && is_null($dispatcher))
		{
			$defaults = true;
		}

		if (!isset($loaded[$type]) || !$defaults)
		{
			$results = null;

			// Load the plugins from the database.
			$plugins = self::_load();

			// Get the specified plugin(s).
			for ($i = 0, $t = count($plugins); $i < $t; $i++)
			{
				if ($plugins[$i]->type == $type && ($plugin === null || $plugins[$i]->name == $plugin))
				{
					self::_import($plugins[$i], $autocreate, $dispatcher);
					$results = true;
				}
			}

			// Bail out early if we're not using default args
			if (!$defaults)
			{
				return $results;
			}
			$loaded[$type] = $results;
		}

		return $loaded[$type];
	}

	/**
	 * Loads the plugin file.
	 *
	 * @param   object            $plugin      The plugin.
	 * @param   boolean           $autocreate  True to autocreate.
	 * @param   JEventDispatcher  $dispatcher  Optionally allows the plugin to use a different dispatcher.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected static function _import($plugin, $autocreate = true, JEventDispatcher $dispatcher = null)
	{
		static $paths = array();

		$plugin->type = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->type);
		$plugin->name = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->name);

		$path = JPATH_PLUGINS . '/' . $plugin->type . '/' . $plugin->name . '/' . $plugin->name . '.php';

		if (!isset($paths[$path]))
		{
			if (file_exists($path))
			{
				if (!isset($paths[$path]))
				{
					require_once $path;
				}
				$paths[$path] = true;

				if ($autocreate)
				{
					// Makes sure we have an event dispatcher
					if (!is_object($dispatcher))
					{
						$dispatcher = JEventDispatcher::getInstance();
					}

					$className = 'plg' . $plugin->type . $plugin->name;

					if (class_exists($className))
					{
						// Load the plugin from the database.
						if (!isset($plugin->params))
						{
							// Seems like this could just go bye bye completely
							$plugin = self::getPlugin($plugin->type, $plugin->name);
						}

						// Instantiate and register the plugin.
						new $className($dispatcher, (array) ($plugin));
					}
				}
			}
			else
			{
				$paths[$path] = false;
			}
		}
	}

	/**
	 * Loads the published plugins.
	 *
	 * @return  array  An array of published plugins
	 *
	 * @since   11.1
	 */
	protected static function _load()
	{
		if (self::$plugins !== null)
		{
			return self::$plugins;
		}

		$user = JFactory::getUser();
		$cache = JFactory::getCache('com_plugins', '');

		$levels = implode(',', $user->getAuthorisedViewLevels());

		if (!self::$plugins = $cache->get($levels))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('folder AS type, element AS name, params')
				->from('#__extensions')
				->where('enabled >= 1')
				->where('type =' . $db->quote('plugin'))
				->where('state >= 0')
				->where('access IN (' . $levels . ')')
				->order('ordering');

			self::$plugins = $db->setQuery($query)->loadObjectList();

			$cache->store(self::$plugins, $levels);
		}

		return self::$plugins;
	}
}
