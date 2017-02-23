<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Module
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Module helper class
 *
 * @since  1.5
 */
abstract class JModuleHelper
{
	/**
	 * Get module by name (real, eg 'Breadcrumbs' or folder, eg 'mod_breadcrumbs')
	 *
	 * @param   string  $name   The name of the module
	 * @param   string  $title  The title of the module, optional
	 *
	 * @return  stdClass  The Module object
	 *
	 * @since   1.5
	 */
	public static function &getModule($name, $title = null)
	{
		$result = null;
		$modules =& static::load();
		$total = count($modules);

		for ($i = 0; $i < $total; $i++)
		{
			// Match the name of the module
			if ($modules[$i]->name == $name || $modules[$i]->module == $name)
			{
				// Match the title if we're looking for a specific instance of the module
				if (!$title || $modules[$i]->title == $title)
				{
					// Found it
					$result = &$modules[$i];
					break;
				}
			}
		}

		// If we didn't find it, and the name is mod_something, create a dummy object
		if (is_null($result) && substr($name, 0, 4) == 'mod_')
		{
			$result            = new stdClass;
			$result->id        = 0;
			$result->title     = '';
			$result->module    = $name;
			$result->position  = '';
			$result->content   = '';
			$result->showtitle = 0;
			$result->control   = '';
			$result->params    = '';
		}

		return $result;
	}

	/**
	 * Get modules by position
	 *
	 * @param   string  $position  The position of the module
	 *
	 * @return  array  An array of module objects
	 *
	 * @since   1.5
	 */
	public static function &getModules($position)
	{
		$position = strtolower($position);
		$result = array();
		$input  = JFactory::getApplication()->input;

		$modules =& static::load();

		$total = count($modules);

		for ($i = 0; $i < $total; $i++)
		{
			if ($modules[$i]->position == $position)
			{
				$result[] = &$modules[$i];
			}
		}

		if (count($result) == 0)
		{
			if ($input->getBool('tp') && JComponentHelper::getParams('com_templates')->get('template_positions_display'))
			{
				$result[0] = static::getModule('mod_' . $position);
				$result[0]->title = $position;
				$result[0]->content = $position;
				$result[0]->position = $position;
			}
		}

		return $result;
	}

	/**
	 * Checks if a module is enabled. A given module will only be returned
	 * if it meets the following criteria: it is enabled, it is assigned to
	 * the current menu item or all items, and the user meets the access level
	 * requirements.
	 *
	 * @param   string  $module  The module name
	 *
	 * @return  boolean See description for conditions.
	 *
	 * @since   1.5
	 */
	public static function isEnabled($module)
	{
		$result = static::getModule($module);

		return !is_null($result) && $result->id !== 0;
	}

