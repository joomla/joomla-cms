<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Event
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
* Plugin helper class
*
* @static
* @author		Johan Janssens <johan.janssens@joomla.org>
* @package		Joomla.Framework
* @subpackage	Event
* @since		1.5
*/
class JPluginHelper
{
	/**
	 * Get the plugin data of a specific type if no specific plugin is specified
	 * otherwise only the specific plugin data is returned
	 *
	 * @access public
	 * @param string 	$type 	The plugin type, relates to the sub-directory in the plugins directory
	 * @param string 	$plugin	The plugin name
	 * @return mixed 	An array of plugin data objects, or a plugin data object
	 */
	function &getPlugin($type, $plugin = null)
	{
		$result = array();

		$plugins = JPluginHelper::_load();
		
		$total = count($plugins);
		for($i = 0; $i < $total; $i++) 
		{
			if(is_null($plugin))
			{
				if($plugins[$i]->type == $type) {
					$result[] = $plugins[$i];
				}
			}
			else
			{
				if($plugins[$i]->type == $type && $plugins[$i]->name == $plugin) {
					$result = $plugins[$i];
					break;
				}
			}

		}
		
		return $result;
	}

	/**
	 * Checks if a plugin is enabled
	 *
	 * @access	public
	 * @param string 	$type 	The plugin type, relates to the sub-directory in the plugins directory
	 * @param string 	$plugin	The plugin name
	 * @return	boolean
	 */
	function isEnabled( $type, $plugin = null )
	{
		$result = &JPluginHelper::getPlugin( $type, $plugin);
		return (!empty($result));
	}

	/**
	* Loads all the plugin files for a particular type if no specific plugin is specified
	* otherwise only the specific pugin is loaded.
	*
	* @access public
	* @param string 	$type 	The plugin type, relates to the sub-directory in the plugins directory
	* @param string 	$plugin	The plugin name
	* @return boolean True if success
	*/
	function importPlugin($type, $plugin = null, $autocreate = true, $dispatcher = null)
	{
		$result = false;

		$plugins = JPluginHelper::_load();

		$total = count($plugins);
		for($i = 0; $i < $total; $i++) {
			if($plugins[$i]->type == $type && ($plugins[$i]->name == $plugin ||  $plugin === null)) {
				JPluginHelper::_import( $plugins[$i], $autocreate, $dispatcher );
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * Loads the plugin file
	 *
	 * @access private
	 * @return boolean True if success
	 */
	function _import( &$plugin, $autocreate = true, $dispatcher = null )
	{
		static $paths;

		if (!$paths) {
			$paths = array();
		}

		$result	= false;
		$plugin->type = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->type);
		$plugin->name  = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->name);
		
		$path	= JPATH_PLUGINS.DS.$plugin->type.DS.$plugin->name.'.php';

		if (!isset( $paths[$path] ))
		{
			if (file_exists( $path ))
			{
				//needed for backwards compatibility
				global $_MAMBOTS, $mainframe;

				jimport('joomla.plugin.plugin');
				require_once( $path );
				$paths[$path] = true;

				if($autocreate)
				{
					// Makes sure we have an event dispatcher
					if(!is_object($dispatcher)) {
						$dispatcher = & JDispatcher::getInstance();
					}

					$className = 'plg'.$plugin->type.$plugin->name;
					if(class_exists($className))
					{
						// load plugin parameters
						$plugin =& JPluginHelper::getPlugin($plugin->type, $plugin->name);
						
						// create the plugin
						$instance = new $className($dispatcher, (array)($plugin));
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
	 * Loads the published plugins
	 *
	 * @access private
	 */
	function _load()
	{
		static $plugins;

		if (isset($plugins)) {
			return $plugins;
		}

		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();

		if (isset($user))
		{
			$aid = $user->get('aid', 0);

			$query = 'SELECT folder AS type, element AS name, params'
				. ' FROM #__plugins'
				. ' WHERE published >= 1'
				. ' AND access <= ' . (int) $aid
				. ' ORDER BY ordering';
		}
		else
		{
			$query = 'SELECT folder AS type, element AS name, params'
				. ' FROM #__plugins'
				. ' WHERE published >= 1'
				. ' ORDER BY ordering';
		}

		$db->setQuery( $query );

		if (!($plugins = $db->loadObjectList())) {
			JError::raiseWarning( 'SOME_ERROR_CODE', "Error loading Plugins: " . $db->getErrorMsg());
			return false;
		}

		return $plugins;
	}

}
