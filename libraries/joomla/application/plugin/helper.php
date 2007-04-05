<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Application
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
* @subpackage	Application
* @since		1.5
*/
class JPluginHelper
{
	/**
	 * Get the plugin data of a group if no specific plugin is specified
	 * otherwise only the specific plugin data is returned
	 *
	 * @access public
	 * @param string 	$group 	The group name, relates to the sub-directory in the plugins directory
	 * @param string 	$plugin	The plugin name
	 * @return mixed 	An array of plugin data objects, or a plugin data object
	 */
	function &getPlugin($group, $plugin = null)
	{
		$result = array();

		$plugins = JPluginHelper::_load();

		$total = count($plugins);
		for($i = 0; $i < $total; $i++) {

			if(is_null($plugin))
			{
				if($plugins[$i]->folder == $group) {
					$result[] = $plugins[$i];
				}
			}
			else
			{
				if($plugins[$i]->folder == $group && $plugins[$i]->element == $plugin) {
					$result = $plugins[$i];
					break;
				}
			}

		}

		return $result;
	}

	/**
	* Loads all the plugin files for a particular group if no specific plugin is specified
	* otherwise only the specific pugin is loaded.
	*
	* @access public
	* @param string 	$group 	The group name, relates to the sub-directory in the plugins directory
	* @param string 	$plugin	The plugin name
	* @return boolean True if success
	*/
	function importPlugin($group, $plugin = null, $autocreate = true, $dispatcher = null)
	{
		$result = false;

		$plugins = JPluginHelper::_load();

		$total = count($plugins);
		for($i = 0; $i < $total; $i++) {
			if($plugins[$i]->folder == $group && ($plugins[$i]->element == $plugin ||  $plugin === null)) {
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
	 * @param string The folder (group)
	 * @param string The elements (name of file without extension)
	 * @param int Published state
	 * @param string The params for the bot
	 * @return boolean True if success
	 */
	function _import( &$plugin, $autocreate = true, $dispatcher = null )
	{
		static $paths;

		if (!$paths) {
			$paths = array();
		}

		$result	= false;
		$plugin->folder = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->folder);
		$plugin->element = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->element);
		$path	= JPATH_PLUGINS.DS.$plugin->folder.DS.$plugin->element.'.php';

		if (!isset( $paths[$path] ))
		{
			if (file_exists( $path ))
			{
				//needed for backwards compatibility
				global $_MAMBOTS, $mainframe;

				require_once( $path );
				$paths[$path] = true;

				if($autocreate)
				{
					// Makes sure we have an event dispatcher
					if(!is_object($dispatcher)) {
						$dispatcher = & JEventDispatcher::getInstance();
					}

					$className = 'plg'.$plugin->folder.$plugin->element;
					if(class_exists($className)) {
						$plugin = new $className($dispatcher);
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
	 * Loads the plugin data
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

			$query = 'SELECT id, name, folder, element, published, params'
				. ' FROM #__plugins'
				. ' WHERE published >= 1'
				. ' AND access <= ' . (int) $aid
				. ' ORDER BY ordering';
		}
		else
		{
			$query = 'SELECT id, name, folder, element, published, params'
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