	/**
	 * Render the module.
	 *
	 * @param   object  $module   A module object.
	 * @param   array   $attribs  An array of attributes for the module (probably from the XML).
	 *
	 * @return  string  The HTML content of the module output.
	 *
	 * @since   1.5
	 */
	public static function renderModule($module, $attribs = array())
	{
		static $chrome;

		$app = JFactory::getApplication();

		// Check that $module is a valid module object
		if (!is_object($module) || !isset($module->module) || !isset($module->params))
		{
			if (JDEBUG)
			{
				JLog::addLogger(array('text_file' => 'jmodulehelper.log.php'), JLog::ALL, array('modulehelper'));
				$app->getLogger()->debug(
					__METHOD__ . '() - The $module parameter should be a module object.',
					array('category' => 'modulehelper')
				);
			}

			return;
		}

		if (JDEBUG)
		{
			JProfiler::getInstance('Application')->mark('beforeRenderModule ' . $module->module . ' (' . $module->title . ')');
		}

		// Record the scope.
		$scope = $app->scope;

		// Set scope to component name
		$app->scope = $module->module;

		// Get module parameters
		$params = new Registry($module->params);

		// Get the template
		$template = $app->getTemplate();

		// Get module path
		$module->module = preg_replace('/[^A-Z0-9_\.-]/i', '', $module->module);
		$path = JPATH_BASE . '/modules/' . $module->module . '/' . $module->module . '.php';

		// Load the module
		if (file_exists($path))
		{
			$lang = JFactory::getLanguage();

			$coreLanguageDirectory      = JPATH_BASE;
			$extensionLanguageDirectory = dirname($path);

			$langPaths = $lang->getPaths();

			// Only load the module's language file if it hasn't been already
			if (!$langPaths || (!isset($langPaths[$coreLanguageDirectory]) && !isset($langPaths[$extensionLanguageDirectory])))
			{
				// 1.5 or Core then 1.6 3PD
				$lang->load($module->module, $coreLanguageDirectory, null, false, true) ||
					$lang->load($module->module, $extensionLanguageDirectory, null, false, true);
			}

			$content = '';
			ob_start();
			include $path;
			$module->content = ob_get_contents() . $content;
			ob_end_clean();
		}

		// Load the module chrome functions
		if (!$chrome)
		{
			$chrome = array();
		}

		include_once JPATH_THEMES . '/system/html/modules.php';
		$chromePath = JPATH_THEMES . '/' . $template . '/html/modules.php';

		if (!isset($chrome[$chromePath]))
		{
			if (file_exists($chromePath))
			{
				include_once $chromePath;
			}

			$chrome[$chromePath] = true;
		}

		// Check if the current module has a style param to override template module style
		$paramsChromeStyle = $params->get('style');

		if ($paramsChromeStyle)
		{
			$attribs['style'] = preg_replace('/^(system|' . $template . ')\-/i', '', $paramsChromeStyle);
		}

		// Make sure a style is set
		if (!isset($attribs['style']))
		{
			$attribs['style'] = 'none';
		}

		// Dynamically add outline style
		if ($app->input->getBool('tp') && JComponentHelper::getParams('com_templates')->get('template_positions_display'))
		{
			$attribs['style'] .= ' outline';
		}

		// If the $module is nulled it will return an empty content, otherwise it will render the module normally.
		$app->triggerEvent('onRenderModule', array(&$module, &$attribs));

		if (is_null($module) || !isset($module->content))
		{
			return '';
		}

		foreach (explode(' ', $attribs['style']) as $style)
		{
			$chromeMethod = 'modChrome_' . $style;

			// Apply chrome and render module
			if (function_exists($chromeMethod))
			{
				$module->style = $attribs['style'];

				ob_start();
				$chromeMethod($module, $params, $attribs);
				$module->content = ob_get_contents();
				ob_end_clean();
			}
		}

		// Revert the scope
		$app->scope = $scope;

		$app->triggerEvent('onAfterRenderModule', array(&$module, &$attribs));

		if (JDEBUG)
		{
			JProfiler::getInstance('Application')->mark('afterRenderModule ' . $module->module . ' (' . $module->title . ')');
		}

		return $module->content;
	}

	/**
	 * Get the path to a layout for a module
	 *
	 * @param   string  $module  The name of the module
	 * @param   string  $layout  The name of the module layout. If alternative layout, in the form template:filename.
	 *
	 * @return  string  The path to the module layout
	 *
	 * @since   1.5
	 */
	public static function getLayoutPath($module, $layout = 'default')
	{
		$template = JFactory::getApplication()->getTemplate();
		$defaultLayout = $layout;

		if (strpos($layout, ':') !== false)
		{
			// Get the template and file name from the string
			$temp = explode(':', $layout);
			$template = ($temp[0] == '_') ? $template : $temp[0];
			$layout = $temp[1];
			$defaultLayout = $temp[1] ?: 'default';
		}

		// Build the template and base path for the layout
		$tPath = JPATH_THEMES . '/' . $template . '/html/' . $module . '/' . $layout . '.php';
		$bPath = JPATH_BASE . '/modules/' . $module . '/tmpl/' . $defaultLayout . '.php';
		$dPath = JPATH_BASE . '/modules/' . $module . '/tmpl/default.php';

		// If the template has a layout override use it
		if (file_exists($tPath))
		{
			return $tPath;
		}

		if (file_exists($bPath))
		{
			return $bPath;
		}

		return $dPath;
	}

