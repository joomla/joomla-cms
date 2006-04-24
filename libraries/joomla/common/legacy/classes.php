<?php
/**
* @version $Id: legacy.php 1525 2005-12-21 21:08:29Z Jinx $
* @package Joomla.Legacy
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.database.database' );
jimport( 'joomla.database.database.mysql' );
jimport( 'joomla.database.table' );
jimport( 'joomla.database.table.*' );

/**
 * Legacy class, derive from JApplication instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosMainFrame extends JApplication
{
	/**
	 * Class constructor
	 * @param database A database connection object
	 * @param string The url option [DEPRECATED]
	 * @param string The path of the mos directory [DEPRECATED]
	 */
	function __construct( &$db, $option, $basePath=null, $client=0 ) {
		parent::__construct( $client );
	}

	/**
	 * Class constructor
	 * @param database A database connection object
	 * @param string The url option [DEPRECATED]
	 * @param string The path of the mos directory [DEPRECATED]
	 */
	function mosMainFrame( &$db, $option, $basePath=null, $client=0 ) {
		parent::__construct( $client );
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

	/**
	* Depreacted, use JDocument->setTitle instead or override in your application class
	* @since 1.5
	*/
	function setPageTitle( $title=null ) {
		$document=& $this->getDocument();
		$document->setTitle($title);
	}

	/**
	* Depreacted, use JDocument->getTitle instead or override in your application class
	* @since 1.5
	*/
	function getPageTitle() {
		$document=& $this->getDocument();
		return $document->getTitle();
	}
}

