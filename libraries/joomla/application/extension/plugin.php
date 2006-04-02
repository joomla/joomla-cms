<?php
/**
* @version $Id: plugin.php 1603 2006-01-01 17:26:54Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.common.base.observer' );

/**
 * JPlugin Class
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.1
 */
class JPlugin extends JObserver {

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @since 1.1
	 */
	function JPlugin(& $subject) {
		parent::__construct($subject);
	}

	/**
	 * Method to trigger events based upon the JAuthenticate object
	 *
	 * @access public
	 * @param array Arguments
	 * @return mixed Routine return value
	 * @since 1.1
	 */
	function update(& $args) {
		/*
		 * First lets get the event from the argument array.  Next we will unset the
		 * event argument as it has no bearing on the method to handle the event.
		 */
		$event = $args['event'];
		unset($args['event']);

		/*
		 * If the method to handle an event exists, call it and return its return
		 * value.  If it does not exist, return null.
		 */
		if (method_exists($this, $event)) {
			return call_user_func_array ( array($this, $event), $args );
		} else {
			return null;
		}
	}
}

/**
* Plugin helper class
* 
* @static
* @author		Johan Janssens <johan.janssens@joomla.org>
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.1
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
	function importPlugin($group, $plugin = null) 
	{
		$result = false;
		
		$plugins = JPluginHelper::_load();

		$total = count($plugins);
		for($i = 0; $i < $total; $i++) {
			if($plugins[$i]->folder == $group && ($plugins[$i]->element == $plugin ||  $plugin === null)) {
				JPluginHelper::_import( $plugins[$i]->folder, $plugins[$i]->element, $plugins[$i]->published, $plugins[$i]->params );
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
	function _import( $folder, $element, $published, $params='' )
	{
		global $_MAMBOTS, $mainframe; //needed for backwards compatibility
		
		$path = JPATH_SITE . DS .'plugins'. DS . $folder . DS . $element . '.php';
	
		$result = false;
		
		if (file_exists( $path )) {
			require_once( $path );
			$result = true;
		} 
		
		return $result;
	}

	/**
	 * Loads the plugin data
	 * 
	 * @access private
	 */
	function _load() 
	{
		global $mainframe;

		static $plugins;

		if (isset($plugins)) {
			return $plugins;
		}

		$db		= & $mainframe->getDBO();
		$user	= & $mainframe->getUser();

		if (is_object( $user )) {
			$gid = $user->get('gid');
		} else {
			$gid = 0;
		}

		$query = "SELECT id, folder, element, published, params"
			. "\n FROM #__plugins"
			. "\n WHERE published >= 1"
			. "\n AND access <= $gid"
			. "\n ORDER BY ordering"
			;

		$db->setQuery( $query );

		if (!($plugins = $db->loadObjectList())) {
			JError::raiseWarning( 'SOME_ERROR_CODE', "Error loading Plugins: " . $db->getErrorMsg());
			return false;
		}

		return $plugins;
	}

}
?>