	/**
	 * Load published modules.
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	protected static function &load()
	{
		static $modules;

		if (isset($modules))
		{
			return $modules;
		}

		$app = JFactory::getApplication();

		$modules = null;

		$app->triggerEvent('onPrepareModuleList', array(&$modules));

		// If the onPrepareModuleList event returns an array of modules, then ignore the default module list creation
		if (!is_array($modules))
		{
			$modules = static::getModuleList();
		}

		$app->triggerEvent('onAfterModuleList', array(&$modules));

		$modules = static::cleanModuleList($modules);

		$app->triggerEvent('onAfterCleanModuleList', array(&$modules));

		return $modules;
	}

	/**
	 * Module list
	 *
	 * @return  array
	 */
	public static function getModuleList()
	{
		$app = JFactory::getApplication();
		$Itemid = $app->input->getInt('Itemid');
		$groups = implode(',', JFactory::getUser()->getAuthorisedViewLevels());
		$lang = JFactory::getLanguage()->getTag();
		$clientId = (int) $app->getClientId();

		// Build a cache ID for the resulting data object
		$cacheId = $groups . $clientId . (int) $Itemid;

		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params, mm.menuid')
			->from('#__modules AS m')
			->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = m.id')
			->where('m.published = 1')
			->join('LEFT', '#__extensions AS e ON e.element = m.module AND e.client_id = m.client_id')
			->where('e.enabled = 1');

		$date = JFactory::getDate();
		$now = $date->toSql();
		$nullDate = $db->getNullDate();
		$query->where('(m.publish_up = ' . $db->quote($nullDate) . ' OR m.publish_up <= ' . $db->quote($now) . ')')
			->where('(m.publish_down = ' . $db->quote($nullDate) . ' OR m.publish_down >= ' . $db->quote($now) . ')')
			->where('m.access IN (' . $groups . ')')
			->where('m.client_id = ' . $clientId)
			->where('(mm.menuid = ' . (int) $Itemid . ' OR mm.menuid <= 0)');

		// Filter by language
		if ($app->isClient('site') && $app->getLanguageFilter())
		{
			$query->where('m.language IN (' . $db->quote($lang) . ',' . $db->quote('*') . ')');
			$cacheId .= $lang . '*';
		}

		$query->order('m.position, m.ordering');

		// Set the query
		$db->setQuery($query);

		try
		{
			/** @var JCacheControllerCallback $cache */
			$cache = JFactory::getCache('com_modules', 'callback');

			$modules = $cache->get(array($db, 'loadObjectList'), array(), md5($cacheId), false);
		}
		catch (RuntimeException $e)
		{
			$app->getLogger()->warning(
				JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $e->getMessage()),
				array('category' => 'jerror')
			);

			return array();
		}

