<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Plugin;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Plugin helper class
 *
 * @since  1.5
 */
abstract class PluginHelper
{
	/**
	 * A persistent cache of the loaded plugins.
	 *
	 * @var    array
	 * @since  1.7
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
	 * @since   3.0
	 */
	public static function getLayoutPath($type, $name, $layout = 'default')
	{
		$template = \JFactory::getApplication()->getTemplate();
		$defaultLayout = $layout;

		if (strpos($layout, ':') !== false)
		{
			// Get the template and file name from the string
			$temp = explode(':', $layout);
			$template = $temp[0] === '_' ? $template : $temp[0];
			$layout = $temp[1];
			$defaultLayout = $temp[1] ?: 'default';
		}

		// Build the template and base path for the layout
		$tPath = JPATH_THEMES . '/' . $template . '/html/plg_' . $type . '_' . $name . '/' . $layout . '.php';
		$bPath = JPATH_PLUGINS . '/' . $type . '/' . $name . '/tmpl/' . $defaultLayout . '.php';
		$dPath = JPATH_PLUGINS . '/' . $type . '/' . $name . '/tmpl/default.php';

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
	 * @param   string  $type    The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string  $plugin  The plugin name.
	 *
	 * @return  mixed  An array of plugin data objects, or a plugin data object.
	 *
	 * @since   1.5
	 */
	public static function getPlugin($type, $plugin = null)
	{
		$result = array();
		$plugins = static::load();

		// Find the correct plugin(s) to return.
		if (!$plugin)
		{
			foreach ($plugins as $p)
			{
				// Is this the right plugin?
				if ($p->type === $type)
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
				if ($p->type === $type && $p->name === $plugin)
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
	 * @param   string  $type    The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string  $plugin  The plugin name.
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public static function isEnabled($type, $plugin = null)
	{
		$result = static::getPlugin($type, $plugin);

		return !empty($result);
	}

	/**
	 * Loads all the plugin files for a particular type if no specific plugin is specified
	 * otherwise only the specific plugin is loaded.
	 *
	 * @param   string             $type        The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string             $plugin      The plugin name.
	 * @param   boolean            $autocreate  Autocreate the plugin.
	 * @param   \JEventDispatcher  $dispatcher  Optionally allows the plugin to use a different dispatcher.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.5
	 */
	public static function importPlugin($type, $plugin = null, $autocreate = true, \JEventDispatcher $dispatcher = null)
	{
		static $loaded = array();

		// Check for the default args, if so we can optimise cheaply
		$defaults = false;

		if ($plugin === null && $autocreate === true && $dispatcher === null)
		{
			$defaults = true;
		}

		// Ensure we have a dispatcher now so we can correctly track the loaded plugins
		$dispatcher = $dispatcher ?: \JEventDispatcher::getInstance();

		// Get the dispatcher's hash to allow plugins to be registered to unique dispatchers
		$dispatcherHash = spl_object_hash($dispatcher);

		if (!isset($loaded[$dispatcherHash]))
		{
			$loaded[$dispatcherHash] = array();
		}

		if (!$defaults || !isset($loaded[$dispatcherHash][$type]))
		{
			$results = null;

			// Load the plugins from the database.
			$plugins = static::load();

			// Get the specified plugin(s).
			for ($i = 0, $t = count($plugins); $i < $t; $i++)
			{
				if ($plugins[$i]->type === $type && ($plugin === null || $plugins[$i]->name === $plugin))
				{
					static::import($plugins[$i], $autocreate, $dispatcher);
					$results = true;
				}
			}

			// Bail out early if we're not using default args
			if (!$defaults)
			{
				return $results;
			}

			$loaded[$dispatcherHash][$type] = $results;
		}

		return $loaded[$dispatcherHash][$type];
	}

	/**
	 * Loads the plugin file.
	 *
	 * @param   object             $plugin      The plugin.
	 * @param   boolean            $autocreate  True to autocreate.
	 * @param   \JEventDispatcher  $dispatcher  Optionally allows the plugin to use a different dispatcher.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use PluginHelper::import() instead
	 */
	protected static function _import($plugin, $autocreate = true, \JEventDispatcher $dispatcher = null)
	{
		static::import($plugin, $autocreate, $dispatcher);
	}

	/**
	 * Loads the plugin file.
	 *
	 * @param   object             $plugin      The plugin.
	 * @param   boolean            $autocreate  True to autocreate.
	 * @param   \JEventDispatcher  $dispatcher  Optionally allows the plugin to use a different dispatcher.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected static function import($plugin, $autocreate = true, \JEventDispatcher $dispatcher = null)
	{
		static $paths = array();

		// Ensure we have a dispatcher now so we can correctly track the loaded paths
		$dispatcher = $dispatcher ?: \JEventDispatcher::getInstance();

		// Get the dispatcher's hash to allow paths to be tracked against unique dispatchers
		$dispatcherHash = spl_object_hash($dispatcher);

		if (!isset($paths[$dispatcherHash]))
		{
			$paths[$dispatcherHash] = array();
		}

		$plugin->type = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->type);
		$plugin->name = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->name);

		$path = JPATH_PLUGINS . '/' . $plugin->type . '/' . $plugin->name . '/' . $plugin->name . '.php';

		if (!isset($paths[$dispatcherHash][$path]))
		{
			if (file_exists($path))
			{
				if (!isset($paths[$dispatcherHash][$path]))
				{
					require_once $path;
				}

				$paths[$dispatcherHash][$path] = true;

				if ($autocreate)
				{
					$className = 'Plg' . str_replace('-', '', $plugin->type) . $plugin->name;

					if ($plugin->type == 'editors-xtd')
					{
						// This type doesn't follow the convention
						$className = 'PlgEditorsXtd' . $plugin->name;

						if (!class_exists($className))
						{
							$className = 'PlgButton' . $plugin->name;
						}
					}

					if (class_exists($className))
					{
						// Load the plugin from the database.
						if (!isset($plugin->params))
						{
							// Seems like this could just go bye bye completely
							$plugin = static::getPlugin($plugin->type, $plugin->name);
						}

						// Instantiate and register the plugin.
						new $className($dispatcher, (array) $plugin);
					}
				}
			}
			else
			{
				$paths[$dispatcherHash][$path] = false;
			}
		}
	}

	/**
	 * Loads the published plugins.
	 *
	 * @return  array  An array of published plugins
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use PluginHelper::load() instead
	 */
	protected static function _load()
	{
		return static::load();
	}

	/**
	 * Loads the published plugins.
	 *
	 * @return  array  An array of published plugins
	 *
	 * @since   3.2
	 */
	protected static function load()
	{
		if (static::$plugins !== null)
		{
			return static::$plugins;
		}

		$levels = implode(',', \JFactory::getUser()->getAuthorisedViewLevels());

		/** @var \JCacheControllerCallback $cache */
		$cache = \JFactory::getCache('com_plugins', 'callback');

		$loader = function () use ($levels)
		{
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true)
				->select(
					$db->quoteName(
						array(
							'folder',
							'element',
							'params',
							'extension_id'
						),
						array(
							'type',
							'name',
							'params',
							'id'
						)
					)
				)
				->from('#__extensions')
				->where('enabled = 1')
				->where('type = ' . $db->quote('plugin'))
				->where('state IN (0,1)')
				->where('access IN (' . $levels . ')')
				->order('ordering');
			$db->setQuery($query);

			return $db->loadObjectList();
		};

		try
		{
			static::$plugins = $cache->get($loader, array(), md5($levels), false);
		}
		catch (\JCacheException $cacheException)
		{
			static::$plugins = $loader();
		}

		return static::$plugins;
	}

	/**
	 * Pseudo Lock the row.
	 *
	 * @param   string  $name      The plugin name.
	 * @param   string  $type      The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string  &$lastrun  The plugin last run time.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function lock($name, $type, &$lastrun)
	{
		// Prevent multiple execution
		$db = \JFactory::getDbo();

		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->qn('checked_out_time') . ' = ' . $db->q('1918-01-01 00:00:00'))
			->where($db->quoteName('element') . ' = ' . $db->quote($name))
			->where($db->quoteName('folder') . ' = ' . $db->quote($type))
			->where($db->quoteName('checked_out_time') . ' != ' . $db->q('1918-01-01 00:00:00'));

		try
		{
			// Lock the tables to prevent multiple plugin executions causing a race condition
			$db->lockTable('#__extensions');
		}
		catch (JDatabaseException $e)
		{
			// If we can't lock the tables it's too risky to continue execution
			return false;
		}

		$db->setQuery($query);

		try
		{
			// Update the plugin parameters
			$db->execute();
		
		}
		catch (JDatabaseException $exc)
		{
			// If we failed to execute
			$db->unlockTables();
			return false;
		}

		$result = (int) $db->getAffectedRows();
		if ($result === 0)
		{
			return false;
		}

		try
		{
			// Unlock the tables after writing
			$db->unlockTables();
		}
		catch (JDatabaseException $e)
		{
			// If we can't unlock the tables assume we have somehow failed
			return false;
		}
			
		$query = $db->getQuery(true)
			->select($db->quoteName('params'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' = ' . $db->quote($name))
			->where($db->quoteName('folder') . ' = ' . $db->quote($type))
			->where($db->quoteName('checked_out_time') . ' = ' . $db->q('1918-01-01 00:00:00'));

		$db->setQuery($query);
		$params = $db->loadColumn();
		$taskParams = json_decode($params[0], true);
		$lastrun = $taskParams['lastrun'];

		return true;
	}

	/**
	 * Pseudo unLock the row.
	 *
	 * @param   string  $name    The plugin name.
	 * @param   string  $type    The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string  $unlock  The unlock type.
	 *
	 * @return  string  The task id.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function unLock($name, $type, $unlock = true)
	{

		$taskid = null;
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('params'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' = ' . $db->quote($name))
			->where($db->quoteName('folder') . ' = ' . $db->quote($type))
			->where($db->quoteName('checked_out_time') . ' = ' . $db->q('1918-01-01 00:00:00'));

		$db->setQuery($query);
		$params = $db->loadColumn();

		if ($unlock)
		{
			// Update last run and taskid 
			$taskParams = json_decode($params[0], true);
			$taskid = $taskParams['taskid'];

			$taskid++;
			$registry = new Registry($taskParams);
			$registry->set('taskid', $taskid);
			$registry->set('lastrun', time());

			$query = $db->getQuery(true)
				->update($db->quoteName('#__extensions'))
				->set($db->quoteName('params') . ' = ' . $db->quote($registry->toString('JSON')))
				->set($db->qn('checked_out_time') . ' = ' . $db->quote(\JFactory::getDate()->toSql()))
				->where($db->quoteName('element') . ' = ' . $db->quote($name))
				->where($db->quoteName('folder') . ' = ' . $db->quote($type));
		}  
		else
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__extensions'))
				->set($db->qn('checked_out_time') . ' = ' . $db->quote(\JFactory::getDate()->toSql()))
				->where($db->quoteName('element') . ' = ' . $db->quote($name))
				->where($db->quoteName('folder') . ' = ' . $db->quote($type));
		}

		try
		{
			// Lock the tables to prevent multiple plugin executions causing a race condition
			$db->lockTable('#__extensions');
		}
		catch (JDatabaseException $e)
		{
			// If we can't lock the tables it's too risky to continue execution
			return false;
		}

		try
		{
			// Update the plugin parameters
			$result = $db->setQuery($query)->execute();
		}
		catch (JDatabaseException $exc)
		{
			// If we failed to execute
			$db->unlockTables();
			return false;
		}

		try
		{
			// Unlock the tables after writing
			$db->unlockTables();
		}
		catch (JDatabaseException $e)
		{
			// If we can't unlock the tables assume we have somehow failed
			return false;
		}

		return $taskid;

	}
}