/**
 * Legacy class, derive from JTable instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosDBTable extends JTable
{
	/**
	 * Constructor
	 */
	function __construct($table, $key, &$db) {
		parent::__construct( $table, $key, $db );
	}

	function mosDBTable($table, $key, &$db) {
		parent::__construct( $table, $key, $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableCategory instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosCategory extends JTableCategory
{
	/**
	 * Constructor
	 */
	function __construct( &$db) {
		parent::__construct( $db );
	}

	function mosCategory(&$db) {
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableComponent instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosComponent extends JTableComponent
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosComponent( &$db) {
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableContent instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosContent extends JTableContent
{
	/**
	 * Constructor
	 */
	function __construct( &$db ) {
		parent::__construct( $db );
	}

	function mosComponent( &$db ) {
		parent::__construct($db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, replaced by JTablePlugin
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosMambot extends JTablePlugin
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosMambot(&$db) {
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableMenu instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosMenu extends JTableMenu
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosMenu(&$db) {
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableModule instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosModule extends JTableModule
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosModule(&$db) {
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableSection instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosSection extends JTableSection
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosSection(&$db) {
		parent::__construct( $db );
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableSession instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosSession extends JTableSession
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct(  $db );
	}

	function mosSession(&$db) {
		parent::__construct( $db );
	}

	/**
	 * Encodes a session id
	 */
	function hash( $value )
	{
		global $mainframe;

		if (phpversion() <= '4.2.1') {
			$agent = getenv( 'HTTP_USER_AGENT' );
		} else {
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}

		return md5( $agent . $mainframe->getCfg('secret') . $value . $_SERVER['REMOTE_ADDR'] );
	}

	/**
	 * Set the information to allow a session to persist
	 */
	function persist()
	{
		global $mainframe;

		$usercookie = mosGetParam( $_COOKIE, 'usercookie', null );
		if ($usercookie) {
			// Remember me cookie exists. Login with usercookie info.
			$mainframe->login( $usercookie['username'], $usercookie['password'] );
		}
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JTableUser instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosUser extends JTableUser
{
	/**
	 * Constructor
	 */
	function __construct(&$db) {
		parent::__construct( $db );
	}

	function mosUser(&$db) {
		parent::__construct( $db);
	}

	/**
	 * Legacy Method, use reorder() instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use publish() instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 ) {
		$this->publish( $cid, $publish, $user_id );
	}
}

/**
 * Legacy class, use JDatabase
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class database extends JDatabaseMySQL
{
	function __construct ($host='localhost', $user, $pass, $db='', $table_prefix='', $offline = true) {
		parent::__construct( $host, $user, $pass, $db, $table_prefix );
	}
}

 /**
 * Legacy class, use & JFactory::getCache instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosCache
{
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
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
jimport('joomla.utilities.profiler');
class mosProfiler extends JProfiler
{
	/**
	* @return object A function cache object
	*/
	function JProfiler (  $prefix=''  ) {
		parent::__construct($prefix);
	}
}

 /**
 * Legacy class, use JParameter instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosParameters extends JParameter
{
	/**
	* @param string The raw parms text
	* @param string Path to the xml setup file
	* @param string The type of setup file
	*/
	function __construct($text, $path = '', $type = 'component') {
		parent::__construct($text, $path);
	}

	/**
	 * Legacy function, use JParameter->toObject instead
	 *
	 * @deprecated As of version 1.5
	 */
	function toObject() {
		$this->toObject();
	}

	/**
	 * Legacy function, use JParameter->toArray instead
	 *
	 * @deprecated As of version 1.5
	 */
	function toArray() {
		$this->toArray();
	}

	/**
	 * Parse an .ini string, based on phpDocumentor phpDocumentor_parse_ini_file function
	 *
	 * @access public
	 * @param mixed The ini string or array of lines
	 * @param boolean add an associative index for each section [in brackets]
	 * @return object
	 */
	function parse($txt, $process_sections = false, $asArray = false)
	{
		$this->loadINI($txt);

		if($asArray) {
			return $this->toArray();
		}

		return $this->toObject( );
	}

	/**
	* Special handling for textarea param
	*/
	function textareaHandling( &$txt ) {
		$total = count( $txt );
		for( $i=0; $i < $total; $i++ ) {
			if ( strstr( $txt[$i], "\n" ) ) {
				$txt[$i] = str_replace( "\n", '<br />', $txt[$i] );
			}
		}
		$txt = implode( "\n", $txt );

		return $txt;
	}
}

/**
 * Legacy class, will be replaced by full MVC implementation in 1.2
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosAbstractTasker
{
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
		$this->_taskMap = array();
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
			josRedirect( $this->_redirect, $this->_message );
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
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosMambotHandler extends JEventDispatcher
{
	function __construct() {
		parent::__construct();
	}

	/**
	* Loads all the bot files for a particular group
	* @param string The group name, relates to the sub-directory in the plugins directory
	*/
	function loadBotGroup( $group ) {
		return JPluginHelper::importPlugin($group);
	}
	/**
	 * Loads the bot file
	 * @param string The folder (group)
	 * @param string The elements (name of file without extension)
	 * @param int Published state
	 * @param string The params for the bot
	 */
	function loadBot( $folder, $element, $published, $params='' ) {
		return JPluginHelper::_import($folder, $element, $published, $params='' );
	}

	/**
	* Registers a function to a particular event group
	*
	* @param string The event name
	* @param string The function name
	*/
	function registerFunction( $event, $function ) {
		 JApplication::registerEvent( $event, $function );
	}

	/**
	* Deprecated, use JEventDispatcher->trigger intead and handle return values
	* in your code
	* @since 1.5
	*/
	function call($event)
	{
		$args = & func_get_args();
		array_shift($args);

		$retArray = $this->trigger( $event, $args );
		return $retArray[0];
	}
}

/**
 * Legacy class, removed
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class mosEmpty
{
	function def( $key, $value='' ) {
		return 1;
	}
	function get( $key, $default='' ) {
		return 1;
	}
}


/**
 * Legacy class, removed
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
class MENU_Default
{
	function MENU_Default() {
		JMenuBar::startTable();
		JMenuBar::publishList();
		JMenuBar::unpublishList();
		JMenuBar::addNew();
		JMenuBar::editList();
		JMenuBar::deleteList();
		JMenuBar::spacer();
		JMenuBar::endTable();
	}
}

/**
 * Legacy class, use JPanel instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 * @subpackage	1.5
 */
jimport('joomla.presentation.pane');
class mosTabs extends JPaneTabs
{
	var $useCookies = false;

	function __construct( $useCookies, $xhtml = null) {
		parent::__construct( array('useCookies' => $useCookies) );
	}

	function startTab( $tabText, $paneid ) {
		$this->startPanel( $tabText, $paneid);
	}

	function endTab()  {
		$this->endPanel();
	}
}
?>