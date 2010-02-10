<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.application.component.helper');

/**
 * Module helper class
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
abstract class JModuleHelper
{

	/**
	 * Get module by name (real, eg 'Breadcrumbs' or folder, eg 'mod_breadcrumbs')
	 *
	 * @param	string	The name of the module
	 * @param	string	The title of the module, optional
	 *
	 * @return	object	The Module object
	 */
	public static function &getModule($name, $title = null)
	{
		$result		= null;
		$modules	= &JModuleHelper::_load();
		$total		= count($modules);
		for ($i = 0; $i < $total; $i++)
		{
			// Match the name of the module
			if ($modules[$i]->name == $name)
			{
				// Match the title if we're looking for a specific instance of the module
				if (!$title || $modules[$i]->title == $title)
				{
					$result = &$modules[$i];
					break;	// Found it
				}
			}
		}

		// if we didn't find it, and the name is mod_something, create a dummy object
		if (is_null($result) && substr($name, 0, 4) == 'mod_')
		{
			$result				= new stdClass;
			$result->id			= 0;
			$result->title		= '';
			$result->module		= $name;
			$result->position	= '';
			$result->content	= '';
			$result->showtitle	= 0;
			$result->control	= '';
			$result->params		= '';
			$result->user		= 0;
		}

		return $result;
	}

	/**
	 * Get modules by position
	 *
	 * @param string	$position	The position of the module
	 *
	 * @return array	An array of module objects
	 */
	public static function &getModules($position)
	{
		$app		= &JFactory::getApplication();
		$position	= strtolower($position);
		$result		= array();

		$modules = &JModuleHelper::_load();

		$total = count($modules);
		for ($i = 0; $i < $total; $i++)
		{
			if ($modules[$i]->position == $position) {
				$result[] = &$modules[$i];
			}
		}
		if (count($result) == 0)
		{
			if ($app->getCfg('debug_modules') && JRequest::getBool('tp'))
			{
				$result[0] = JModuleHelper::getModule('mod_'.$position);
				$result[0]->title = $position;
				$result[0]->content = $position;
				$result[0]->position = $position;
			}
		}

		return $result;
	}

	/**
	 * Checks if a module is enabled
	 *
	 * @param	string	The module name
	 *
	 * @return	boolean
	 */
	public static function isEnabled($module)
	{
		$result = &JModuleHelper::getModule($module);
		return (!is_null($result));
	}

	/**
	 * Render the module.
	 *
	 * @param	object	A module object.
	 * @param	array	An array of attributes for the module (probably from the XML).
	 *
	 * @return	strign	The HTML content of the module output.
	 */
	public static function renderModule($module, $attribs = array())
	{
		static $chrome;

		$option = JRequest::getCmd('option');
		$app	= &JFactory::getApplication();

		// Record the scope.
		$scope	= $app->scope;

		// Set scope to component name
		$app->scope = $module->module;

		// Get module parameters
		$params = new JParameter($module->params);

		// Get module path
		$module->module = preg_replace('/[^A-Z0-9_\.-]/i', '', $module->module);
		$path = JPATH_BASE.'/modules/'.$module->module.'/'.$module->module.'.php';

		// Load the module
		if (!$module->user && file_exists($path))
		{
			$lang = &JFactory::getLanguage();
			// 1.6 3PD
			$lang->load($module->module, dirname($path));
			// 1.5 or Core
			$lang->load($module->module);

			$content = '';
			ob_start();
			require $path;
			$module->content = ob_get_contents().$content;
			ob_end_clean();
		}

		// Load the module chrome functions
		if (!$chrome) {
			$chrome = array();
		}

		require_once JPATH_BASE.'/templates/system/html/modules.php';
		$chromePath = JPATH_BASE.'/templates/'.$app->getTemplate().'/html/modules.php';
		if (!isset($chrome[$chromePath]))
		{
			if (file_exists($chromePath)) {
				require_once $chromePath;
			}
			$chrome[$chromePath] = true;
		}

		//make sure a style is set
		if (!isset($attribs['style'])) {
			$attribs['style'] = 'none';
		}

		//dynamically add outline style
		if ($app->getCfg('debug_modules') && JRequest::getBool('tp')) {
			$attribs['style'] .= ' outline';
		}

		foreach(explode(' ', $attribs['style']) as $style)
		{
			$chromeMethod = 'modChrome_'.$style;

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

		$app->scope = $scope; //revert the scope

		return $module->content;
	}

	/**
	 * Get the path to a layout for a module
	 *
	 * @static
	 * @param	string	$module	The name of the module
	 * @param	string	$layout	The name of the module layout
	 * @return	string	The path to the module layout
	 * @since	1.5
	 */
	public static function getLayoutPath($module, $layout = 'default')
	{
		$app = JFactory::getApplication();

		// Build the template and base path for the layout
		$tPath = JPATH_BASE.'/templates/'.$app->getTemplate().'/html/'.$module.'/'.$layout.'.php';
		$bPath = JPATH_BASE.'/modules/'.$module.'/tmpl/'.$layout.'.php';

		// If the template has a layout override use it
		if (file_exists($tPath)) {
			return $tPath;
		}
		else {
			return $bPath;
		}
	}

	/**
	 * Load published modules
	 *
	 * @return	array
	 */
	protected static function &_load()
	{
		static $clean;

		if (isset($clean)) {
			return $clean;
		}

		$Itemid = JRequest::getInt('Itemid');
		$app	= JFactory::getApplication();
		$user	= &JFactory::getUser();
		$groups	= implode(',', $user->authorisedLevels());
		$db		= &JFactory::getDbo();

		$query = new JDatabaseQuery;
		$query->select('id, title, module, position, content, showtitle, params, mm.menuid');
		$query->from('#__modules AS m');
		$query->join('LEFT','#__modules_menu AS mm ON mm.moduleid = m.id');
		$query->where('m.published = 1');

		$date = JFactory::getDate();
		$now = $date->toMySQL();
		$nullDate = $db->getNullDate();
		$query->where('(m.publish_up = '.$db->Quote($nullDate).' OR m.publish_up <= '.$db->Quote($now).')');
		$query->where('(m.publish_down = '.$db->Quote($nullDate).' OR m.publish_down >= '.$db->Quote($now).')');

		$query->where('m.access IN ('.$groups.')');
		$query->where('m.client_id = '. (int) $app->getClientId());
		if (isset($Itemid)) {
			$query->where('(mm.menuid = '. (int) $Itemid .' OR mm.menuid <= 0)');
		}
		$query->order('position, ordering');
		$db->setQuery($query);
		
/*		$where	= isset($Itemid) ? ' AND (mm.menuid = '. (int) $Itemid .' OR mm.menuid <= 0)' : '';
		$db->setQuery(
			'SELECT id, title, module, position, content, showtitle, params, mm.menuid'
			. ' FROM #__modules AS m'
			. ' LEFT JOIN #__modules_menu AS mm ON mm.moduleid = m.id'
			. ' WHERE m.published = 1'
			. ' AND m.access IN ('.$groups.')'
			. ' AND m.client_id = '. (int) $app->getClientId()
			. $where
			. ' ORDER BY position, ordering'
		);*/

		if (null === ($modules = $db->loadObjectList()))
		{
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('ERROR_LOADING_MODULES') . $db->getErrorMsg());
			return false;
		}

		// Apply negative selections and eliminate duplicates
		$negId	= $Itemid ? -(int)$Itemid : false;
		$dupes	= array();
		$clean	= array();
		for ($i = 0, $n = count($modules); $i < $n; $i++)
		{
			$module = &$modules[$i];

			// The module is excluded if there is an explicit prohibition, or if
			// the Itemid is missing or zero and the module is in exclude mode.
			$negHit	= ($negId === (int) $module->menuid)
					|| (!$negId && (int)$module->menuid < 0);

			if (isset($dupes[$module->id]))
			{
				// If this item has been excluded, keep the duplicate flag set,
				// but remove any item from the cleaned array.
				if ($negHit) {
					unset($clean[$module->id]);
				}
				continue;
			}
			$dupes[$module->id] = true;

			// Only accept modules without explicit exclusions.
			if (!$negHit)
			{
				//determine if this is a custom module
				$file				= $module->module;
				$custom				= substr($file, 0, 4) == 'mod_' ?  0 : 1;
				$module->user		= $custom;
				// Custom module name is given by the title field, otherwise strip off "com_"
				$module->name		= $custom ? $module->title : substr($file, 4);
				$module->style		= null;
				$module->position	= strtolower($module->position);
				$clean[$module->id]	= $module;
			}
		}
		unset($dupes);
		// Return to simple indexing that matches the query order.
		$clean = array_values($clean);

		return $clean;
	}
}
