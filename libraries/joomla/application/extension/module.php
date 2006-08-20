<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

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

	function renderModule($module, $params = array())
	{
		global $mainframe, $Itemid, $task, $option;

		// Initialize variables
		$user 		=& JFactory::getUser();
		$db	 	    =& JFactory::getDBO();
		$acl  		=& JFactory::getACL();
		$style		= isset($params['style']) ? $params['style'] : $module->style;
		$outline	= isset($params['outline']) ? $params['outline'] : false;

		// For backwards compatibility extract the config vars as globals
		$registry =& JFactory::getConfig();
		foreach (get_object_vars($registry->toObject()) as $k => $v)
		{
			$name = 'mosConfig_'.$k;
			$$name = $v;
		}

		// Get module parameters
		$params = new JParameter( $module->params );

		// Get module path
		$path = JPATH_BASE.'/modules/'.$module->module.'/'.$module->module.'.php';

		// Load the module
		if (!$module->user && file_exists( $path )) {
			$lang =& JFActory::getLanguage();
			$lang->load($module->module);

			ob_start();
			require $path;
			$module->content = ob_get_contents();
			ob_end_clean();
		}

		// Load the module chrome functions
		require_once (JPATH_BASE.'/modules/templates/modules.php');
		$chromePath = JPATH_BASE.'/templates/'.$mainframe->getTemplate().'/html/modules.php';
		if (file_exists($chromePath)) {
			require_once ($chromePath);
		}

		// Select the module chrome function
		switch ( $style )
		{
			case -3:
				$style = 'rounded';
				break;

			case -2:
				$style = 'xhtml';
				break;

			case -1:
				$style = 'raw';
				break;

			case 1:
				$style = 'horiz';
				break;

			case 0:
				$style = 'table';
				break;
		}
		$chromeMethod = 'modChrome_'.$style;

		// Handle template preview outlining
		$contents = null;
		if($outline && !$mainframe->isAdmin()) {
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
				$chromeMethod($module, $params);
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
	 * Load published modules
	 * 
	 * @access	private
	 * @return	array
	 */
	function &_load()
	{
		global $mainframe;

		static $modules;

		if (isset($modules))
		{
			return $modules;
		}

		$user	=& JFactory::getUser();
		$db		=& JFactory::getDBO();

		$gid	= $user->get('gid');
		$Itemid = JRequest::getVar('Itemid');

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
			//determine if this is a user module
			$file = $modules[$i]->module;
			$user = substr( $file, 0, 4 )  == 'mod_' ?  0 : 1;
			$modules[$i]->user  = $user;
			// CHECK: custom module name is given by the title field, otherwise it's just 'om' ??
			$modules[$i]->name  = $user ? $modules[$i]->title : substr( $file, 4 );
			$modules[$i]->style = null;
		}

		return $modules;
	}
}
?>