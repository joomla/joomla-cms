<?php

/**
* @version $Id: legacy.php 1525 2005-12-21 21:08:29Z Jinx $
* @package JoomlaLegacy
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Legacy class, derive from JApplication instead
* @deprecated As of version 1.1
* @package JoomlaLegacy
*/
class mosMainFrame extends JApplication {
	/**
	 * Class constructor
	 * @param database A database connection object
	 * @param string The url option [DEPRECATED]
	 * @param string The path of the mos directory [DEPRECATED]
	 */
	function __construct( &$db, $option, $basePath=null, $client=0 ) {
		parent::__construct( $option, $client );
	}

	/**
	 * Class constructor
	 * @param database A database connection object
	 * @param string The url option [DEPRECATED]
	 * @param string The path of the mos directory [DEPRECATED]
	 */
	function mosMainFrame( &$db, $option, $basePath=null, $client=0 ) {
		parent::__construct( $option, $client );
	}

	/**
	 * Initialises the user session
	 *
	 * Old sessions are flushed based on the configuration value for the cookie
	 * lifetime. If an existing session, then the last access time is updated.
	 * If a new session, a session id is generated and a record is created in
	 * the mos_sessions table.
	 */
	function initSession( ) {
		//do nothing, contructor handles session creation
	}

	/**
	 * Gets the base path for the client
	 * @param mixed A client identifier
	 * @param boolean True (default) to add traling slash
	 */
	function getBasePath( $client=0, $addTrailingSlash=true ) {
		global $mosConfig_absolute_path;

		switch ($client) {
			case '0':
			case 'site':
			case 'front':
			default:
				return mosPathName( $mosConfig_absolute_path, $addTrailingSlash );
				break;

			case '2':
			case 'installation':
				return mosPathName( $mosConfig_absolute_path . '/installation', $addTrailingSlash );
				break;

			case '1':
			case 'admin':
			case 'administrator':
				return mosPathName( $mosConfig_absolute_path . '/administrator', $addTrailingSlash );
				break;

		}
	}
}

/**
* Legacy class, derive from JModel instead
* @deprecated As of version 1.1
* @package JoomlaLegacy
*/
jimport( 'joomla.models.model' );
/**
 * @package Joomla
 * @deprecated As of version 1.1
 */
class mosDBTable extends JModel {
	/**
	 * Constructor
	 */
	function __construct($table, $key, &$db) {
		parent::__construct( $table, $key, $db );
	}

	function mosDBTable($table, $key, &$db) {
		parent::__construct( $table, $key, $db );
	}
}

/**
 * @package JoomlaLegacy
 * @deprecated As of version 1.1, use JCategoryModel instead
 */
class mosCategory extends JCategoryModel {
	/**
	 * Constructor
	 */
	function __construct( &$db) {
		parent::__construct( $db );
	}

	function mosCategory(&$db) {
		parent::__construct( $db );
	}
}

/**
 * @package JoomlaLegacy
 * @package Joomla, use JComponentModel instead
 * @deprecated As of version 1.1
 */
class mosComponent extends JComponentModel {
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosComponent( &$db) {
		parent::__construct( $db );
	}
}

/**
 * @package JoomlaLegacy
 * @deprecated As of version 1.1, use JCategoryModel instead
 */
class mosContent extends JContentModel {
	/**
	 * Constructor
	 */
	function __construct( &$db ) {
		parent::__construct( $db );
	}

	function mosComponent( &$db ) {
		parent::__construct($db );
	}
}

/**
 * @package JoomlaLegacy
 * @deprecated As of version 1.1, use JMambotModel instead
 */
class mosMambot extends JMambotModel {
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosMambot(&$db) {
		parent::__construct( $db );
	}
}

/**
 * @package JoomlaLegacy
 * @deprecated As of version 1.1, use JMenuModel instead
 */
class mosMenu extends JMenuModel {
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosMenu(&$db) {
		parent::__construct( $db );
	}
}

/**
 * @package JoomlaLegacy
 * @deprecated As of version 1.1, use JMenuModel instead
 */
class mosModule extends JModuleModel {
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosModule(&$db) {
		parent::__construct( $db );
	}
}

