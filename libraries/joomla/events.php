<?php

/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.classes.object' );

/**
* Event handler
* @package Joomla
* @subpackage JFramework
* @since 1.0
*/
class JEventHandler extends JObject {
	/** @var array An array of functions in event groups */
	var $_events	= null;

	/**
	* Constructor
	*/
	function __construct() {
		$this->_events = array();
	}

	/**
	 * Returns a reference to the global Language object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $browser = &JEventHandler::getInstance();</pre>
	 *
	 * @return JEventHandler  The EventHandler object.
	 */
	function &getInstance()
	{
		static $instances;

		if (!isset($instances)) {
			$instances = array();
		}

		if (empty($instances[0])) {
			$instances[0] = new JEventHandler();
		}

		return $instances[0];
	}

	/**
	* Registers a function to a particular event group
	* @param string The event name
	* @param string The function name
	*/
	function registerFunction( $event, $function ) {
		$this->_events[$event][] = array( $function );
	}

	/**
	* @param string The group name
	* @param string The list name
	* @return array
	*/
	function getList( $group, $listName ) {
		return $this->_lists[$group][$listName];
	}
	/**
	* Calls all functions associated with an event group
	* @param string The event name
	* @param array An array of arguments
	* @param boolean True is unpublished bots are to be processed [DEPRECEATED]
	* @return array An array of results from each function call
	*/
	function trigger( $event, $args=null, $doUnpublished=false ) {
		$result = array();

		if ($args === null) {
			$args = array();
		}
		if ($event == 'onPrepareContent' || $doUnpublished) {
			// prepend the published argument
			array_unshift( $args, null );
		}
		if (isset( $this->_events[$event] )) {
			foreach ($this->_events[$event] as $func) {
				if (function_exists( $func[0] )) {
					$result[] = call_user_func_array( $func[0], $args );
				}
			}
		}
		return $result;
	}
	/**
	* Same as trigger but only returns the first event and
	* allows for a variable argument list
	* @param string The event name
	* @return array The result of the first function call
	*/
	function call( $event ) {

		$args =& func_get_args();
		array_shift( $args );

		if (isset( $this->_events[$event] )) {
			foreach ($this->_events[$event] as $func) {
				if (function_exists( $func[0] )) {
						return call_user_func_array( $func[0], $args );
				}
			}
		}
		return null;
	}
}

/**
* Bot loader
* @package Joomla
* @subpackage JFramework
* @since 1.1
*/
class JBotLoader
{
	/**
	* Loads all the bot files for a particular group
	* @param string The group name, relates to the sub-directory in the mambots directory
	*/
	function importGroup( $group )
	{
		static $bots;

		if (!isset($bots)) {
			$bots = JBotLoader::_load();
		}

		$n = count( $bots);
		for ($i = 0; $i < $n; $i++) {
			if($bots[$i]->folder == $group) {
				JBotLoader::import( $bots[$i]->folder, $bots[$i]->element, $bots[$i]->published, $bots[$i]->params );
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
