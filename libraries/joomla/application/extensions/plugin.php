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
 * @package Joomla
 * @subpackage JFramework
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
	 * Method to trigger events based upon the JAuth object
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
* @package Joomla
* @subpackage JFramework
* @since 1.1
*/
class JPluginHelper
{
	/**
	* Loads all the bot files for a particular group
	* @param string The group name, relates to the sub-directory in the mambots directory
	*/
	function importGroup( $group )
	{
		static $bots;

		if (!isset($bots)) {
			$bots = JPluginHelper::_load();
		}

		$n = count( $bots);
		for ($i = 0; $i < $n; $i++) {
			if($bots[$i]->folder == $group) {
				JPluginHelper::import( $bots[$i]->folder, $bots[$i]->element, $bots[$i]->published, $bots[$i]->params );
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
		
		$path = JPATH_SITE . '/mambots/' . $folder . '/' . $element . '.php';
		if (file_exists( $path )) {
			require_once( $path );
		}
	}

	function _load() {
		global $mainframe;

		$db =& $mainframe->getDBO();
		$my =& $mainframe->getUser();

		if (is_object( $my )) {
			$gid = $my->gid;
		} else {
			$gid = 0;
		}

		$query = "SELECT folder, element, published, params"
			. "\n FROM #__mambots"
			. "\n WHERE published >= 1"
			. "\n AND access <= $gid"
			. "\n ORDER BY ordering"
			;

		$db->setQuery( $query );

		if (!($bots = $db->loadObjectList())) {
			//echo "Error loading Mambots: " . $database->getErrorMsg();
			return false;
		}

		return $bots;
	}

}
?>