/**
 * @package JoomlaLegacy
 * @deprecated As of version 1.1, use JSectionModel instead
 */
class mosSection extends JSectionModel {
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosSection(&$db) {
		parent::__construct( $db );
	}
}

/**
 * @package JoomlaLegacy
 * @deprecated As of version 1.1, use JMenuModel instead
 */
class mosSession extends JSessionModel {
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct(  $db );
	}

	function mosSession(&$db) {
		parent::__construct( $db );
	}
}

/**
 * @package JoomlaLegacy
 * @deprecated As of version 1.1, use JUserModel instead
 */
class mosUser extends JUserModel {
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosUser(&$db) {
		parent::__construct( $db);
	}
}

jimport( 'joomla.database.database' );
jimport( 'joomla.database.adapters.mysql' );

/**
 * @package JoomlaLegacy
 * @deprecated As of version 1.1
 */
class database extends JDatabaseMySQL {
	function __construct ($host='localhost', $user, $pass, $db='', $table_prefix='', $offline = true) {
		parent::__construct( $host, $user, $pass, $db, $table_prefix );
	}
}

/**
 * Legacy class, use JFactory::getCache instead
 * @package JoomlaLegacy
 * @deprecated As of version 1.1
 */
class mosCache {
	/**
	* @return object A function cache object
	*/
	function &getCache(  $group=''  ) {
		return JFactory::getCache($group);
	}
	/**
	* Cleans the cache
	*/
	function cleanCache( $group=false ) {
		$cache =& JFactory::getCache($group);
		$cache->cleanCache($group);
	}
}

/**
 * Legacy class, use JProfiler instead
 * @package JoomlaLegacy
 * @deprecated  As of version 1.1
 */
class mosProfiler extends JProfiler {
	/**
	* @return object A function cache object
	*/
	function JProfiler (  $prefix=''  ) {
		parent::__construct($prefix);
	}
}

/**
 * Legacy class, use JParameters instead
 * @package JoomlaLegacy
 * @deprecated As of version 1.1
 */
class mosParameters extends JParameters {

	/**
	* @param string The raw parms text
	* @param string Path to the xml setup file
	* @param string The type of setup file
	*/
	function __construct($text, $path = '', $type = 'component') {
		$this->_params = $this->parse($text);
		$this->_raw = $text;
		$this->_path = $path;
		$this->_type = $type;
	}

}

/**
 * Legacy class, will be replaced by full MVC implementation in 1.2
 * @package JoomlaLegacy
 * @deprecated As of version 1.1
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
 * Legacy class, use JEventDispatcher instead
 * @package JoomlaLegacy
 * @deprecated As of version 1.1
 */
class mosMambotHandler extends JEventDispatcher {
	function __construct() {
		parent::__construct();
	}

	/**
	* Loads all the bot files for a particular group
	* @param string The group name, relates to the sub-directory in the mambots directory
	*/
	function loadBotGroup( $group ) {
		return JPluginHelper::importGroup($group);
	}
	/**
	 * Loads the bot file
	 * @param string The folder (group)
	 * @param string The elements (name of file without extension)
	 * @param int Published state
	 * @param string The params for the bot
	 */
	function loadBot( $folder, $element, $published, $params='' ) {
		return JPluginHelper::import($folder, $element, $published, $params='' );
	}

	/**
	* Registers a function to a particular event group
	*
	* @param string The event name
	* @param string The function name
	*/
	function registerEvent( $event, $function ) {
		$this->attach(array( $event => $function ));
	}
}

/**
 * Legacy class, removed
 * @package JoomlaLegacy
 * @deprecated As of version 1.1
 */
class mosEmpty {
	function def( $key, $value='' ) {
		return 1;
	}
	function get( $key, $default='' ) {
		return 1;
	}
}

/**
 * Legacy global
 * 	use JApplicaiton->registerEvent and JApplication->triggerEvent for event handling
 *  use JPlugingHelper::importGroup and JPluginHelper::import to load bot code
 *  @deprecated As of version 1.1
 */
$_MAMBOTS = new mosMambotHandler();

?>