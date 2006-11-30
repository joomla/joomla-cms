<?php
/**
* @version $Id$
* @package Joomla.Framework
* @subpackage Application
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Import library dependencies
jimport('joomla.application.component.helper');

/**
* Module helper class
*
* @static
* @author		Johan Janssens <johan.janssens@joomla.org>
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.5
*/
class JModuleHelper
{
	/**
	 * Get module by name
	 *
	 * @access public
	 * @param string 	$name	The name of the module
	 * @return object	The Module object
	 */
	function &getModule($name)
	{
		$result = null;

		$modules =& JModuleHelper::_load();

		$total = count($modules);
		for ($i = 0; $i < $total; $i++) {
			if ($modules[$i]->name == $name)
			{
				$result =& $modules[$i];
				break;
			}
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
	function &getModules($position)
	{
		$result = array();

		$modules =& JModuleHelper::_load();

		$total = count($modules);
		for($i = 0; $i < $total; $i++) {
			if($modules[$i]->position == $position) {
				$result[] =& $modules[$i];
			}
		}

		return $result;
	}

	function renderModule($module, $attribs = array())
	{
		static $chrome;
		global $mainframe, $Itemid, $option;
		
		// Initialize variables
		$style		= isset($attribs['style'])   ? $attribs['style']   : $module->style;
		$outline	= isset($attribs['outline']) ? $attribs['outline'] : false;

		// Handle legacy globals if enabled
		if ($mainframe->getCfg('legacy'))
		{
			// Include legacy globals
			global $my, $database, $acl;

			// Get the task variable for local scope
			$task = JRequest::getVar( 'task' );

			// For backwards compatibility extract the config vars as globals
			$registry =& JFactory::getConfig();
			foreach (get_object_vars($registry->toObject()) as $k => $v) {
				$name = 'mosConfig_'.$k;
				$$name = $v;
			}
			$contentConfig = &JComponentHelper::getParams( 'com_content' );
			foreach (get_object_vars($contentConfig->toObject()) as $k => $v)
			{
				$name = 'mosConfig_'.$k;
				$$name = $v;
			}
			$usersConfig = &JComponentHelper::getParams( 'com_users' );
			foreach (get_object_vars($usersConfig->toObject()) as $k => $v)
			{
				$name = 'mosConfig_'.$k;
				$$name = $v;
			}
		}

		// Get module parameters
		$params = new JParameter( $module->params );

		// Get module path
		$path = JPATH_BASE.'/modules/'.$module->module.'/'.$module->module.'.php';

		// Load the module
		if (!$module->user && file_exists( $path ) && empty($module->content))
		{
			$lang =& JFactory::getLanguage();
			$lang->load($module->module);

			ob_start();
			require $path;
			$module->content = ob_get_contents();
			ob_end_clean();
		}

		// Load the module chrome functions
		if (!$chrome) {
			$chrome = array();
		}
		require_once (JPATH_BASE.'/modules/templates/modules.php');
		$chromePath = JPATH_BASE.'/templates/'.$mainframe->getTemplate().'/html/modules.php';
		if (!isset( $chrome[$chromePath]))
		{
			if (file_exists($chromePath)) {
				require_once ($chromePath);
			}
			$chrome[$chromePath] = true;
		}

		$chromeMethod = 'modChrome_'.$style;

		// Handle template preview outlining
		$contents = null;
		if($outline && !$mainframe->isAdmin())
		{
			$doc =& JFactory::getDocument();
			$css  = ".mod-preview-info { padding: 2px 4px 2px 4px; border: 1px solid black; position: absolute; background-color: white; color: red;opacity: .80; filter: alpha(opacity=80); -moz-opactiy: .80; }";
			$css .= ".mod-preview-wrapper { background-color:#eee;  border: 1px dotted black; color:#700; opacity: .50; filter: alpha(opacity=50); -moz-opactiy: .50;}";
			$doc->addStyleDeclaration($css);

			$contents .= "
			<div class=\"mod-preview\">
			<div class=\"mod-preview-info\">".$module->position."[".$style."]</div>
			<div class=\"mod-preview-wrapper\">";
		}

		// Apply chrome and render module
		ob_start();
			if (!function_exists($chromeMethod)) {
				echo $module->content;
			} else {
				$chromeMethod($module, $params, $attribs);
			}
		$contents .= ob_get_contents();
		ob_end_clean();

		// Close template preview outlining if enabled
		if($outline && !$mainframe->isAdmin()) {
			$contents .= "</div></div>";
		}

		return $contents;
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
	function getLayoutPath($module, $layout = 'default')
	{
		global $mainframe;

		// Build the template and base path for the layout
		$tPath = JPATH_BASE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.$module.DS.$layout.'.php';
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
	function &_load()
	{
		global $mainframe, $Itemid;

		static $modules;

		if (isset($modules)) {
			return $modules;
		}

		$user	=& JFactory::getUser();
		$db		=& JFactory::getDBO();

		$gid	= $user->get('gid');

		$modules	= array();

		$wheremenu = isset( $Itemid ) ? "\n AND ( mm.menuid = ". $Itemid ." OR mm.menuid = 0 )" : '';

		$query = "SELECT id, title, module, position, content, showtitle, control, params"
			. "\n FROM #__modules AS m"
			. "\n LEFT JOIN #__modules_menu AS mm ON mm.moduleid = m.id"
			. "\n WHERE m.published = 1"
			. "\n AND m.access <= ". (int)$gid
			. "\n AND m.client_id = ". (int)$mainframe->getClientId()
			. $wheremenu
			. "\n ORDER BY position, ordering";

		$db->setQuery( $query );
		$modules = $db->loadObjectList();

		$total = count($modules);
		for($i = 0; $i < $total; $i++)
		{
			//determine if this is a custom module
			$file					= $modules[$i]->module;
			$custom 				= substr( $file, 0, 4 )  == 'mod_' ?  0 : 1;
			$modules[$i]->user  	= $custom;
			// CHECK: custom module name is given by the title field, otherwise it's just 'om' ??
			$modules[$i]->name		= $custom ? $modules[$i]->title : substr( $file, 4 );
			$modules[$i]->style		= null;
			$modules[$i]->position	= strtolower($modules[$i]->position);
		}

		return $modules;
	}
}
?>
