<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Application
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

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
	 * @access	public
	 * @param	string 	$name	The name of the module
	 * @param	string	$title	The title of the module, optional
	 * @return	object	The Module object
	 */
	public static function &getModule($name, $title = null )
	{
		$result		= null;
		$modules	=& JModuleHelper::_load();
		$total		= count($modules);
		for ($i = 0; $i < $total; $i++)
		{
			// Match the name of the module
			if ($modules[$i]->name == $name)
			{
				// Match the title if we're looking for a specific instance of the module
				if ( ! $title || $modules[$i]->title == $title )
				{
					$result =& $modules[$i];
					break;	// Found it
				}
			}
		}

		// if we didn't find it, and the name is mod_something, create a dummy object
		if (is_null( $result ) && substr( $name, 0, 4 ) == 'mod_')
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
	 * @access public
	 * @param string 	$position	The position of the module
	 * @return array	An array of module objects
	 */
	public static function &getModules($position)
	{
		$position	= strtolower( $position );
		$result		= array();

		$modules =& JModuleHelper::_load();

		$total = count($modules);
		for($i = 0; $i < $total; $i++) {
			if($modules[$i]->position == $position) {
				$result[] =& $modules[$i];
			}
		}
		if(count($result) == 0) {
			if(JRequest::getBool('tp')) {
				$result[0] = JModuleHelper::getModule( 'mod_'.$position );
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
	 * @access	public
	 * @param   string 	$module	The module name
	 * @return	boolean
	 */
	public static function isEnabled( $module )
	{
		$result = &JModuleHelper::getModule( $module);
		return (!is_null($result));
	}

	public static function renderModule($module, $attribs = array())
	{
		static $chrome;
		$option = JRequest::getCMD('option');

		$appl	= JFactory::getApplication();

		//needed for backwards compatibility
		// @todo if legacy ...
		$mainframe =& $appl;

		$scope = $appl->scope; //record the scope
		$appl->scope = $module->module;  //set scope to component name

		// Get module parameters
		$params = new JParameter( $module->params );

		// Get module path
		$module->module = preg_replace('/[^A-Z0-9_\.-]/i', '', $module->module);
		$path = JPATH_BASE.DS.'modules'.DS.$module->module.DS.$module->module.'.php';

		// Load the module
		if (!$module->user && file_exists( $path ) && empty($module->content))
		{
			$lang =& JFactory::getLanguage();
			// 1.5 or Core
			$lang->load($module->module);
			// 1.6 3PD
			$lang->load( $module->module, dirname($path));


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

		require_once JPATH_BASE.DS.'templates'.DS.'system'.DS.'html'.DS.'modules.php';
		$chromePath = JPATH_BASE.DS.'templates'.DS.$appl->getTemplate().DS.'html'.DS.'modules.php';
		if (!isset( $chrome[$chromePath]))
		{
			if (file_exists($chromePath)) {
				require_once $chromePath;
			}
			$chrome[$chromePath] = true;
		}

		//make sure a style is set
		if(!isset($attribs['style'])) {
			$attribs['style'] = 'none';
		}

		//dynamically add outline style
		if(JRequest::getBool('tp')) {
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

		$appl->scope = $scope; //revert the scope

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
		$appl = JFactory::getApplication();

		// Build the template and base path for the layout
		$tPath = JPATH_BASE.DS.'templates'.DS.$appl->getTemplate().DS.'html'.DS.$module.DS.$layout.'.php';
		$bPath = JPATH_BASE.DS.'modules'.DS.$module.DS.'tmpl'.DS.$layout.'.php';

		// If the template has a layout override use it
		if (file_exists($tPath)) {
			return $tPath;
		} else {
			return $bPath;
		}
	}

	/**
	 * Load published modules
	 *
	 * @access	private
	 * @return	array
	 */
	protected static function &_load()
	{
		$Itemid = JRequest::getInt('Itemid');
		$appl	= JFactory::getApplication();

		static $clean;

		if (isset($clean)) {
			return $clean;
		}

		$user = &JFactory::getUser();
		$db = &JFactory::getDBO();

		$aid = $user->get('aid', 0);

		$modules = array();

		$wheremenu = !empty($Itemid)
			? ' AND (mm.menuid = ' . (int)$Itemid . ' OR mm.menuid <= 0)'
			: '';

		$query = 'SELECT id, title, module, position, content, showtitle, control, params, mm.menuid'
			. ' FROM #__modules AS m'
			. ' LEFT JOIN #__modules_menu AS mm ON mm.moduleid = m.id'
			. ' WHERE m.published = 1'
			. ' AND m.access <= '. (int)$aid
			. ' AND m.client_id = '. (int)$appl->getClientId()
			. $wheremenu
			. ' ORDER BY position, ordering';

		$db->setQuery($query);

		try {
			$modules = $db->loadObjectList();
		} catch(JException $e) {
			JError::raiseWarning(
				'SOME_ERROR_CODE',
				JText::_('Error Loading Modules') . $db->getErrorMsg()
			);
			$false = false;
			return $false;
		}

		// Apply negative selections and eliminate duplicates
		$negId = $Itemid ? -(int)$Itemid : false;
		$dups = array();
		$clean = array();
		foreach ($modules as $i => $module)
		{
			/*
			 * The module is excluded if there is an explicit prohibition, or if
			 * the Itemid is missing or zero and the module is in exclude mode.
			*/
			$negHit = $negId === (int)$module->menuid
					|| (!$negId && (int)$module->menuid < 0);
			if (isset($dups[$module->id])) {
			/*
			 * If this item has been excluded, keep the duplicate flag set,
				 * but remove any item from the cleaned array.
				 */
				if ($negHit) {
					unset($clean[$module->id]);
				}
				continue;
			}
			$dups[$module->id] = true;
			// Only accept modules without explicit exclusions.
			if (! $negHit) {
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
		// Return to simple indexing that matches the query order.
		$clean = array_values($clean);

		return $clean;
	}
}
