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

/**
 * Task routing class
 * @package Joomla
 * @abstract
 * @since 1.0
 */
class mosAbstractTasker {
	/** @var array An array of the class methods to call for a task */
	var $_taskMap 	= null;
	/** @var string The name of the current task*/
	var $_task 		= null;
	/** @var array An array of the class methods*/
	var $_methods 	= null;
	/** @var string A url to redirect to */
	var $_redirect 	= null;
	/** @var string A message about the operation of the task */
	var $_message 	= null;

	// action based access control

	/** @var string The ACO Section */
	var $_acoSection 		= null;
	/** @var string The ACO Section value */
	var $_acoSectionValue 	= null;

	/**
	 * Constructor
	 * @param string Set the default task
	 */
	function mosAbstractTasker( $default='' ) {
		$taskMap = array();
		$this->_methods = array();
		foreach (get_class_methods( get_class( $this ) ) as $method) {
			if (substr( $method, 0, 1 ) != '_') {
				$this->_methods[] = strtolower( $method );
				// auto register public methods as tasks
				$this->_taskMap[strtolower( $method )] = $method;
			}
		}
		$this->_redirect = '';
		$this->_message = '';
		if ($default) {
			$this->registerDefaultTask( $default );
		}
	}

	/**
	 * Sets the access control levels
	 * @param string The ACO section (eg, the component)
	 * @param string The ACO section value (if using a constant value)
	 */
	function setAccessControl( $section, $value=null ) {
		$this->_acoSection = $section;
		$this->_acoSectionValue = $value;
	}
	/**
	 * Access control check
	 */
	function accessCheck( $task ) {
		global $acl, $my;

		// only check if the derived class has set these values
		if ($this->_acoSection) {
			// ensure user has access to this function
			if ($this->_acoSectionValue) {
				// use a 'constant' task for this task handler
				$task = $this->_acoSectionValue;
			}
			return $acl->acl_check( $this->_acoSection, $task, 'users', $my->usertype );
		} else {
			return true;
		}
	}

	/**
	 * Set a URL to redirect the browser to
	 * @param string A URL
	 */
	function setRedirect( $url, $msg = null ) {
		$this->_redirect = $url;
		if ($msg !== null) {
			$this->_message = $msg;
		}
	}
	/**
	 * Redirects the browser
	 */
	function redirect() {
		if ($this->_redirect) {
			mosRedirect( $this->_redirect, $this->_message );
		}
	}
	/**
	 * Register (map) a task to a method in the class
	 * @param string The task
	 * @param string The name of the method in the derived class to perform for this task
	 */
	function registerTask( $task, $method ) {
		if (in_array( strtolower( $method ), $this->_methods )) {
			$this->_taskMap[strtolower( $task )] = $method;
		} else {
			$this->methodNotFound( $method );
		}
	}
	/**
	 * Register the default task to perfrom if a mapping is not found
	 * @param string The name of the method in the derived class to perform if the task is not found
	 */
	function registerDefaultTask( $method ) {
		$this->registerTask( '__default', $method );
	}
	/**
	 * Perform a task by triggering a method in the derived class
	 * @param string The task to perform
	 * @return mixed The value returned by the function
	 */
	function performTask( $task ) {
		$this->_task = $task;

		$task = strtolower( $task );
		if (isset( $this->_taskMap[$task] )) {
			$doTask = $this->_taskMap[$task];
		} else if (isset( $this->_taskMap['__default'] )) {
			$doTask = $this->_taskMap['__default'];
		} else {
			return $this->taskNotFound( $this->_task );
		}

		if ($this->accessCheck( $doTask )) {
			return call_user_func( array( &$this, $doTask ) );
		} else {
			return $this->notAllowed( $task );
		}
	}
	/**
	 * Get the last task that was to be performed
	 * @return string The task that was or is being performed
	 */
	function getTask() {
		return $this->_task;
	}
	/**
	 * Basic method if the task is not found
	 * @param string The task
	 * @return null
	 */
	function taskNotFound( $task ) {
		echo JText::_( 'Task' ) .' ' . $task . ' '. JText::_( 'not found' );
		return null;
	}
	/**
	 * Basic method if the registered method is not found
	 * @param string The name of the method in the derived class
	 * @return null
	 */
	function methodNotFound( $name ) {
		echo JText::_( 'Method' ) .' ' . $name . ' '. JText::_( 'not found' );
		return null;
	}
	/**
	 * Basic method if access is not permitted to the task
	 * @param string The name of the method in the derived class
	 * @return null
	 */
	function notAllowed( $name ) {
		echo JText::_( 'ALERTNOTAUTH' );

		return null;
	}
}


