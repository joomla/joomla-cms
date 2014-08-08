<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
	 * @param   string       $type        The plugin type, relates to the sub-directory in the plugins directory.
	 * @param   string       $plugin      The plugin name.
	 * @param   boolean      $autocreate  Autocreate the plugin.
	 * @param   JDispatcher  $dispatcher  Optionally allows the plugin to use a different dispatcher.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public static function importPlugin($type, $plugin = null, $autocreate = true, $dispatcher = null)
	{
		static $loaded = array();

		// check for the default args, if so we can optimise cheaply
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
	 * @param   JPlugin      &$plugin     The plugin.
	 * @param   boolean      $autocreate  True to autocreate.
	 * @param   JDispatcher  $dispatcher  Optionally allows the plugin to use a different dispatcher.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	protected static function _import(&$plugin, $autocreate = true, $dispatcher = null)
	{
		static $paths = array();

		$plugin->type = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->type);
		$plugin->name = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->name);

		$legacypath = JPATH_PLUGINS . '/' . $plugin->type . '/' . $plugin->name . '.php';
		$path = JPATH_PLUGINS . '/' . $plugin->type . '/' . $plugin->name . '/' . $plugin->name . '.php';

		if (!isset($paths[$path]) || !isset($paths[$legacypath]))
		{
			$pathExists = file_exists($path);
			if ($pathExists || file_exists($legacypath))
			{
				$path = $pathExists ? $path : $legacypath;

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
						$dispatcher = JDispatcher::getInstance();
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

		if (!(self::$plugins = $cache->get($levels)))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('folder AS type, element AS name, params')
				->from('#__extensions')
				->where('enabled >= 1')
				->where('type =' . $db->Quote('plugin'))
				->where('state >= 0')
				->where('access IN (' . $levels . ')')
				->order('ordering');

			self::$plugins = $db->setQuery($query)->loadObjectList();

			if ($error = $db->getErrorMsg())
			{
				JError::raiseWarning(500, $error);
				return false;
			}

			$cache->store(self::$plugins, $levels);
		}

		return self::$plugins;
	}
}
