<?php

/**
* @version $Id: plugin.php 1603 2006-01-01 17:26:54Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
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
 * @author Louis Landry <louis@webimagery.net>
 * @package Joomla.Framework
 * @subpackage Application
 * @since 1.1
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
		 * value.  If it does not exist, return a boolean true.
		 */
		if (method_exists($this, $event)) {
			return call_user_func_array(array($this, $event), $args);
		} else {
			return true;
		}
	}
}

/**
* Plugin helper class
* 
* @author Johan Janssens <johan@joomla.be>
* @package Joomla.Framework
* @subpackage Application
* @since 1.1
*/
class JPluginHelper
{
	/**
	 * Get plugin by folder and element
	 *
	 * @access public
	 * @param string 	$folder	The folder of the plugin
	 * @param string 	$name	The element of the plugin
	 * @return object	The Plugin object
	 */
	function &getPlugin($folder, $name) 
	{
		$result = null;
		
		$plugins = JPluginHelper::_load();

		$total = count($plugins);
		for($i = 0; $i < $total; $i++) {
			if($plugins[$i]->element == $name && $plugins[$i]->folder == $folder) {
				JPluginHelper::import( $plugins[$i]->folder, $plugins[$i]->element, $plugins[$i]->published, $plugins[$i]->params );
				$result =& $plugins[$i];
				break;
			}
		}

		return $result;
	}

	/**
	* Loads all the bot files for a particular group
	* @param string The group name, relates to the sub-directory in the plugins directory
	*/
	function importGroup( $group )
	{

		$plugins = JPluginHelper::_load();

		$n = count( $plugins );
		for ($i = 0; $i < $n; $i++) {
			if($plugins[$i]->folder == $group) {
				JPluginHelper::import( $plugins[$i]->folder, $plugins[$i]->element, $plugins[$i]->published, $plugins[$i]->params );
			}
		}
		return true;
	}
	/**
	 * Loads the bot file
	 * @param string The folder (group)
	 * @param string The elements (name of file without extension)
	 * @param int Published state
	 * @param string The params for the bot
	 */
	function import( $folder, $element, $published, $params='' )
	{
		global $_MAMBOTS, $mainframe; //needed for backwards compatibility
		
		$path = JPATH_SITE . DS .'plugins'. DS . $folder . DS . $element . '.php';
		if (file_exists( $path )) {
			require_once( $path );
		}
	}

	function _load() {
		global $mainframe;

		static $plugins;

		if (isset($plugins)) {
			return $plugins;
		}

		$db = & $mainframe->getDBO();
		$my = & $mainframe->getUser();

		if (is_object( $my )) {
			$gid = $my->gid;
		} else {
			$gid = 0;
		}

		$query = "SELECT folder, element, published, params"
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