/**
* Plugin handler
* @package Joomla
* @since 1.0
*/
class mosMambotHandler {
	/** @var array An array of functions in event groups */
	var $_events	= null;
	/** @var array An array of lists */
	var $_lists		= null;
	/** @var array An array of mambots */
	var $_bots		= null;
	/** @var int Index of the mambot being loaded */
	var $_loading	= null;

	/**
	* Constructor
	*/
	function mosMambotHandler() {
		$this->_events = array();
	}
	/**
	* Loads all the bot files for a particular group
	* @param string The group name, relates to the sub-directory in the mambots directory
	*/
	function loadBotGroup( $group ) {
		global $database, $my, $mosConfig_absolute_path;
		global $_MAMBOTS;

		$group = trim( $group );
		if (is_object( $my )) {
			$gid = $my->gid;
		} else {
			$gid = 0;
		}

		$group = trim( $group );

		switch ( $group ) {
			case 'content':
				$query = "SELECT folder, element, published, params"
				. "\n FROM #__mambots"
				. "\n WHERE access <= $gid"
				. "\n AND folder = '$group'"
				. "\n ORDER BY ordering"
				;
				break;

			default:
				$query = "SELECT folder, element, published, params"
				. "\n FROM #__mambots"
				. "\n WHERE published >= 1"
				. "\n AND access <= $gid"
				. "\n AND folder = '$group'"
				. "\n ORDER BY ordering"
				;
				break;
		}
		$database->setQuery( $query );

		if (!($bots = $database->loadObjectList())) {
			//echo "Error loading Mambots: " . $database->getErrorMsg();
			return false;
		}
		$n = count( $bots);
		for ($i = 0; $i < $n; $i++) {
			$this->loadBot( $bots[$i]->folder, $bots[$i]->element, $bots[$i]->published, $bots[$i]->params );
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
	function loadBot( $folder, $element, $published, $params='' ) {
		global $mosConfig_absolute_path;
		global $_MAMBOTS;

		$path = $mosConfig_absolute_path . '/mambots/' . $folder . '/' . $element . '.php';
		if (file_exists( $path )) {
			$this->_loading = count( $this->_bots );
			$bot = new stdClass;
			$bot->folder 	= $folder;
			$bot->element 	= $element;
			$bot->published = $published;
			$bot->lookup 	= $folder . '/' . $element;
			$bot->params 	= $params;
			$this->_bots[] 	= $bot;

			require_once( $path );

			$this->_loading = null;
		}
	}
	/**
	* Registers a function to a particular event group
	* @param string The event name
	* @param string The function name
	*/
	function registerFunction( $event, $function ) {
		$this->_events[$event][] = array( $function, $this->_loading );
	}
	/**
	* Makes a option for a particular list in a group
	* @param string The group name
	* @param string The list name
	* @param string The value for the list option
	* @param string The text for the list option
	*/
	function addListOption( $group, $listName, $value, $text='' ) {
		$this->_lists[$group][$listName][] = mosHTML::makeOption( $value, $text );
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
	* @param boolean True is unpublished bots are to be processed
	* @return array An array of results from each function call
	*/
	function trigger( $event, $args=null, $doUnpublished=false ) {
		$result = array();

		if ($args === null) {
			$args = array();
		}
		if ($doUnpublished) {
			// prepend the published argument
			array_unshift( $args, null );
		}
		if (isset( $this->_events[$event] )) {
			foreach ($this->_events[$event] as $func) {
				if (function_exists( $func[0] )) {
					if ($doUnpublished) {
						$args[0] = $this->_bots[$func[1]]->published;
						$result[] = call_user_func_array( $func[0], $args );
					} else if ($this->_bots[$func[1]]->published) {
						$result[] = call_user_func_array( $func[0], $args );
					}
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
		$doUnpublished=false;

		$args =& func_get_args();
		array_shift( $args );

		if (isset( $this->_events[$event] )) {
			foreach ($this->_events[$event] as $func) {
				if (function_exists( $func[0] )) {
					if ($this->_bots[$func[1]]->published) {
						return call_user_func_array( $func[0], $args );
					}
				}
			}
		}
		return null;
	}
}
?>