		return $modules;
	}

	/**
	 * Clean the module list
	 *
	 * @param   array  $modules  Array with module objects
	 *
	 * @return  array
	 */
	public static function cleanModuleList($modules)
	{
		// Apply negative selections and eliminate duplicates
		$Itemid = JFactory::getApplication()->input->getInt('Itemid');
		$negId = $Itemid ? -(int) $Itemid : false;
		$clean = array();
		$dupes = array();

		foreach ($modules as $i => $module)
		{
			// The module is excluded if there is an explicit prohibition
			$negHit = ($negId === (int) $module->menuid);

			if (isset($dupes[$module->id]))
			{
				// If this item has been excluded, keep the duplicate flag set,
				// but remove any item from the modules array.
				if ($negHit)
				{
					unset($clean[$module->id]);
				}

				continue;
			}

			$dupes[$module->id] = true;

			// Only accept modules without explicit exclusions.
			if ($negHit)
			{
				continue;
			}

			$module->name = substr($module->module, 4);
			$module->style = null;
			$module->position = strtolower($module->position);

			$clean[$module->id] = $module;
		}

		unset($dupes);

		// Return to simple indexing that matches the query order.
		return array_values($clean);
	}

	/**
	 * Module cache helper
	 *
	 * Caching modes:
	 * To be set in XML:
	 * 'static'      One cache file for all pages with the same module parameters
	 * 'oldstatic'   1.5 definition of module caching, one cache file for all pages
	 *               with the same module id and user aid,
	 * 'itemid'      Changes on itemid change, to be called from inside the module:
	 * 'safeuri'     Id created from $cacheparams->modeparams array,
	 * 'id'          Module sets own cache id's
	 *
	 * @param   object  $module        Module object
	 * @param   object  $moduleparams  Module parameters
	 * @param   object  $cacheparams   Module cache parameters - id or url parameters, depending on the module cache mode
	 *
	 * @return  string
	 *
	 * @see     JFilterInput::clean()
	 * @since   1.6
	 */
	public static function moduleCache($module, $moduleparams, $cacheparams)
	{
		if (!isset($cacheparams->modeparams))
		{
			$cacheparams->modeparams = null;
		}

		if (!isset($cacheparams->cachegroup))
		{
			$cacheparams->cachegroup = $module->module;
		}

		$user = JFactory::getUser();
		$conf = JFactory::getConfig();

		/** @var JCacheControllerCallback $cache */
		$cache = JFactory::getCache($cacheparams->cachegroup, 'callback');

		// Turn cache off for internal callers if parameters are set to off and for all logged in users
		if ($moduleparams->get('owncache', null) === '0' || $conf->get('caching') == 0 || $user->get('id'))
		{
			$cache->setCaching(false);
		}

		// Module cache is set in seconds, global cache in minutes, setLifeTime works in minutes
		$cache->setLifeTime($moduleparams->get('cache_time', $conf->get('cachetime') * 60) / 60);

		$wrkaroundoptions = array('nopathway' => 1, 'nohead' => 0, 'nomodules' => 1, 'modulemode' => 1, 'mergehead' => 1);

		$wrkarounds = true;
		$view_levels = md5(serialize($user->getAuthorisedViewLevels()));

		switch ($cacheparams->cachemode)
		{
			case 'id':
				$ret = $cache->get(
					array($cacheparams->class, $cacheparams->method),
					$cacheparams->methodparams,
					$cacheparams->modeparams,
					$wrkarounds,
					$wrkaroundoptions
				);
				break;

			case 'safeuri':
				$secureid = null;

				if (is_array($cacheparams->modeparams))
				{
					$input   = JFactory::getApplication()->input;
					$uri     = $input->getArray();
					$safeuri = new stdClass;
					$noHtmlFilter = JFilterInput::getInstance();

					foreach ($cacheparams->modeparams as $key => $value)
					{
						// Use int filter for id/catid to clean out spamy slugs
						if (isset($uri[$key]))
						{
							$safeuri->$key = $noHtmlFilter->clean($uri[$key], $value);
						}
					}
				}

				$secureid = md5(serialize(array($safeuri, $cacheparams->method, $moduleparams)));
				$ret = $cache->get(
					array($cacheparams->class, $cacheparams->method),
					$cacheparams->methodparams,
					$module->id . $view_levels . $secureid,
					$wrkarounds,
					$wrkaroundoptions
				);
				break;

			case 'static':
				$ret = $cache->get(
					array($cacheparams->class, $cacheparams->method),
					$cacheparams->methodparams,
					$module->module . md5(serialize($cacheparams->methodparams)),
					$wrkarounds,
					$wrkaroundoptions
				);
				break;

			// Provided for backward compatibility, not really useful.
			case 'oldstatic':
				$ret = $cache->get(
					array($cacheparams->class, $cacheparams->method),
					$cacheparams->methodparams,
					$module->id . $view_levels,
					$wrkarounds,
					$wrkaroundoptions
				);
				break;

			case 'itemid':
			default:
				$ret = $cache->get(
					array($cacheparams->class, $cacheparams->method),
					$cacheparams->methodparams,
					$module->id . $view_levels . JFactory::getApplication()->input->getInt('Itemid', null),
					$wrkarounds,
					$wrkaroundoptions
				);
				break;
		}

		return $ret;
	}
}
