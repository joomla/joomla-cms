<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Profiler\Profiler;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Module helper class
 *
 * @since  1.5
 */
abstract class ModuleHelper
{
	/**
	 * Get module by name (real, eg 'Breadcrumbs' or folder, eg 'mod_breadcrumbs')
	 *
	 * @param   string  $name   The name of the module
	 * @param   string  $title  The title of the module, optional
	 *
	 * @return  \stdClass  The Module object
	 *
	 * @since   1.5
	 */
	public static function &getModule($name, $title = null)
	{
		$result = null;
		$modules =& static::load();
		$total = \count($modules);

		for ($i = 0; $i < $total; $i++)
		{
			// Match the name of the module
			if ($modules[$i]->name === $name || $modules[$i]->module === $name)
			{
				// Match the title if we're looking for a specific instance of the module
				if (!$title || $modules[$i]->title === $title)
				{
					// Found it
					$result = &$modules[$i];
					break;
				}
			}
		}

		// If we didn't find it, and the name is mod_something, create a dummy object
		if ($result === null && strpos($name, 'mod_') === 0)
		{
			$result = static::createDummyModule();
			$result->module = $name;
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
		$input  = Factory::getApplication()->input;

		$modules =& static::load();

		$total = \count($modules);

		for ($i = 0; $i < $total; $i++)
		{
			if ($modules[$i]->position === $position)
			{
				$result[] = &$modules[$i];
			}
		}

		if (\count($result) === 0)
		{
			if ($input->getBool('tp') && ComponentHelper::getParams('com_templates')->get('template_positions_display'))
			{
				$result[0] = static::createDummyModule();
				$result[0]->title = $position;
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

		return $result !== null && $result->id !== 0;
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
		$app = Factory::getApplication();

		// Check that $module is a valid module object
		if (!\is_object($module) || !isset($module->module) || !isset($module->params))
		{
			if (JDEBUG)
			{
				Log::addLogger(array('text_file' => 'jmodulehelper.log.php'), Log::ALL, array('modulehelper'));
				$app->getLogger()->debug(
					__METHOD__ . '() - The $module parameter should be a module object.',
					array('category' => 'modulehelper')
				);
			}

			return '';
		}

		// Get module parameters
		$params = new Registry($module->params);

		// Render the module content
		static::renderRawModule($module, $params, $attribs);

		// Return early if only the content is required
		if (!empty($attribs['contentOnly']))
		{
			return $module->content;
		}

		if (JDEBUG)
		{
			Profiler::getInstance('Application')->mark('beforeRenderModule ' . $module->module . ' (' . $module->title . ')');
		}

		// Record the scope.
		$scope = $app->scope;

		// Set scope to component name
		$app->scope = $module->module;

		// Get the template
		$template = $app->getTemplate();

		// Check if the current module has a style param to override template module style
		$paramsChromeStyle = $params->get('style');
		$basePath          = '';

		if ($paramsChromeStyle)
		{
			$paramsChromeStyle   = explode('-', $paramsChromeStyle, 2);
			$ChromeStyleTemplate = strtolower($paramsChromeStyle[0]);
			$attribs['style']    = $paramsChromeStyle[1];

			// Only set $basePath if the specified template isn't the current or system one.
			if ($ChromeStyleTemplate !== $template && $ChromeStyleTemplate !== 'system')
			{
				$basePath = JPATH_THEMES . '/' . $ChromeStyleTemplate . '/html/layouts';
			}
		}

		// Make sure a style is set
		if (!isset($attribs['style']))
		{
			$attribs['style'] = 'none';
		}

		// Dynamically add outline style
		if ($app->input->getBool('tp') && ComponentHelper::getParams('com_templates')->get('template_positions_display'))
		{
			$attribs['style'] .= ' outline';
		}

		$module->style = $attribs['style'];

		// If the $module is nulled it will return an empty content, otherwise it will render the module normally.
		$app->triggerEvent('onRenderModule', array(&$module, &$attribs));

		if ($module === null || !isset($module->content))
		{
			return '';
		}

		$displayData = array(
			'module'  => $module,
			'params'  => $params,
			'attribs' => $attribs,
		);

		foreach (explode(' ', $attribs['style']) as $style)
		{
			if ($moduleContent = LayoutHelper::render('chromes.' . $style, $displayData, $basePath))
			{
				$module->content = $moduleContent;
			}
		}

		// Revert the scope
		$app->scope = $scope;

		$app->triggerEvent('onAfterRenderModule', array(&$module, &$attribs));

		if (JDEBUG)
		{
			Profiler::getInstance('Application')->mark('afterRenderModule ' . $module->module . ' (' . $module->title . ')');
		}

		return $module->content;
	}

	/**
	 * Render the module content.
	 *
	 * @param   object    $module   A module object
	 * @param   Registry  $params   A module parameters
	 * @param   array     $attribs  An array of attributes for the module (probably from the XML).
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public static function renderRawModule($module, Registry $params, $attribs = array())
	{
		if (!empty($module->contentRendered))
		{
			return $module->content;
		}

		if (JDEBUG)
		{
			Profiler::getInstance('Application')->mark('beforeRenderRawModule ' . $module->module . ' (' . $module->title . ')');
		}

		$app = Factory::getApplication();

		// Record the scope.
		$scope = $app->scope;

		// Set scope to component name
		$app->scope = $module->module;

		// Get module path
		$module->module = preg_replace('/[^A-Z0-9_\.-]/i', '', $module->module);

		$dispatcher = $app->bootModule($module->module, $app->getName())->getDispatcher($module, $app);

		// Check if we have a dispatcher
		if ($dispatcher)
		{
			ob_start();
			$dispatcher->dispatch();
			$module->content = ob_get_clean();
		}

		// Add the flag that the module content has been rendered
		$module->contentRendered = true;

		// Revert the scope
		$app->scope = $scope;

		if (JDEBUG)
		{
			Profiler::getInstance('Application')->mark('afterRenderRawModule ' . $module->module . ' (' . $module->title . ')');
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
		$templateObj   = Factory::getApplication()->getTemplate(true);
		$defaultLayout = $layout;
		$template      = $templateObj->template;

		if (strpos($layout, ':') !== false)
		{
			// Get the template and file name from the string
			$temp = explode(':', $layout);
			$template = $temp[0] === '_' ? $template : $temp[0];
			$layout = $temp[1];
			$defaultLayout = $temp[1] ?: 'default';
		}

		$dPath = JPATH_BASE . '/modules/' . $module . '/tmpl/default.php';

		try
		{
			// Build the template and base path for the layout
			$tPath = Path::check(JPATH_THEMES . '/' . $template . '/html/' . $module . '/' . $layout . '.php');
			$iPath = Path::check(JPATH_THEMES . '/' . $templateObj->parent . '/html/' . $module . '/' . $layout . '.php');
			$bPath = Path::check(JPATH_BASE . '/modules/' . $module . '/tmpl/' . $defaultLayout . '.php');
		}
		catch (\Exception $e)
		{
			// On error fallback to the default path
			return $dPath;
		}

		// If the template has a layout override use it
		if (is_file($tPath))
		{
			return $tPath;
		}

		if (!empty($templateObj->parent) && is_file($iPath))
		{
			return $iPath;
		}

		if (is_file($bPath))
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

		$app = Factory::getApplication();

		$modules = null;

		$app->triggerEvent('onPrepareModuleList', array(&$modules));

		// If the onPrepareModuleList event returns an array of modules, then ignore the default module list creation
		if (!\is_array($modules))
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
		$app      = Factory::getApplication();
		$itemId   = $app->input->getInt('Itemid', 0);
		$groups   = $app->getIdentity()->getAuthorisedViewLevels();
		$clientId = (int) $app->getClientId();

		// Build a cache ID for the resulting data object
		$cacheId = implode(',', $groups) . '.' . $clientId . '.' . $itemId;

		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);
		$nowDate = Factory::getDate()->toSql();

		$query->select($db->quoteName(['m.id', 'm.title', 'm.module', 'm.position', 'm.content', 'm.showtitle', 'm.params', 'mm.menuid']))
			->from($db->quoteName('#__modules', 'm'))
			->join(
				'LEFT',
				$db->quoteName('#__modules_menu', 'mm'),
				$db->quoteName('mm.moduleid') . ' = ' . $db->quoteName('m.id')
			)
			->join(
				'LEFT',
				$db->quoteName('#__extensions', 'e'),
				$db->quoteName('e.element') . ' = ' . $db->quoteName('m.module')
				. ' AND ' . $db->quoteName('e.client_id') . ' = ' . $db->quoteName('m.client_id')
			)
			->where(
				[
					$db->quoteName('m.published') . ' = 1',
					$db->quoteName('e.enabled') . ' = 1',
					$db->quoteName('m.client_id') . ' = :clientId',
				]
			)
			->bind(':clientId', $clientId, ParameterType::INTEGER)
			->whereIn($db->quoteName('m.access'), $groups)
			->extendWhere(
				'AND',
				[
					$db->quoteName('m.publish_up') . ' IS NULL',
					$db->quoteName('m.publish_up') . ' <= :publishUp',
				],
				'OR'
			)
			->bind(':publishUp', $nowDate)
			->extendWhere(
				'AND',
				[
					$db->quoteName('m.publish_down') . ' IS NULL',
					$db->quoteName('m.publish_down') . ' >= :publishDown',
				],
				'OR'
			)
			->bind(':publishDown', $nowDate)
			->extendWhere(
				'AND',
				[
					$db->quoteName('mm.menuid') . ' = :itemId',
					$db->quoteName('mm.menuid') . ' <= 0',
				],
				'OR'
			)
			->bind(':itemId', $itemId, ParameterType::INTEGER);

		// Filter by language
		if ($app->isClient('site') && $app->getLanguageFilter() || $app->isClient('administrator') && static::isAdminMultilang())
		{
			$language = $app->getLanguage()->getTag();

			$query->whereIn($db->quoteName('m.language'), [$language, '*'], ParameterType::STRING);
			$cacheId .= $language . '*';
		}

		$query->order($db->quoteName(['m.position', 'm.ordering']));

		// Set the query
		$db->setQuery($query);

		try
		{
			/** @var CallbackController $cache */
			$cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
				->createCacheController('callback', ['defaultgroup' => 'com_modules']);

			$modules = $cache->get(array($db, 'loadObjectList'), array(), md5($cacheId), false);
		}
		catch (\RuntimeException $e)
		{
			$app->getLogger()->warning(
				Text::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $e->getMessage()),
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
		$Itemid = Factory::getApplication()->input->getInt('Itemid');
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
	 * 'itemid'      Changes on itemid change, to be called from inside the module:
	 * 'safeuri'     Id created from $cacheparams->modeparams array,
	 * 'id'          Module sets own cache id's
	 *
	 * @param   object  $module        Module object
	 * @param   object  $moduleparams  Module parameters
	 * @param   object  $cacheparams   Module cache parameters - id or URL parameters, depending on the module cache mode
	 *
	 * @return  string
	 *
	 * @see     InputFilter::clean()
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

		if (!isset($cacheparams->cachesuffix))
		{
			$cacheparams->cachesuffix = '';
		}

		$user = Factory::getUser();
		$app  = Factory::getApplication();

		/** @var CallbackController $cache */
		$cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
			->createCacheController('callback', ['defaultgroup' => $cacheparams->cachegroup]);

		// Turn cache off for internal callers if parameters are set to off and for all logged in users
		$ownCacheDisabled = $moduleparams->get('owncache') === 0 || $moduleparams->get('owncache') === '0';
		$cacheDisabled = $moduleparams->get('cache') === 0 || $moduleparams->get('cache') === '0';

		if ($ownCacheDisabled || $cacheDisabled || $app->get('caching') == 0 || $user->get('id'))
		{
			$cache->setCaching(false);
		}

		// Module cache is set in seconds, global cache in minutes, setLifeTime works in minutes
		$cache->setLifeTime($moduleparams->get('cache_time', $app->get('cachetime') * 60) / 60);

		$wrkaroundoptions = array('nopathway' => 1, 'nohead' => 0, 'nomodules' => 1, 'modulemode' => 1, 'mergehead' => 1);

		$wrkarounds = true;
		$view_levels = md5(serialize($user->getAuthorisedViewLevels()));

		switch ($cacheparams->cachemode)
		{
			case 'id':
				$ret = $cache->get(
					array($cacheparams->class, $cacheparams->method),
					$cacheparams->methodparams,
					$cacheparams->modeparams . $cacheparams->cachesuffix,
					$wrkarounds,
					$wrkaroundoptions
				);
				break;

			case 'safeuri':
				$secureid = null;

				if (\is_array($cacheparams->modeparams))
				{
					$input   = $app->input;
					$uri     = $input->getArray();
					$safeuri = new \stdClass;
					$noHtmlFilter = InputFilter::getInstance();

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
					$module->id . $view_levels . $secureid . $cacheparams->cachesuffix,
					$wrkarounds,
					$wrkaroundoptions
				);
				break;

			case 'static':
				$ret = $cache->get(
					array($cacheparams->class, $cacheparams->method),
					$cacheparams->methodparams,
					$module->module . md5(serialize($cacheparams->methodparams)) . $cacheparams->cachesuffix,
					$wrkarounds,
					$wrkaroundoptions
				);
				break;

			case 'itemid':
			default:
				$ret = $cache->get(
					array($cacheparams->class, $cacheparams->method),
					$cacheparams->methodparams,
					$module->id . $view_levels . $app->input->getInt('Itemid', null) . $cacheparams->cachesuffix,
					$wrkarounds,
					$wrkaroundoptions
				);
				break;
		}

		return $ret;
	}

	/**
	 * Method to determine if filtering by language is enabled in back-end for modules.
	 *
	 * @return  boolean  True if enabled; false otherwise.
	 *
	 * @since   3.8.0
	 */
	public static function isAdminMultilang()
	{
		static $enabled = false;

		if (\count(LanguageHelper::getInstalledLanguages(1)) > 1)
		{
			$enabled = (bool) ComponentHelper::getParams('com_modules')->get('adminlangfilter', 0);
		}

		return $enabled;
	}

	/**
	 * Get module by id
	 *
	 * @param   string  $id  The id of the module
	 *
	 * @return  \stdClass  The Module object
	 *
	 * @since   3.9.0
	 */
	public static function &getModuleById($id)
	{
		$modules =& static::load();

		$total = \count($modules);

		for ($i = 0; $i < $total; $i++)
		{
			// Match the id of the module
			if ((string) $modules[$i]->id === $id)
			{
				// Found it
				return $modules[$i];
			}
		}

		// If we didn't find it, create a dummy object
		$result = static::createDummyModule();

		return $result;
	}

	/**
	 * Method to create a dummy module.
	 *
	 * @return  \stdClass  The Module object
	 *
	 * @since   4.0.0
	 */
	protected static function createDummyModule(): \stdClass
	{
		$module            = new \stdClass;
		$module->id        = 0;
		$module->title     = '';
		$module->module    = '';
		$module->position  = '';
		$module->content   = '';
		$module->showtitle = 0;
		$module->control   = '';
		$module->params    = '';

		return $module;
	}
}
