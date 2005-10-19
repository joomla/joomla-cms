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

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );
define( '_MOS_MAMBO_INCLUDED', 1 );

/**
 * Page generation time
 * @package Joomla
 */
class mosProfiler {
	/** @var int Start time stamp */
	var $start=0;
	/** @var string A prefix for mark messages */
	var $prefix='';

	/**
	 * Constructor
	 * @param string A prefix for mark messages
	 */
	function mosProfiler( $prefix='' ) {
		$this->start = $this->getmicrotime();
		$this->prefix = $prefix;
	}

	/**
	 * @return string A format message of the elapsed time
	 */
	function mark( $label ) {
		return sprintf ( "\n<div class=\"profiler\">$this->prefix %.3f $label</div>", $this->getmicrotime() - $this->start );
	}

	/**
	 * @return float The current time in milliseconds
	 */
	function getmicrotime(){
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}
}

if (phpversion() < '4.2.0') {
	require_once( $mosConfig_absolute_path . '/includes/compat.php41x.php' );
}
if (phpversion() < '4.3.0') {
	require_once( $mosConfig_absolute_path . '/includes/compat.php42x.php' );
}
if (in_array( 'globals', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Global variable hack attempted.' );
}
if (in_array( '_post', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Post variable hack attempted.' );
}
if (version_compare( phpversion(), '5.0' ) < 0) {
	require_once( $mosConfig_absolute_path . '/includes/compat.php50x.php' );
}

@set_magic_quotes_runtime( 0 );

if (@$mosConfig_error_reporting === 0) {
	error_reporting( 0 );
} else if (@$mosConfig_error_reporting > 0) {
	error_reporting( $mosConfig_error_reporting );
}

require_once( $mosConfig_absolute_path . '/includes/version.php' );
require_once( $mosConfig_absolute_path . '/includes/joomla.factory.php' );
require_once( $mosConfig_absolute_path . '/includes/joomla.files.php' );
require_once( $mosConfig_absolute_path . '/includes/database.php' );
require_once( $mosConfig_absolute_path . '/includes/gacl.class.php' );
require_once( $mosConfig_absolute_path . '/includes/gacl_api.class.php' );
require_once( $mosConfig_absolute_path . '/includes/phpmailer/class.phpmailer.php' );
require_once( $mosConfig_absolute_path . '/includes/joomla.xml.php' );
require_once( $mosConfig_absolute_path . '/includes/phpInputFilter/class.inputfilter.php' );

$database = new database( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );
if ($database->getErrorNum()) {
	$mosSystemError = $database->getErrorNum();
	$basePath = dirname( __FILE__ );
	include $basePath . '/../configuration.php';
	include $basePath . '/../offline.php';
	exit();
}
$database->debug( $mosConfig_debug );
$acl = new gacl_api();

/**
 * @package Joomla
 * @abstract
 */
class mosAbstractLog {
	/** @var array */
	var $_log	= null;

	/**
	 * Constructor
	 */
	function mosAbstractLog() {
		$this->__constructor();
	}

	/**
	 * Generic constructor
	 */
	function __constructor() {
		$this->_log = array();
	}

	/**
	 * @param string Log message
	 * @param boolean True to append to last message
	 */
	function log( $text, $append=false ) {
		$n = count( $this->_log );
		if ($append && $n > 0) {
			$this->_log[count( $this->_log )-1] .= $text;
		} else {
			$this->_log[] = $text;
		}
	}

	/**
	 * @param string The glue for each log item
	 * @return string Returns the log
	 */
	function getLog( $glue='<br/>', $truncate=9000, $htmlSafe=false ) {
		$logs = array();
		foreach ($this->_log as $log) {
			if ($htmlSafe) {
				$log = htmlspecialchars( $log );
			}
			$logs[] = substr( $log, 0, $truncate );
		}
		return  implode( $glue, $logs );
	}
}

/**
 * Task routing class
 * @package Joomla
 * @abstract
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
		global $_LANG;
		echo $_LANG->_( 'Task' ) .' ' . $task . ' '. $_LANG->_( 'not found' );
		return null;
	}
	/**
	 * Basic method if the registered method is not found
	 * @param string The name of the method in the derived class
	 * @return null
	 */
	function methodNotFound( $name ) {
		global $_LANG;
		echo $_LANG->_( 'Method' ) .' ' . $name . ' '. $_LANG->_( 'not found' );
		return null;
	}
	/**
	 * Basic method if access is not permitted to the task
	 * @param string The name of the method in the derived class
	 * @return null
	 */
	function notAllowed( $name ) {
		echo $GLOBALS['_LANG']->_( 'NOT_AUTH' );

		return null;
	}
}


/**
* Class to support function caching
* @package Joomla
*/
class mosCache {
	/**
	* @return object A function cache object
	*/
	function &getCache(  $group=''  ) {
		global $mosConfig_absolute_path, $mosConfig_caching, $mosConfig_cachepath, $mosConfig_cachetime;

		require_once( $mosConfig_absolute_path . '/includes/Cache/Lite/Function.php' );

		$options = array(
			'cacheDir' 		=> $mosConfig_cachepath . '/',
			'caching' 		=> $mosConfig_caching,
			'defaultGroup' 	=> $group,
			'lifeTime' 		=> $mosConfig_cachetime
		);
		$cache = new Cache_Lite_Function( $options );
		return $cache;
	}
	/**
	* Cleans the cache
	*/
	function cleanCache( $group=false ) {
		global $mosConfig_caching;
		if ($mosConfig_caching) {
			$cache =& mosCache::getCache( $group );
			$cache->clean( $group );
		}
	}
}
/**
* Joomla! Mainframe class
*
* Provide many supporting API functions
* @package Joomla
*/
class mosMainFrame {
	/** @var database Internal database class pointer */
	var $_db				= null;
	/** @var object An object of configuration variables */
	var $_config			= null;
	/** @var object An object of path variables */
	var $_path				= null;
	/** @var mosSession The current session */
	var $_session			= null;
	/** @var string The current template */
	var $_template			= null;
	/** @var array An array to hold global user state within a session */
	var $_userstate			= null;
	/** @var array An array of page meta information */
	var $_head				= null;
	/** @var string Custom html string to append to the pathway */
	var $_custom_pathway	= null;
	/** @var boolean True if in the admin client */
	var $_isAdmin 			= false;

	/**
	* Class constructor
	* @param database A database connection object
	* @param string The url option
	* @param string The path of the mos directory
	*/
	function mosMainFrame( &$db, $option, $basePath, $isAdmin=false ) {
		$this->_db =& $db;

		// load the configuration values
		$this->_setTemplate( $isAdmin );
		$this->_setAdminPaths( $option, $this->getCfg( 'absolute_path' ) );
		if (isset( $_SESSION['session_userstate'] )) {
			$this->_userstate =& $_SESSION['session_userstate'];
		} else {
			$this->_userstate = array();
		}
		$this->_head = array();
		$this->_head['title'] 	= $GLOBALS['mosConfig_sitename'];
		$this->_head['meta'] 	= array();
		$this->_head['custom'] 	= array();
		
		//set the admin check
		$this->_isAdmin 		= (boolean) $isAdmin;
	}
	/**
	* @param string
	*/
	function setPageTitle( $title=null ) {
		if (@$GLOBALS['mosConfig_pagetitles']) {
			$title = trim( htmlspecialchars( $title ) );
			$this->_head['title'] = $title ? $GLOBALS['mosConfig_sitename'] . ' - '. $title : $GLOBALS['mosConfig_sitename'];
		}
	}
	/**
	* @param string The value of the name attibute
	* @param string The value of the content attibute
	* @param string Text to display before the tag
	* @param string Text to display after the tag
	*/
	function addMetaTag( $name, $content, $prepend='', $append='' ) {
		$name = trim( htmlspecialchars( $name ) );
		$content = trim( htmlspecialchars( $content ) );
		$prepend = trim( $prepend );
		$append = trim( $append );
		$this->_head['meta'][] = array( $name, $content, $prepend, $append );
	}
	/**
	* @param string The value of the name attibute
	* @param string The value of the content attibute to append to the existing
	* Tags ordered in with Site Keywords and Description first
	*/
	function appendMetaTag( $name, $content ) {
		$name = trim( htmlspecialchars( $name ) );
		$n = count( $this->_head['meta'] );
		for ($i = 0; $i < $n; $i++) {
			if ($this->_head['meta'][$i][0] == $name) {
				$content = trim( htmlspecialchars( $content ) );
				if ( $content ) {
					if ( !$this->_head['meta'][$i][1] ) {
						$this->_head['meta'][$i][1] = $content ;
					} else {
						$this->_head['meta'][$i][1] = $content .', '. $this->_head['meta'][$i][1];
					}
				}
				return;
			}
		}
		$this->addMetaTag( $name , $content );
	}

	/**
	* @param string The value of the name attibute
	* @param string The value of the content attibute to append to the existing
	*/
	function prependMetaTag( $name, $content ) {
		$name = trim( htmlspecialchars( $name ) );
		$n = count( $this->_head['meta'] );
		for ($i = 0; $i < $n; $i++) {
			if ($this->_head['meta'][$i][0] == $name) {
				$content = trim( htmlspecialchars( $content ) );
				$this->_head['meta'][$i][1] = $content . $this->_head['meta'][$i][1];
				return;
			}
		}
		$this->addMetaTag( $name, $content );
	}
	/**
	 * Adds a custom html string to the head block
	 * @param string The html to add to the head
	 */
	function addCustomHeadTag( $html ) {
		$this->_head['custom'][] = trim( $html );
	}
	/**
	* @return string
	*/
	function getHead() {
		$head = array();
		$head[] = '<title>' . $this->_head['title'] . '</title>';
		foreach ($this->_head['meta'] as $meta) {
			if ($meta[2]) {
				$head[] = $meta[2];
			}
			$head[] = '<meta name="' . $meta[0] . '" content="' . $meta[1] . '" />';
			if ($meta[3]) {
				$head[] = $meta[3];
			}
		}
		foreach ($this->_head['custom'] as $html) {
			$head[] = $html;
		}
		return implode( "\n", $head ) . "\n";
	}
	/**
	* @return string
	*/
	function getPageTitle() {
		return $this->_head['title'];
	}

	/**
	* @return string
	*/
	function getCustomPathWay() {
		return $this->_custom_pathway;
	}

	function appendPathWay( $html ) {
	$this->_custom_pathway[] = $html;
  }

  /**
	* Gets the value of a user state variable
	* @param string The name of the variable
	*/
	function getUserState( $var_name ) {
		if (is_array( $this->_userstate )) {
			return mosGetParam( $this->_userstate, $var_name, null );
		} else {
			return null;
		}
	}
	/**
	* Gets the value of a user state variable
	* @param string The name of the user state variable
	* @param string The name of the variable passed in a request
	* @param string The default value for the variable if not found
	*/
	function getUserStateFromRequest( $var_name, $req_name, $var_default=null ) {
		if (is_array( $this->_userstate )) {
			if (isset( $_REQUEST[$req_name] )) {
				$this->setUserState( $var_name, $_REQUEST[$req_name] );
			} else if (!isset( $this->_userstate[$var_name] )) {
				$this->setUserState( $var_name, $var_default );
			}
			return $this->_userstate[$var_name];
		} else {
			return null;
		}
	}
	/**
	* Sets the value of a user state variable
	* @param string The name of the variable
	* @param string The value of the variable
	*/
	function setUserState( $var_name, $var_value ) {
		if (is_array( $this->_userstate )) {
			$this->_userstate[$var_name] = $var_value;
		}
	}
	/**
	 * Initialises the user session
	 *
	 * Old sessions are flushed based on the configuration value for the cookie
	 * lifetime. If an existing session, then the last access time is updated.
	 * If a new session, a session id is generated and a record is created in
	 * the mos_sessions table.
	 * @param string The session persistance type: cookie|php
	 */
	function initSession( $type='cookie' ) {
		$session =& $this->_session;
		$session = new mosSession( $this->_db, $type );
		$session->purge( intval( $this->getCfg( 'lifetime' ) ) );

		$sessioncookie = $session->restore();

		if ($session->load( $session->hash( $sessioncookie ) )) {
			// Session cookie exists, update time in session table
			$session->update();
		} else {
			if (!$session->insert()) {
				die( $session->getError() );
			}
			$session->persist();
		}
	}

	/**
	* Login validation function
	*
	* Username and encoded password are passed the the onLoginUser event who
	* is responsible for the user validation.
	* A successful validation updates the current session record with the
	* users details.
	*/
	function login( $username=null,$passwd=null ) {
		global $database, $mainframe, $acl, $_MAMBOTS, $_LANG;

		//$usercookie = mosGetParam( $_COOKIE, 'usercookie', '' );
		//$sessioncookie = mosGetParam( $_COOKIE, 'sessioncookie', '' );
		if (!$username || !$passwd) {
			$username 	= $database->getEscaped( trim( mosGetParam( $_POST, 'username', '' ) ) );
			$passwd 	= $database->getEscaped( trim( mosGetParam( $_POST, 'passwd', '' ) ) );
			$bypost 	= 1;
		}

		if (!$username || !$passwd) {
			// Error check if still no username or password values
			mosErrorAlert( $_LANG->_( 'LOGIN_INCOMPLETE' ) );
			
		} else {
			
			//load user bot group
			$_MAMBOTS->loadBotGroup( 'user' );

			//trigger the onBeforeStoreUser event
			$results = $_MAMBOTS->trigger( 'onLoginUser', array( $username, $passwd ) );

			// TODO: Handle multiple authentication checks
			if ($results[0] > 0) {
				
				$user = new mosUser( $database );
				$user->load( intval( $results[0] ) );

				// check to see if user is blocked from logging in
				if ($user->block == 1) {
					mosErrorAlert( $_LANG->_( 'LOGIN_BLOCKED' ) );
				}
				// fudge the group stuff
				$grp 		= $acl->getAroGroup( $user->id );
				$row->gid 	= 1;

				if ( $acl->is_group_child_of( $grp->name, 'Registered', 'ARO' ) || $acl->is_group_child_of( $grp->name, 'Public Backend', 'ARO' )) {
					// fudge Authors, Editors, Publishers and Super Administrators into the Special Group
					$user->gid = 2;
				}
				$user->usertype = $grp->name;

				// access control check
				$client = $this->_isAdmin ? 'administrator' : 'site';
				//if ( !$acl->acl_check( 'login', $client, 'users', $user->usertype ) ) {
				//	return false;
				//}

				$session =& $this->_session;
				$session->guest 	= 0;
				$session->username 	= $user->username;
				$session->userid 	= intval( $user->id );
				$session->usertype 	= $user->usertype;
				$session->gid 		= intval( $user->gid );

				$session->store();

				$user->setLastVisit();

				$remember = trim( mosGetParam( $_POST, 'remember', '' ) );
				if ($remember == 'yes') {
					$session->remember( $user->username, $user->password );
				}

				//mosCache::cleanCache('com_content');
				mosCache::cleanCache();
				return true;
			} else {
				return false;
			}
		}
	}
	
	/**
	* User logout
	*
	* Passed the current user information to the onLogoutUser event and reverts the current
	* session record back to 'anonymous' parameters
	*/
	function logout() {
		global $_MAMBOTS;

		//load user bot group
		$_MAMBOTS->loadBotGroup( 'user' );

		//get the user
		$user = $this->getUser();

		//trigger the onLogOutUser event
		$results = $_MAMBOTS->trigger( 'onLogoutUser', array( &$user ));

		//mosCache::cleanCache('com_content');
		mosCache::cleanCache();

		$session =& $this->_session;
		$session->destroy();
	}
	/**
	* @return mosUser A user object with the information from the current session
	*/
	function getUser() {
		global $database;

		$user = new mosUser( $this->_db );

		$user->id 			= intval( $this->_session->userid );
		$user->username 	= $this->_session->username;
		$user->usertype 	= $this->_session->usertype;
		$user->gid 			= intval( $this->_session->gid );

		if ($user->id) {
			$query = "SELECT params"
			. "\n FROM #__users"
			. "\n WHERE id = ". intval( $user->id )
			;
			$database->setQuery( $query );
			$params = $database->loadResult();
		} else {
			$params = '';
		}
		$user->params = $params;

		return $user;
	}
	/**
	 * @param string The name of the variable (from configuration.php)
	 * @return mixed The value of the configuration variable or null if not found
	 */
	function getCfg( $varname ) {
		$varname = 'mosConfig_' . $varname;
		if (isset( $GLOBALS[$varname] )) {
			return $GLOBALS[$varname];
		} else {
			return null;
		}
	}

	function _setTemplate( $isAdmin=false ) {
		global $Itemid;
		$mosConfig_absolute_path = $this->getCfg( 'absolute_path' );

		// Default template
		$query = "SELECT template"
		. "\n FROM #__templates_menu"
		. "\n WHERE client_id = 0"
		. "\n AND menuid = 0"
		;
		$this->_db->setQuery( $query );
		$cur_template = $this->_db->loadResult();

		// Assigned template
		if (isset($Itemid) && $Itemid!="" && $Itemid!=0) {
			$query = "SELECT template"
			. "\n FROM #__templates_menu"
			. "\n WHERE client_id = 0"
			. "\n AND menuid = $Itemid"
			. "\n LIMIT 1"
			;
			$this->_db->setQuery( $query );
			$cur_template = $this->_db->loadResult() ? $this->_db->loadResult() : $cur_template;
		}

		if ($isAdmin) {
			$query = "SELECT template"
			. "\n FROM #__templates_menu"
			. "\n WHERE client_id = 1"
			. "\n AND menuid = 0"
			;
			$this->_db->setQuery( $query );
			$cur_template = $this->_db->loadResult();
			$path = "$mosConfig_absolute_path/administrator/templates/$cur_template/index.php";
			if (!file_exists( $path )) {
				$cur_template = 'joomla_admin';
			}
		} else {
			// TemplateChooser Start
			$jos_user_template = mosGetParam( $_COOKIE, 'jos_user_template', '' );
			$jos_change_template = mosGetParam( $_REQUEST, 'jos_change_template', $jos_user_template );
			if ($jos_change_template) {
				// check that template exists in case it was deleted
				if (file_exists( $mosConfig_absolute_path .'/templates/'. $jos_change_template .'/index.php' )) {
					$lifetime = 60*10;
					$cur_template = $jos_change_template;
					setcookie( 'jos_user_template', "$jos_change_template", time()+$lifetime);
				} else {
					setcookie( 'jos_user_template', '', time()-3600 );
				}
			}
			// TemplateChooser End
		}

		$this->_template = $cur_template;
	}

	function getTemplate() {
		return $this->_template;
	}

	/**
	* Determines the paths for including engine and menu files
	* @param string The current option used in the url
	* @param string The base path from which to load the configuration file
	*/
	function _setAdminPaths( $option, $basePath='.' ) {
		$option = strtolower( $option );
		$this->_path = new stdClass();

		$prefix = substr( $option, 0, 4 );
		if ($prefix != 'com_') {
			// ensure backward compatibility with existing links
			$name = $option;
			$option = "com_$option";
		} else {
			$name = substr( $option, 4 );
		}
		// components
		if (file_exists( "$basePath/templates/$this->_template/components/$name.html.php" )) {
			$this->_path->front = "$basePath/components/$option/$name.php";
			$this->_path->front_html = "$basePath/templates/$this->_template/components/$name.html.php";
		} else if (file_exists( "$basePath/components/$option/$name.php" )) {
			$this->_path->front = "$basePath/components/$option/$name.php";
			$this->_path->front_html = "$basePath/components/$option/$name.html.php";
		}
		if (file_exists( "$basePath/administrator/components/$option/admin.$name.php" )) {
			$this->_path->admin = "$basePath/administrator/components/$option/admin.$name.php";
			$this->_path->admin_html = "$basePath/administrator/components/$option/admin.$name.html.php";
		}
		if (file_exists( "$basePath/administrator/components/$option/toolbar.$name.php" )) {
			$this->_path->toolbar = "$basePath/administrator/components/$option/toolbar.$name.php";
			$this->_path->toolbar_html = "$basePath/administrator/components/$option/toolbar.$name.html.php";
			$this->_path->toolbar_default = "$basePath/administrator/includes/toolbar.html.php";
		}
		if (file_exists( "$basePath/components/$option/$name.class.php" )) {
			$this->_path->class = "$basePath/components/$option/$name.class.php";
		} else if (file_exists( "$basePath/administrator/components/$option/$name.class.php" )) {
			$this->_path->class = "$basePath/administrator/components/$option/$name.class.php";
		} else if (file_exists( "$basePath/includes/$name.php" )) {
			$this->_path->class = "$basePath/includes/$name.php";
		}
		if (file_exists("$basePath/administrator/components/$option/admin.$name.php" )) {
			$this->_path->admin = "$basePath/administrator/components/$option/admin.$name.php";
			$this->_path->admin_html = "$basePath/administrator/components/$option/admin.$name.html.php";
		} else {
			$this->_path->admin = "$basePath/administrator/components/com_admin/admin.admin.php";
			$this->_path->admin_html = "$basePath/administrator/components/com_admin/admin.admin.html.php";
		}
	}
	
	/**
	 * Gets the id number for a client
	 * @param mixed A client identifier
	 */
	function getClientID( $client ) {
		switch ($client) {
			case '2':
			case 'installation':
				return 2;
				break;

			case '1':
			case 'admin':
			case 'administrator':
				return 1;
				break;

			case '0':
			case 'site':
			case 'front':
			default:
				return 0;
				break;
		}
	}
	
	/**
	* Returns a stored path variable
	*
	*/
	function getPath( $varname, $option='' ) {
		global $mosConfig_absolute_path;
		if ($option) {
			$temp = $this->_path;
			$this->_setAdminPaths( $option, $this->getCfg( 'absolute_path' ) );
		}
		$result = null;
		if (isset( $this->_path->$varname )) {
			$result = $this->_path->$varname;
		} else {
			switch ($varname) {
				case 'com_xml':
					$name = substr( $option, 4 );
					$path = "$mosConfig_absolute_path/administrator/components/$option/$name.xml";
					if (file_exists( $path )) {
						$result = $path;
					} else {
						$path = "$mosConfig_absolute_path/components/$option/$name.xml";
						if (file_exists( $path )) {
							$result = $path;
						}
					}
					break;

				case 'mod0_xml':
					// Site modules
					if ($option == '') {
						$path = $mosConfig_absolute_path . "/modules/custom.xml";
					} else {
						$path = $mosConfig_absolute_path . "/modules/$option.xml";
					}
					if (file_exists( $path )) {
						$result = $path;
					}
					break;

				case 'mod1_xml':
					// admin modules
					if ($option == '') {
						$path = $mosConfig_absolute_path . '/administrator/modules/custom.xml';
					} else {
						$path = $mosConfig_absolute_path . "/administrator/modules/$option.xml";
					}
					if (file_exists( $path )) {
						$result = $path;
					}
					break;

				case 'bot_xml':
					// Site mambots
					$path = $mosConfig_absolute_path . "/mambots/$option.xml";
					if (file_exists( $path )) {
						$result = $path;
					}
					break;

				case 'menu_xml':
					$path = $mosConfig_absolute_path . "/administrator/components/com_menus/$option/$option.xml";
					if (file_exists( $path )) {
						$result = $path;
					}
					break;

				case 'installer_html':
					$path = $mosConfig_absolute_path . "/administrator/components/com_installer/$option/$option.html.php";
					if (file_exists( $path )) {
						$result = $path;
					}
					break;

				case 'installer_class':
					$path = $mosConfig_absolute_path . "/administrator/components/com_installer/$option/$option.class.php";
					if (file_exists( $path )) {
						$result = $path;
					}
					break;
			}
		}
		if ($option) {
			$this->_path = $temp;
		}
		return $result;
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
				return mosFS::getNativePath( $mosConfig_absolute_path, $addTrailingSlash );
				break;

			case '2':
			case 'installation':
				return mosFS::getNativePath( $mosConfig_absolute_path . '/installation', $addTrailingSlash );
				break;

			case '1':
			case 'admin':
			case 'administrator':
				return mosFS::getNativePath( $mosConfig_absolute_path . '/administrator', $addTrailingSlash );
				break;

		}
	}
	
	/**
	* Detects a 'visit'
	*
	* This function updates the agent and domain table hits for a particular
	* visitor.  The user agent is recorded/incremented if this is the first visit.
	* A cookie is set to mark the first visit.
	*/
	function detect() {
		global $mosConfig_enable_stats;
		if ($mosConfig_enable_stats == 1) {
			if (mosGetParam( $_COOKIE, 'mosvisitor', 0 )) {
				return;
			}
			setcookie( "mosvisitor", "1" );

			if (phpversion() <= "4.2.1") {
				$agent = getenv( "HTTP_USER_AGENT" );
				$domain = gethostbyaddr( getenv( "REMOTE_ADDR" ) );
			} else {
				$agent = $_SERVER['HTTP_USER_AGENT'];
				$domain = gethostbyaddr( $_SERVER['REMOTE_ADDR'] );
			}

			$browser = mosGetBrowser( $agent );

			$query = "SELECT COUNT(*)"
			. "\n FROM #__stats_agents"
			. "\n WHERE agent = '$browser'"
			. "\n AND type = 0"
			;
			$this->_db->setQuery( $query );
			if ($this->_db->loadResult()) {
				$query = "UPDATE #__stats_agents"
				. "\n SET hits = ( hits + 1 )"
				. "\n WHERE agent = '$browser'"
				. "\n AND type = 0"
				;
				$this->_db->setQuery( $query );
			} else {
				$query = "INSERT INTO #__stats_agents"
				. "\n ( agent, type )"
				. "\n VALUES ( '$browser', 0 )"
				;
				$this->_db->setQuery( $query );
			}
			$this->_db->query();

			$os = mosGetOS( $agent );

			$query = "SELECT COUNT(*)"
			. "\n FROM #__stats_agents"
			. "\n WHERE agent = '$os'"
			. "\n AND type = 1"
			;
			$this->_db->setQuery( $query );
			if ($this->_db->loadResult()) {
				$query = "UPDATE #__stats_agents"
				. "\n SET hits = ( hits + 1 )"
				. "\n WHERE agent = '$os'"
				. "\n AND type = 1"
				;
				$this->_db->setQuery( $query );
			} else {
				$query = "INSERT INTO #__stats_agents"
				. "\n ( agent, type )"
				. "\n VALUES ( '$os', 1 )"
				;
				$this->_db->setQuery( $query );
			}
			$this->_db->query();

			// tease out the last element of the domain
			$tldomain = split( "\.", $domain );
			$tldomain = $tldomain[count( $tldomain )-1];

			if (is_numeric( $tldomain )) {
				$tldomain = "Unknown";
			}

			$query = "SELECT COUNT(*)"
			. "\n FROM #__stats_agents"
			. "\n WHERE agent = '$tldomain'"
			. "\n AND type = 2"
			;
			$this->_db->setQuery( $query );
			if ($this->_db->loadResult()) {
				$query = "UPDATE #__stats_agents"
				. "\n SET hits = ( hits + 1 )"
				. "\n WHERE agent = '$tldomain'"
				. "\n AND type = 2"
				;
				$this->_db->setQuery( $query );
			} else {
				$query = "INSERT INTO #__stats_agents"
				. "\n ( agent, type )"
				. "\n VALUES ( '$tldomain', 2 )"
				;
				$this->_db->setQuery( $query );
			}
			$this->_db->query();
		}
	}

	/**
	* @return correct Itemid for Content Item
	*/
	function getItemid( $id, $typed=1, $link=1, $bs=1, $bc=1, $gbs=1 ) {
		global $Itemid;

		$_Itemid = '';
		if ($_Itemid == '' && $typed) {
			// Search for typed link
			$query = "SELECT id"
			. "\n FROM #__menu"
			. "\n WHERE type = 'content_typed'"
			. "\n AND published = 1"
			. "\n AND link = 'index.php?option=com_content&task=view&id=$id'"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}

		if ($_Itemid == '' && $link) {
			// Search for item link
			$query = "SELECT id"
			."\n FROM #__menu"
			."\n WHERE type = 'content_item_link'"
			. "\n AND published = 1"
			. "\n AND link = 'index.php?option=com_content&task=view&id=$id'"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}

		if ($_Itemid == '') {
			// Search in sections
			$query = "SELECT m.id "
			. "\n FROM #__content AS i"
			. "\n LEFT JOIN #__sections AS s ON i.sectionid = s.id"
			. "\n LEFT JOIN #__menu AS m ON m.componentid = s.id "
			. "\n WHERE m.type = 'content_section'"
			. "\n AND m.published = 1"
			. "\n AND i.id = $id"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}

		if ($_Itemid == '' && $bs) {
			// Search in specific blog section
			$query = "SELECT m.id "
			. "\n FROM #__content AS i"
			. "\n LEFT JOIN #__sections AS s ON i.sectionid = s.id"
			. "\n LEFT JOIN #__menu AS m ON m.componentid = s.id "
			. "\n WHERE m.type = 'content_blog_section'"
			. "\n AND m.published = 1"
			. "\n AND i.id = $id"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}

		if ($_Itemid == '' && $bc) {
			// Search in specific blog category
			$query = "SELECT m.id "
			. "\n FROM #__content AS i"
			. "\n LEFT JOIN #__categories AS c ON i.catid = c.id"
			. "\n LEFT JOIN #__menu AS m ON m.componentid = c.id "
			. "\n WHERE m.type = 'content_blog_category'"
			. "\n AND m.published = 1"
			. "\n AND i.id = $id"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}

		if ($_Itemid == '' && $gbs) {
			// Search in global blog section
			$query = "SELECT id "
			. "\n FROM #__menu "
			. "\n WHERE type = 'content_blog_section'"
			. "\n AND published = 1"
			. "\n AND componentid = 0"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}

		if ($_Itemid == '') {
			// Search in categories
			$query = "SELECT m.id "
			. "\n FROM #__content AS i"
			. "\n LEFT JOIN #__categories AS cc ON i.catid = cc.id"
			. "\n LEFT JOIN #__menu AS m ON m.componentid = cc.id "
			. "\n WHERE m.type = 'content_category'"
			. "\n AND m.published = 1"
			. "\n AND i.id = $id"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}
		
		if ( $_Itemid != '' ) {
			return $_Itemid;
		} else {
			return $Itemid;
		}
	}

	/**
	* @return number of Published Blog Sections
	*/
	function getBlogSectionCount( ) {
		$query = "SELECT COUNT( m.id )"
		."\n FROM #__content AS i"
		."\n LEFT JOIN #__sections AS s ON i.sectionid = s.id"
		."\n LEFT JOIN #__menu AS m ON m.componentid = s.id "
		."\n WHERE m.type = 'content_blog_section'"
		."\n AND m.published = 1"
		;
		$this->_db->setQuery( $query );
		$count = $this->_db->loadResult();
		return $count;
	}

	/**
	* @return number of Published Blog Categories
	*/
	function getBlogCategoryCount( ) {
		$query = "SELECT COUNT( m.id )"
		. "\n FROM #__content AS i"
		. "\n LEFT JOIN #__categories AS c ON i.catid = c.id"
		. "\n LEFT JOIN #__menu AS m ON m.componentid = c.id "
		. "\n WHERE m.type = 'content_blog_category'"
		. "\n AND m.published = 1"
		;
		$this->_db->setQuery( $query );
		$count = $this->_db->loadResult();
		return $count;
	}

	/**
	* @return number of Published Global Blog Sections
	*/
	function getGlobalBlogSectionCount( ) {
		$query = "SELECT COUNT( id )"
		."\n FROM #__menu "
		."\n WHERE type = 'content_blog_section'"
		."\n AND published = 1"
		."\n AND componentid = 0"
		;
		$this->_db->setQuery( $query );
		$count = $this->_db->loadResult();
		return $count;
	}

	/**
	* @return number of Static Content
	*/
	function getStaticContentCount( ) {
		$query = "SELECT COUNT( id )"
		."\n FROM #__menu "
		."\n WHERE type = 'content_typed'"
		."\n AND published = 1"
		;
		$this->_db->setQuery( $query );
		$count = $this->_db->loadResult();
		return $count;
	}

	/**
	* @return number of Content Item Links
	*/
	function getContentItemLinkCount( ) {
		$query = "SELECT COUNT( id )"
		."\n FROM #__menu "
		."\n WHERE type = 'content_item_link'"
		."\n AND published = 1"
		;
		$this->_db->setQuery( $query );
		$count = $this->_db->loadResult();
		return $count;
	}

	/**
	* @param string The name of the property
	* @param mixed The value of the property to set
	*/
	function set( $property, $value=null ) {
		$this->$property = $value;
	}

	/**
	* @param string The name of the property
	* @param mixed  The default value
	* @return mixed The value of the property
	*/
	function get($property, $default=null) {
		if(isset($this->$property)) {
			return $this->$property;
		} else {
			return $default;
		}
	}
	
	/** Is admin interface?
	 * @return boolean
	 * @since 1.0.2
	 */
	function isAdmin() {
		return $this->_isAdmin;
	}
}

/**
* Component database table class
* @package Joomla
*/
class mosComponent extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $name				= null;
	/** @var string */
	var $link				= null;
	/** @var int */
	var $menuid				= null;
	/** @var int */
	var $parent				= null;
	/** @var string */
	var $admin_menu_link	= null;
	/** @var string */
	var $admin_menu_alt		= null;
	/** @var string */
	var $option				= null;
	/** @var string */
	var $ordering			= null;
	/** @var string */
	var $admin_menu_img		= null;
	/** @var int */
	var $iscore				= null;
	/** @var string */
	var $params				= null;

	/**
	* @param database A database connector object
	*/
	function mosComponent( &$db ) {
		$this->mosDBTable( '#__components', 'id', $db );
	}
}

/**
* Utility class for all HTML drawing classes
* @package Joomla
*/
class mosHTML {
	function makeOption( $value, $text='', $value_name='value', $text_name='text' ) {
		$obj = new stdClass;
		$obj->$value_name = $value;
		$obj->$text_name = trim( $text ) ? $text : $value;
		return $obj;
	}

  function writableCell( $folder ) {
	global $_LANG;

  	echo '<tr>';
  	echo '<td class="item">' . $folder . '/</td>';
  	echo '<td >';
  	echo is_writable( "../$folder" ) ? '<b><font color="green">'. $_LANG->_( 'Writeable' ) .'</font></b>' : '<b><font color="red">'. $_LANG->_( 'Unwriteable' ) .'</font></b>' . '</td>';
  	echo '</tr>';
  }

	/**
	* Generates an HTML select list
	* @param array An array of objects
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param string The name of the object variable for the option value
	* @param string The name of the object variable for the option text
	* @param mixed The key that is selected
	* @returns string HTML for the select list
	*/
	function selectList( &$arr, $tag_name, $tag_attribs, $key, $text, $selected=NULL, $idtag='' ) {
		reset( $arr );
		
		$id = $tag_name;
		if ( $idtag ) {
			$id = $idtag;
		}

		$html = '<select name="'. $tag_name .'" id="'. $id .'" '. $tag_attribs .'>';
		for ($i=0, $n=count( $arr ); $i < $n; $i++ ) {
			if( is_array( $arr[$i] ) ) {
				$k 		= $arr[$i][$key];
				$t	 	= $arr[$i][$text];
				$id 	= ( isset( $arr[$i]['id'] ) ? $arr[$i]['id'] : null );
			} else {
				$k 		= $arr[$i]->$key;
				$t	 	= $arr[$i]->$text;
				$id 	= ( isset( $arr[$i]->id ) ? $arr[$i]->id : null );
			}

			$extra = '';
			$extra .= $id ? ' id="' . $arr[$i]->id . '"' : '';
			if (is_array( $selected )) {
				foreach ($selected as $obj) {
					$k2 = $obj->$key;
					if ($k == $k2) {
						$extra .= ' selected="selected"';
						break;
					}
				}
			} else {
				$extra .= ( $k == $selected ? ' selected="selected"' : '' );
			}
			$html .= '<option value="'. $k .'" '. $extra .'>' . $t . '</option>';
		}
		$html .= '</select>';

		return $html;
	}

	/**
	* Writes a select list of integers
	* @param int The start integer
	* @param int The end integer
	* @param int The increment
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @param string The printf format to be applied to the number
	* @returns string HTML for the select list
	*/
	function integerSelectList( $start, $end, $inc, $tag_name, $tag_attribs, $selected, $format="" ) {
		$start 	= intval( $start );
		$end 	= intval( $end );
		$inc 	= intval( $inc );
		$arr 	= array();

		for ($i=$start; $i <= $end; $i+=$inc) {
			$fi = $format ? sprintf( "$format", $i ) : "$i";
			$arr[] = mosHTML::makeOption( $fi, $fi );
		}

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', $selected );
	}

	/**
	* Writes a select list of month names based on Language settings
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @returns string HTML for the select list values
	*/
	function monthSelectList( $tag_name, $tag_attribs, $selected ) {
		global $_LANG;

		$arr = array(
			mosHTML::makeOption( '01', $_LANG->_( 'JAN' ) ),
			mosHTML::makeOption( '02', $_LANG->_( 'FEB' ) ),
			mosHTML::makeOption( '03', $_LANG->_( 'MAR' ) ),
			mosHTML::makeOption( '04', $_LANG->_( 'APR' ) ),
			mosHTML::makeOption( '05', $_LANG->_( 'MAY' ) ),
			mosHTML::makeOption( '06', $_LANG->_( 'JUN' ) ),
			mosHTML::makeOption( '07', $_LANG->_( 'JUL' ) ),
			mosHTML::makeOption( '08', $_LANG->_( 'AUG' ) ),
			mosHTML::makeOption( '09', $_LANG->_( 'SEP' ) ),
			mosHTML::makeOption( '10', $_LANG->_( 'OCT' ) ),
			mosHTML::makeOption( '11', $_LANG->_( 'NOV' ) ),
			mosHTML::makeOption( '12', $_LANG->_( 'DEC' ) )
		);

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', $selected );
	}

	/**
	* Generates an HTML select list from a tree based query list
	* @param array Source array with id and parent fields
	* @param array The id of the current list item
	* @param array Target array.  May be an empty array.
	* @param array An array of objects
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param string The name of the object variable for the option value
	* @param string The name of the object variable for the option text
	* @param mixed The key that is selected
	* @returns string HTML for the select list
	*/
	function treeSelectList( &$src_list, $src_id, $tgt_list, $tag_name, $tag_attribs, $key, $text, $selected ) {

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ($src_list as $v ) {
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$ilist = mosTreeRecurse( 0, '', array(), $children );

		// assemble menu items to the array
		$this_treename = '';
		foreach ($ilist as $item) {
			if ($this_treename) {
				if ($item->id != $src_id && strpos( $item->treename, $this_treename ) === false) {
					$tgt_list[] = mosHTML::makeOption( $item->id, $item->treename );
				}
			} else {
				if ($item->id != $src_id) {
					$tgt_list[] = mosHTML::makeOption( $item->id, $item->treename );
				} else {
					$this_treename = "$item->treename/";
				}
			}
		}
		// build the html select list
		return mosHTML::selectList( $tgt_list, $tag_name, $tag_attribs, $key, $text, $selected );
	}

	/**
	* Writes a yes/no select list
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @returns string HTML for the select list values
	*/
	function yesnoSelectList( $tag_name, $tag_attribs, $selected, $yes='yes', $no='no' ) {
		global $_LANG;

		$arr = array(
			mosHTML::makeOption( '0', $_LANG->_( $no ) ),
			mosHTML::makeOption( '1', $_LANG->_( $yes ) ),
		);

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', $selected );
	}

	/**
	* Generates an HTML radio list
	* @param array An array of objects
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @param string The name of the object variable for the option value
	* @param string The name of the object variable for the option text
	* @returns string HTML for the select list
	*/
	function radioList( &$arr, $tag_name, $tag_attribs, $selected=null, $key='value', $text='text' ) {
		reset( $arr );
		$html = "";
		for ($i=0, $n=count( $arr ); $i < $n; $i++ ) {
			$k = $arr[$i]->$key;
			$t = $arr[$i]->$text;
			$id = ( isset($arr[$i]->id) ? @$arr[$i]->id : null);

			$extra = '';
			$extra .= $id ? " id=\"" . $arr[$i]->id . "\"" : '';
			if (is_array( $selected )) {
				foreach ($selected as $obj) {
					$k2 = $obj->$key;
					if ($k == $k2) {
						$extra .= " selected=\"selected\"";
						break;
					}
				}
			} else {
				$extra .= ($k == $selected ? " checked=\"checked\"" : '');
			}
			$html .= "\n\t<input type=\"radio\" name=\"$tag_name\" id=\"$tag_name$k\" value=\"".$k."\"$extra $tag_attribs />";
			$html .= "\n\t<label for=\"$tag_name$k\">$t</label>";
		}
		$html .= "\n";
		return $html;
	}

	/**
	* Writes a yes/no radio list
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @returns string HTML for the radio list
	*/
	function yesnoRadioList( $tag_name, $tag_attribs, $selected, $yes='yes', $no='no' ) {
		global $_LANG;

		$arr = array(
			mosHTML::makeOption( '0', $_LANG->_( $no ) ),
			mosHTML::makeOption( '1', $_LANG->_( $yes ) )
		);
		return mosHTML::radioList( $arr, $tag_name, $tag_attribs, $selected );
	}

	/**
	* @param int The row index
	* @param int The record id
	* @param boolean
	* @param string The name of the form element
	* @return string
	*/
	function idBox( $rowNum, $recId, $checkedOut=false, $name='cid' ) {
		if ( $checkedOut ) {
			return '';
		} else {
			return '<input type="checkbox" id="cb'.$rowNum.'" name="'.$name.'[]" value="'.$recId.'" onclick="isChecked(this.checked);" />';
		}
	}

	function sortIcon( $base_href, $field, $state='none' ) {
		global $mosConfig_live_site;

		$alts = array(
			'none' 	=> _CMN_SORT_NONE,
			'asc' 	=> _CMN_SORT_ASC,
			'desc' 	=> _CMN_SORT_DESC,
		);
		$next_state = 'asc';
		if ($state == 'asc') {
			$next_state = 'desc';
		} else if ($state == 'desc') {
			$next_state = 'none';
		}

		$html = "<a href=\"$base_href&field=$field&order=$next_state\">"
		. "<img src=\"$mosConfig_live_site/images/M_images/sort_$state.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"{$alts[$next_state]}\" />"
		. "</a>";
		return $html;
	}

	/**
	* Writes Close Button
	*/
	function CloseButton ( &$params, $hide_js=NULL ) {
		global $_LANG;

		// displays close button in Pop-up window
		if ( $params->get( 'popup' ) && !$hide_js ) {
			?>
			<div align="center" style="margin-top: 30px; margin-bottom: 30px;">
			<a href='javascript:window.close();'>
			<span class="small">
			<?php echo $_LANG->_( 'PROMPT_CLOSE' );?>
			</span>
			</a>
			</div>
			<?php
		}
	}

	/**
	* Writes Back Button
	*/
	function BackButton ( &$params, $hide_js=NULL ) {
		// Back Button
		if ( $params->get( 'back_button' ) && !$params->get( 'popup' ) && !$hide_js) {
			?>
			<div class="back_button">
			<a href='javascript:history.go(-1)'>
			<?php echo _BACK; ?>
			</a>
			</div>
			<?php
		}
	}

	/**
	* Cleans text of all formating and scripting code
	*/
	function cleanText ( &$text ) {
		$text = preg_replace( "'<script[^>]*>.*?</script>'si", '', $text );
		$text = preg_replace( '/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2 (\1)', $text );
		$text = preg_replace( '/<!--.+?-->/', '', $text );
		$text = preg_replace( '/{.+?}/', '', $text );
		$text = preg_replace( '/&nbsp;/', ' ', $text );
		$text = preg_replace( '/&amp;/', ' ', $text );
		$text = preg_replace( '/&quot;/', ' ', $text );
		$text = strip_tags( $text );
		$text = htmlspecialchars( $text );
		return $text;
	}

	/**
	* Writes Print icon
	*/
	function PrintIcon( &$row, &$params, $hide_js, $link, $status=NULL ) {
		global $mosConfig_live_site, $mosConfig_absolute_path, $cur_template, $Itemid;
		if ( $params->get( 'print' )  && !$hide_js ) {
			// use default settings if none declared
			if ( !$status ) {
				$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
			}

			// checks template image directory for image, if non found default are loaded
			if ( $params->get( 'icons' ) ) {
				$image = mosAdminMenus::ImageCheck( 'printButton.png', '/images/M_images/', NULL, NULL, _CMN_PRINT );
			} else {
				$image = _ICON_SEP .'&nbsp;'. _CMN_PRINT. '&nbsp;'. _ICON_SEP;
			}

			if ( $params->get( 'popup' ) && !$hide_js ) {
				// Print Preview button - used when viewing page
				?>
				<td align="right" width="100%" class="buttonheading">
				<a href="#" onclick="javascript:window.print(); return false" title="<?php echo _CMN_PRINT;?>">
				<?php echo $image;?>
				</a>
				</td>
				<?php
			} else {
				// Print Button - used in pop-up window
				?>
				<td align="right" width="100%" class="buttonheading">
				<a href="javascript:void window.open('<?php echo $link; ?>', 'win2', '<?php echo $status; ?>');" title="<?php echo _CMN_PRINT;?>">
				<?php echo $image;?>
				</a>
				</td>
				<?php
			}
		}
	}

	/**
	* simple Javascript Cloaking
	* email cloacking
 	* by default replaces an email with a mailto link with email cloacked
	*/
	function emailCloaking( $mail, $mailto=1, $text='', $email=1 ) {
		// convert text
		$mail 		= mosHTML::encoding_converter( $mail );
		// split email by @ symbol
		$mail		= explode( '@', $mail );
		$mail_parts	= explode( '.', $mail[1] );
		// random number
		$rand	= rand( 1, 100000 );

		$replacement 	= "\n<script language='JavaScript' type='text/javascript'> \n";
		$replacement 	.= "<!-- \n";
		$replacement 	.= "var prefix = '&#109;a' + 'i&#108;' + '&#116;o'; \n";
		$replacement 	.= "var path = 'hr' + 'ef' + '='; \n";
		$replacement 	.= "var addy". $rand ." = '". @$mail[0] ."' + '&#64;'; \n";
		$replacement 	.= "addy". $rand ." = addy". $rand ." + '". implode( "' + '&#46;' + '", $mail_parts ) ."'; \n";
		if ( $mailto ) {
			// special handling when mail text is different from mail addy
			if ( $text ) {
				if ( $email ) {
					// convert text
					$text 	= mosHTML::encoding_converter( $text );
					// split email by @ symbol
					$text 	= explode( '@', $text );
					$text_parts	= explode( '.', $text[1] );
					$replacement 	.= "var addy_text". $rand ." = '". @$text[0] ."' + '&#64;' + '". implode( "' + '&#46;' + '", @$text_parts ) ."'; \n";
				} else {
					$text 	= mosHTML::encoding_converter( $text );
					$replacement 	.= "var addy_text". $rand ." = '". $text ."';\n";
				}
				$replacement 	.= "document.write( '<a ' + path + '\'' + prefix + ':' + addy". $rand ." + '\'>' ); \n";
				$replacement 	.= "document.write( addy_text". $rand ." ); \n";
				$replacement 	.= "document.write( '<\/a>' ); \n";
			} else {
				$replacement 	.= "document.write( '<a ' + path + '\'' + prefix + ':' + addy". $rand ." + '\'>' ); \n";
				$replacement 	.= "document.write( addy". $rand ." ); \n";
				$replacement 	.= "document.write( '<\/a>' ); \n";
			}
		} else {
			$replacement 	.= "document.write( addy". $rand ." ); \n";
		}
		$replacement 	.= "//--> \n";
		$replacement 	.= "</script>";
		$replacement 	.= "<noscript> \n";
		$replacement 	.= _CLOAKING;
		$replacement 	.= "\n</noscript>";
		
		return $replacement;
	}

	function encoding_converter( $text ) {
		// replace vowels with character encoding
		$text 	= str_replace( 'a', '&#97;', $text );
		$text 	= str_replace( 'e', '&#101;', $text );
		$text 	= str_replace( 'i', '&#105;', $text );
		$text 	= str_replace( 'o', '&#111;', $text );
		$text	= str_replace( 'u', '&#117;', $text );

		return $text;
	}
}

/**
* Category database table class
* @package Joomla
*/
class mosCategory extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $parent_id			= null;
	/** @var string The menu title for the Category (a short name)*/
	var $title				= null;
	/** @var string The full name for the Category*/
	var $name				= null;
	/** @var string */
	var $image				= null;
	/** @var string */
	var $section			= null;
	/** @var int */
	var $image_position		= null;
	/** @var string */
	var $description		= null;
	/** @var boolean */
	var $published			= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var time */
	var $checked_out_time	= null;
	/** @var int */
	var $ordering			= null;
	/** @var int */
	var $access				= null;
	/** @var string */
	var $params				= null;

	/**
	* @param database A database connector object
	*/
	function mosCategory( &$db ) {
		$this->mosDBTable( '#__categories', 'id', $db );
	}
	// overloaded check function
	function check() {
		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = "Your Category must contain a title.";
			return false;
		}
		if (trim( $this->name ) == '') {
			$this->_error = "Your Category must have a name.";
			return false;
		}
		// check for existing name
		$query = "SELECT id"
		. "\n FROM #__categories "
		. "\n WHERE name = '$this->name'"
		. "\n AND section = '$this->section'"
		;
		$this->_db->setQuery( $query );

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = "There is a category already with that name, please try again.";
			return false;
		}
		return true;
	}
}

/**
* Section database table class
* @package Joomla
*/
class mosSection extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string The menu title for the Section (a short name)*/
	var $title				= null;
	/** @var string The full name for the Section*/
	var $name				= null;
	/** @var string */
	var $image				= null;
	/** @var string */
	var $scope				= null;
	/** @var int */
	var $image_position		= null;
	/** @var string */
	var $description		= null;
	/** @var boolean */
	var $published			= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var time */
	var $checked_out_time	= null;
	/** @var int */
	var $ordering			= null;
	/** @var int */
	var $access				= null;
	/** @var string */
	var $params				= null;

	/**
	* @param database A database connector object
	*/
	function mosSection( &$db ) {
		$this->mosDBTable( '#__sections', 'id', $db );
	}
	// overloaded check function
	function check() {
		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = "Your Section must contain a title.";
			return false;
		}
		if (trim( $this->name ) == '') {
			$this->_error = "Your Section must have a name.";
			return false;
		}
		// check for existing name
		$query = "SELECT id"
		. "\n FROM #__sections "
		. "\n WHERE name = '$this->name'"
		. "\n AND scope = '$this->scope'"
		;
		$this->_db->setQuery( $query );

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = "There is a section already with that name, please try again.";
			return false;
		}
		return true;
	}
}

/**
* Module database table class
* @package Joomla
*/
class mosContent extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $title				= null;
	/** @var string */
	var $title_alias		= null;
	/** @var string */
	var $introtext			= null;
	/** @var string */
	var $fulltext			= null;
	/** @var int */
	var $state				= null;
	/** @var int The id of the category section*/
	var $sectionid			= null;
	/** @var int DEPRECATED */
	var $mask				= null;
	/** @var int */
	var $catid				= null;
	/** @var datetime */
	var $created			= null;
	/** @var int User id*/
	var $created_by			= null;
	/** @var string An alias for the author*/
	var $created_by_alias	= null;
	/** @var datetime */
	var $modified			= null;
	/** @var int User id*/
	var $modified_by		= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var time */
	var $checked_out_time	= null;
	/** @var datetime */
	var $frontpage_up		= null;
	/** @var datetime */
	var $frontpage_down		= null;
	/** @var datetime */
	var $publish_up			= null;
	/** @var datetime */
	var $publish_down		= null;
	/** @var string */
	var $images				= null;
	/** @var string */
	var $urls				= null;
	/** @var string */
	var $attribs			= null;
	/** @var int */
	var $version			= null;
	/** @var int */
	var $parentid			= null;
	/** @var int */
	var $ordering			= null;
	/** @var string */
	var $metakey			= null;
	/** @var string */
	var $metadesc			= null;
	/** @var int */
	var $access				= null;
	/** @var int */
	var $hits				= null;

	/**
	* @param database A database connector object
	*/
	function mosContent( &$db ) {
		$this->mosDBTable( '#__content', 'id', $db );
	}

	/**
	 * Validation and filtering
	 */
	function check() {
		// filter malicious code
		$ignoreList = array( 'introtext', 'fulltext' );
		$this->filter( $ignoreList );

		/*
		TODO: This filter is too rigorous,
		need to implement more configurable solution
		// specific filters
		$iFilter = new InputFilter( null, null, 1, 1 );
		$this->introtext = trim( $iFilter->process( $this->introtext ) );
		$this->fulltext =  trim( $iFilter->process( $this->fulltext ) );
		*/

		if (trim( str_replace( '&nbsp;', '', $this->fulltext ) ) == '') {
			$this->fulltext = '';
		}

		return true;
	}

	/**
	* Converts record to XML
	* @param boolean Map foreign keys to text values
	*/
	function toXML( $mapKeysToText=false ) {
		global $database;

		if ($mapKeysToText) {
			$query = "SELECT name"
			. "\n FROM #__sections"
			. "\n WHERE id = $this->sectionid"
			;
			$database->setQuery( $query );
			$this->sectionid = $database->loadResult();

			$query = "SELECT name"
			. "\n FROM #__categories"
			. "\n WHERE id $this->catid"
			;
			$database->setQuery( $query );
			$this->catid = $database->loadResult();

			$query = "SELECT name"
			. "\n FROM #__users"
			. "\n WHERE id = $this->created_by"
			;
			$database->setQuery( $query );
			$this->created_by = $database->loadResult();
		}

		return parent::toXML( $mapKeysToText );
	}
}

/**
* Module database table class
* @package Joomla
*/
class mosMenu extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $menutype			= null;
	/** @var string */
	var $name				= null;
	/** @var string */
	var $link				= null;
	/** @var int */
	var $type				= null;
	/** @var int */
	var $published			= null;
	/** @var int */
	var $componentid		= null;
	/** @var int */
	var $parent				= null;
	/** @var int */
	var $sublevel			= null;
	/** @var int */
	var $ordering			= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var datetime */
	var $checked_out_time	= null;
	/** @var boolean */
	var $pollid				= null;
	/** @var string */
	var $browserNav			= null;
	/** @var int */
	var $access				= null;
	/** @var int */
	var $utaccess			= null;
	/** @var string */
	var $params				= null;

	/**
	* @param database A database connector object
	*/
	function mosMenu( &$db ) {
		$this->mosDBTable( '#__menu', 'id', $db );
	}
}

/**
* Users Table Class
*
* Provides access to the jos_user table
* @package Joomla
*/
class mosUser extends mosDBTable {
	/** @var int Unique id*/
	var $id				= null;
	/** @var string The users real name (or nickname)*/
	var $name			= null;
	/** @var string The login name*/
	var $username		= null;
	/** @var string email*/
	var $email			= null;
	/** @var string MD5 encrypted password*/
	var $password		= null;
	/** @var string */
	var $usertype		= null;
	/** @var int */
	var $block			= null;
	/** @var int */
	var $sendEmail		= null;
	/** @var int The group id number */
	var $gid			= null;
	/** @var datetime */
	var $registerDate	= null;
	/** @var datetime */
	var $lastvisitDate	= null;
	/** @var string activation hash*/
	var $activation		= null;
	/** @var string */
	var $params			= null;

	/**
	* @param database A database connector object
	*/
	function mosUser( &$database ) {
		$this->mosDBTable( '#__users', 'id', $database );
	}

	/**
	 * Validation and filtering
	 * @return boolean True is satisfactory
	 */
	function check() {
		global $mosConfig_uniquemail;

		// filter malicious code
		//$this->filter();

		// Validate user information
		if (trim( $this->name ) == '') {
			$this->_error = _REGWARN_NAME;
			return false;
		}

		if (trim( $this->username ) == '') {
			$this->_error = _REGWARN_UNAME;
			return false;
		}

		if (eregi( "[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", $this->username) || strlen( $this->username ) < 3) {
			$this->_error = sprintf( _VALID_AZ09, _PROMPT_UNAME, 2 );
			return false;
		}

		if ((trim($this->email == "")) || (preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $this->email )==false)) {
			$this->_error = _REGWARN_MAIL;
			return false;
		}

		// check for existing username
		$query = "SELECT id"
		. "\n FROM #__users "
		. "\n WHERE username = '$this->username'"
		. "\n AND id != $this->id"
		;
		$this->_db->setQuery( $query );
		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = _REGWARN_INUSE;
			return false;
		}

		if ($mosConfig_uniquemail) {
			// check for existing email
			$query = "SELECT id"
			. "\n FROM #__users "
			. "\n WHERE email = '$this->email'"
			. "\n AND id != $this->id"
			;
			$this->_db->setQuery( $query );
			$xid = intval( $this->_db->loadResult() );
			if ($xid && $xid != intval( $this->id )) {
				$this->_error = _REGWARN_EMAIL_INUSE;
				return false;
			}
		}

		return true;
	}

	function store( $updateNulls=false ) {
		global $acl, $migrate;
		$section_value = 'users';

		$k = $this->_tbl_key;
		$key =  $this->$k;
		if( $key && !$migrate) {
			// existing record
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
			// syncronise ACL
			// single group handled at the moment
			// trivial to expand to multiple groups
			$groups = $acl->get_object_groups( $section_value, $this->$k, 'ARO' );
			$acl->del_group_object( $groups[0], $section_value, $this->$k, 'ARO' );
			$acl->add_group_object( $this->gid, $section_value, $this->$k, 'ARO' );

			$object_id = $acl->get_object_id( $section_value, $this->$k, 'ARO' );
			$acl->edit_object( $object_id, $section_value, $this->_db->getEscaped( $this->name ), $this->$k, 0, 0, 'ARO' );
		} else {
			// new record
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
			// syncronise ACL
			$acl->add_object( $section_value, $this->_db->getEscaped( $this->name ), $this->$k, null, null, 'ARO' );
			$acl->add_group_object( $this->gid, $section_value, $this->$k, 'ARO' );
		}
		if( !$ret ) {
			$this->_error = strtolower(get_class( $this ))."::store failed <br />" . $this->_db->getErrorMsg();
			return false;
		} else {
			return true;
		}
	}

	function delete( $oid=null ) {
		global $acl;

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		$aro_id = $acl->get_object_id( 'users', $this->$k, 'ARO' );
//		$acl->del_object( $aro_id, 'ARO', true );

		$query = "DELETE FROM $this->_tbl"
		. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
		;
		$this->_db->setQuery( $query );

		if ($this->_db->query()) {
			// cleanup related data

			// :: private messaging
			$query = "DELETE FROM #__messages_cfg"
			. "\n WHERE user_id = ". $this->$k .""
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->_error = $this->_db->getErrorMsg();
				return false;
			}
			$query = "DELETE FROM #__messages"
			. "\n WHERE user_id_to = ". $this->$k .""
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->_error = $this->_db->getErrorMsg();
				return false;
			}

			return true;
		} else {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}
	}
	
	/**
	 * Updates last visit time of user
	 * @param int The timestamp, defaults to 'now'
	 * @return boolean False if an error occurs
	 */
	function setLastVisit( $timeStamp=null, $id=null ) {
		global $_LANG;

		// check for User ID
		if (is_null( $id )) {
			if (isset( $this )) {
				$id = $this->id;
			} else {
				// do not translate
				die( $_LANG->_( 'Error mosUser::setLastVisit cannot call method statically with no id' ) );
			}
		}
		// data check
		$id = intval( $id );

		// if no timestamp value is passed to functon, than current time is used
		if ( $timeStamp ) {
			$dateTime = date( 'Y-m-d H:i:s', $timeStamp );
		} else {
			$dateTime = date( 'Y-m-d H:i:s' );
		}

		// updates user lastvistdate field with date and time
		$query = "UPDATE $this->_tbl"
		. "\n SET lastvisitDate = '$dateTime'"
		. "\n WHERE id = '$id'"
		;
		$this->_db->setQuery( $query );
		if (!$this->_db->query()) {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}

		return true;
	}

	/**
	 * Gets the users from a group
	 * @param string The value for the group (not used 1.0)
	 * @param string The name for the group
	 * @param string If RECURSE, will drill into child groups
	 * @param string Ordering for the list
	 * @return array
	 */
	function getUserListFromGroup( $value, $name, $recurse='NO_RECURSE', $order='name' ) {
		global $acl;

		// Change back in
		//$group_id = $acl->get_group_id( $value, $name, $group_type = 'ARO');
		$group_id = $acl->get_group_id( $name, $group_type = 'ARO');
		$objects = $acl->get_group_objects( $group_id, 'ARO', 'RECURSE');

		if (isset( $objects['users'] )) {
			$gWhere = '(id =' . implode( ' OR id =', $objects['users'] ) . ')';

			$query = "SELECT id AS value, name AS text"
			. "\n FROM #__users"
			. "\n WHERE block = '0'"
			. "\n AND " . $gWhere
			. "\n ORDER BY ". $order
			;
			$this->_db->setQuery( $query );
			$options = $this->_db->loadObjectList();
			return $options;
		} else {
			return array();
		}
	}
}

/**
* Template Table Class
*
* Provides access to the jos_templates table
* @package Joomla
*/
class mosTemplate extends mosDBTable {
	/** @var int */
	var $id				= null;
	/** @var string */
	var $cur_template	= null;
	/** @var int */
	var $col_main		= null;

	/**
	* @param database A database connector object
	*/
	function mosTemplate( &$database ) {
		$this->mosDBTable( '#__templates', 'id', $database );
	}
}

/**
 * Utility function to return a value from a named array or a specified default
 * @param array A named array
 * @param string The key to search for
 * @param mixed The default value to give if no key found
 * @param int An options mask: _MOS_NOTRIM prevents trim, _MOS_ALLOWHTML allows safe html, _MOS_ALLOWRAW allows raw input
 */
define( "_MOS_NOTRIM", 0x0001 );
define( "_MOS_ALLOWHTML", 0x0002 );
define( "_MOS_ALLOWRAW", 0x0004 );
function mosGetParam( &$arr, $name, $def=null, $mask=0 ) {
	static $noHtmlFilter = null;
	static $safeHtmlFilter = null;

	$return = null;
	if (isset( $arr[$name] )) {
		if (is_string( $arr[$name] )) {
			if (!($mask&_MOS_NOTRIM)) {
				$arr[$name] = trim( $arr[$name] );
			}
			if ($mask&_MOS_ALLOWRAW) {
				// do nothing
			} else if ($mask&_MOS_ALLOWHTML) {
				// do nothing - compatibility mode
				/*
				if (is_null( $safeHtmlFilter )) {
					$safeHtmlFilter = new InputFilter( null, null, 1, 1 );
				}
				$arr[$name] = $safeHtmlFilter->process( $arr[$name] );
				*/
			} else {
				if (is_null( $noHtmlFilter )) {
					$noHtmlFilter = new InputFilter( /* $tags, $attr, $tag_method, $attr_method, $xss_auto */ );
				}
				$arr[$name] = $noHtmlFilter->process( $arr[$name] );
			}
			if (!get_magic_quotes_gpc()) {
				$arr[$name] = addslashes( $arr[$name] );
			}
		}
		return $arr[$name];
	} else {
		return $def;
	}
}

/**
 * Strip slashes from strings or arrays of strings
 * @param mixed The input string or array
 * @return mixed String or array stripped of slashes
 */
function mosStripslashes( &$value ) {
	$ret = '';
	if (is_string( $value )) {
		$ret = stripslashes( $value );
	} else {
		if (is_array( $value )) {
			$ret = array();
			foreach ($value as $key => $val) {
				$ret[$key] = mosStripslashes( $val );
			}
		} else {
			$ret = $value;
		}
	}
	return $ret;
}

/**
* Copy the named array content into the object as properties
* only existing properties of object are filled. when undefined in hash, properties wont be deleted
* @param array the input array
* @param obj byref the object to fill of any class
* @param string
* @param boolean
*/
function mosBindArrayToObject( $array, &$obj, $ignore='', $prefix=NULL, $checkSlashes=true ) {
	if (!is_array( $array ) || !is_object( $obj )) {
		return (false);
	}

	foreach (get_object_vars($obj) as $k => $v) {
		if( substr( $k, 0, 1 ) != '_' ) {			// internal attributes of an object are ignored
			if (strpos( $ignore, $k) === false) {
				if ($prefix) {
					$ak = $prefix . $k;
				} else {
					$ak = $k;
				}
				if (isset($array[$ak])) {
					$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? mosStripslashes( $array[$ak] ) : $array[$k];
				}
			}
		}
	}

	return true;
}

/**
* Utility function redirect the browser location to another url
*
* Can optionally provide a message.
* @param string The file system path
* @param string A filter for the names
*/
function mosRedirect( $url, $msg='' ) {
   
   global $mainframe;
   
    // specific filters
	$iFilter = new InputFilter();
	$url = $iFilter->process( $url );
	if (!empty($msg)) {
		$msg = $iFilter->process( $msg );
	}
	
	if ($iFilter->badAttributeValue( array( 'href', $url ))) {
		$url = $GLOBALS['mosConfig_live_site'];
	}
	
	if (trim( $msg )) {
	 	if (strpos( $url, '?' )) {
			$url .= '&mosmsg=' . urlencode( $msg );
		} else {
			$url .= '?mosmsg=' . urlencode( $msg );
		}
	}
	
	if (headers_sent()) {
		echo "<script>document.location.href='$url';</script>\n";
	} else {
		@ob_end_clean(); // clear output buffer
		header( 'HTTP/1.1 301 Moved Permanently' );
		header( "Location: ". $url );
	}
	exit();
}

function mosErrorAlert( $text, $action='window.history.go(-1);', $mode=1 ) {
	$text = nl2br( $text );
	$text = addslashes( $text );
	$text = strip_tags( $text );

	switch ( $mode ) {
		case 2:
			echo "<script>$action</script> \n";
			break;

		case 1:
		default:
			echo "<script>alert('$text'); $action</script> \n";
			echo '<noscript>';
			mosRedirect( @$_SERVER['HTTP_REFERER'], $text );
			echo '</noscript>';
			break;
	}

	exit;
}

function mosTreeRecurse( $id, $indent, $list, &$children, $maxlevel=9999, $level=0, $type=1 ) {
	if (@$children[$id] && $level <= $maxlevel) {
		foreach ($children[$id] as $v) {
			$id = $v->id;

			if ( $type ) {
				$pre 	= '<sup>L</sup>&nbsp;';
				$spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			} else {
				$pre 	= '- ';
				$spacer = '&nbsp;&nbsp;';
			}

			if ( $v->parent == 0 ) {
				$txt 	= $v->name;
			} else {
				$txt 	= $pre . $v->name;
			}
			$pt = $v->parent;
			$list[$id] = $v;
			$list[$id]->treename = "$indent$txt";
			$list[$id]->children = count( @$children[$id] );
			$list = mosTreeRecurse( $id, $indent . $spacer, $list, $children, $maxlevel, $level+1, $type );
		}
	}
	return $list;
}

/**
* Class mosMambot
* @package Joomla
*/
class mosMambot extends mosDBTable {
	/** @var int */
	var $id					= null;
	/** @var varchar */
	var $name				= null;
	/** @var varchar */
	var $element			= null;
	/** @var varchar */
	var $folder				= null;
	/** @var tinyint unsigned */
	var $access				= null;
	/** @var int */
	var $ordering			= null;
	/** @var tinyint */
	var $published			= null;
	/** @var tinyint */
	var $iscore				= null;
	/** @var tinyint */
	var $client_id			= null;
	/** @var int unsigned */
	var $checked_out		= null;
	/** @var datetime */
	var $checked_out_time	= null;
	/** @var text */
	var $params				= null;

	function mosMambot( &$db ) {
		$this->mosDBTable( '#__mambots', 'id', $db );
	}
}

/**
* Module database table class
* @package Joomla
*/
class mosModule extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $title				= null;
	/** @var string */
	var $showtitle			= null;
	/** @var int */
	var $content			= null;
	/** @var int */
	var $ordering			= null;
	/** @var string */
	var $position			= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var time */
	var $checked_out_time	= null;
	/** @var boolean */
	var $published			= null;
	/** @var string */
	var $module				= null;
	/** @var int */
	var $numnews			= null;
	/** @var int */
	var $access				= null;
	/** @var string */
	var $params				= null;
	/** @var string */
	var $iscore				= null;
	/** @var string */
	var $client_id			= null;

	/**
	* @param database A database connector object
	*/
	function mosModule( &$db ) {
		$this->mosDBTable( '#__modules', 'id', $db );
	}
	// overloaded check function
	function check() {
		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = "Your Module must contain a title.";
			return false;
		}

		// limitation has been removed
		// check for existing title
		//$this->_db->setQuery( "SELECT id FROM #__modules"
		//. "\nWHERE title='$this->title'"
		//);
		// check for module of same name
		//$xid = intval( $this->_db->loadResult() );
		//if ($xid && $xid != intval( $this->id )) {
		//	$this->_error = "There is a module already with that name, please try again.";
		//	return false;
		//}
		return true;
	}
}

/**
* Session database table class
* @package Joomla
*/
class mosSession extends mosDBTable {
	/** @var int Primary key */
	var $session_id			= null;
	/** @var string */
	var $time				= null;
	/** @var string */
	var $userid				= null;
	/** @var string */
	var $usertype			= null;
	/** @var string */
	var $username			= null;
	/** @var time */
	var $gid				= null;
	/** @var int */
	var $guest				= null;

	/** @var string */
	var $_session_cookie	= null;
	/** @var string */
	var $_sessionType		= null;

	/**
	 * Constructor
	 * @param database A database connector object
	 */
	function mosSession( &$db, $type='cookie' ) {
		$this->mosDBTable( '#__session', 'session_id', $db );

		$this->guest = 1;
		$this->username = '';
		$this->gid = 0;
		$this->_sessionType = $type;
	}

	function insert() {
		$this->generateId();
		$this->time = time();
		$ret = $this->_db->insertObject( $this->_tbl, $this );

		if( !$ret ) {
			$this->_error = strtolower(get_class( $this ))."::store failed <br />" . $this->_db->stderr();
			return false;
		} else {
			return true;
		}
	}

	function update( $updateNulls=false ) {
		$this->time = time();
		$ret = $this->_db->updateObject( $this->_tbl, $this, 'session_id', $updateNulls );

		if( !$ret ) {
			$this->_error = strtolower(get_class( $this ))."::store failed <br />" . $this->_db->stderr();
			return false;
		} else {
			return true;
		}
	}

	/**
	 * @return string The id of the session
	 */
	function restore() {
		switch ($this->_sessionType) {
			case 'php':
				$id = mosGetParam( $_SESSION, 'session_id', null );
				break;

			case 'cookie':
			default:
				$id = mosGetParam( $_COOKIE, 'sessioncookie', null );
				break;
		}
		return $id;
	}

	/**
	 * Set the information to allow a session to persist
	 */
	function persist() {
		global $mainframe;

		switch ($this->_sessionType) {
			case 'php':
				$_SESSION['session_id'] = $this->getCookie();
				break;

			case 'cookie':
			default:
				setcookie( 'sessioncookie', $this->getCookie(), time() + 43200, '/' );

				$usercookie = mosGetParam( $_COOKIE, 'usercookie', null );
				if ($usercookie) {
					// Remember me cookie exists. Login with usercookie info.
					$mainframe->login( $usercookie['username'], $usercookie['password'] );
				}
				break;
		}
	}

	/**
	 * Allows site to remember login
	 * @param string The username
	 * @param string The user password
	 */
	function remember( $username, $password ) {
		switch ($this->_sessionType) {
			case 'php':
				// not recommended
				break;

			case 'cookie':
			default:
				$lifetime = time() + 365*24*60*60;
				setcookie( 'usercookie[username]', $user->username, $lifetime, '/' );
				setcookie( 'usercookie[password]', $user->password, $lifetime, '/' );
				break;
		}
	}

	/**
	 * Destroys the pesisting session
	 */
	function destroy() {
		if ($this->userid) {
			// update the user last visit
			$query = "UPDATE #__users"
			. "\n SET lastvisitDate = " . $this->_db->Quote( date( 'Y-m-d\TH:i:s' ) )
			. "\n WHERE id='". intval( $this->userid ) ."'";
			$this->_db->setQuery( $query );

			if ( !$this->_db->query() ) {
		 		mosErrorAlert( $database->stderr() );
			}
		}

		switch ($this->_sessionType) {
			case 'php':
				$query = "DELETE FROM #__session"
				. "\n WHERE session_id = ". $this->_db->Quote( $this->session_id )
				;
				$this->_db->setQuery( $query );
				if ( !$this->_db->query() ) {
			 		mosErrorAlert( $this->_db->stderr() );
				}

				session_unset();
				//session_unregister( 'session_id' );
				if ( session_is_registered( 'session_id' ) ) {
					session_destroy();
				}
				break;
			case 'cookie':
			default:
				// revert the session
				$this->guest 	= 1;
				$this->username = '';
				$this->userid 	= '';
				$this->usertype = '';
				$this->gid 		= 0;

				$this->update();

				$lifetime = time() - 1800;
				setcookie( 'usercookie[username]', ' ', $lifetime, '/' );
				setcookie( 'usercookie[password]', ' ', $lifetime, '/' );
				setcookie( 'usercookie', ' ', $lifetime, '/' );
				@session_destroy();
				break;
		}
	}

	/**
	 * Generates a unique id for the session
	 */
	function generateId() {
		$failsafe = 20;
		$randnum = 0;
		while ($failsafe--) {
			$randnum = md5( uniqid( microtime(), 1 ) );
			if ($randnum != '') {
				$cryptrandnum = md5( $randnum );
				$query = "SELECT $this->_tbl_key"
				. "\n FROM $this->_tbl"
				. "\n WHERE $this->_tbl_key = ". $this->_db->Quote( md5( $randnum ) )
				;
				$this->_db->setQuery( $query );
				if(!$result = $this->_db->query()) {
					die( $this->_db->stderr( true ));
					// todo: handle gracefully
				}
				if ($this->_db->getNumRows($result) == 0) {
					break;
				}
			}
		}
		$this->_session_cookie = $randnum;
		$this->session_id = $this->hash( $randnum );
	}

	/**
	 * @return string The cookie|session based session id
	 */
	function getCookie() {
		return $this->_session_cookie;
	}

	/**
	 * Encodes a session id
	 */
	function hash( $value ) {
		if (phpversion() <= '4.2.1') {
			$agent = getenv( 'HTTP_USER_AGENT' );
		} else {
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}

		return md5( $agent . $GLOBALS['mosConfig_secret'] . $value . $_SERVER['REMOTE_ADDR'] );
	}

	/**
	* Purge old sessions
	* @param int Session age in seconds
	* @return mixed Resource on success, null on fail
	*/
	function purge( $age=1800 ) {
		$past = time() - $age;
		$query = "DELETE FROM $this->_tbl"
		. "\n WHERE ( time < $past )"
		;
		$this->_db->setQuery($query);

		return $this->_db->query();
	}
}


function mosObjectToArray($p_obj) {
	$retarray = null;
	if(is_object($p_obj))
	{
		$retarray = array();
		foreach (get_object_vars($p_obj) as $k => $v)
		{
			if(is_object($v))
			$retarray[$k] = mosObjectToArray($v);
			else
			$retarray[$k] = $v;
		}
	}
	return $retarray;
}
/**
* Checks the user agent string against known browsers
*/
function mosGetBrowser( $agent ) {
	require( 'includes/agent_browser.php' );

	if (preg_match( "/msie[\/\sa-z]*([\d\.]*)/i", $agent, $m )
	&& !preg_match( "/webtv/i", $agent )
	&& !preg_match( "/omniweb/i", $agent )
	&& !preg_match( "/opera/i", $agent )) {
		// IE
		return "MS Internet Explorer $m[1]";
	} else if (preg_match( "/netscape.?\/([\d\.]*)/i", $agent, $m )) {
		// Netscape 6.x, 7.x ...
		return "Netscape $m[1]";
	} else if ( preg_match( "/mozilla[\/\sa-z]*([\d\.]*)/i", $agent, $m )
	&& !preg_match( "/gecko/i", $agent )
	&& !preg_match( "/compatible/i", $agent )
	&& !preg_match( "/opera/i", $agent )
	&& !preg_match( "/galeon/i", $agent )
	&& !preg_match( "/safari/i", $agent )) {
		// Netscape 3.x, 4.x ...
		return "Netscape $m[2]";
	} else {
		// Other
		$found = false;
		foreach ($browserSearchOrder as $key) {
			if (preg_match( "/$key.?\/([\d\.]*)/i", $agent, $m )) {
				$name = "$browsersAlias[$key] $m[1]";
				return $name;
				break;
			}
		}
	}

	return 'Unknown';
}

/**
* Checks the user agent string against known operating systems
*/
function mosGetOS( $agent ) {
	require( "includes/agent_os.php" );

	foreach ($osSearchOrder as $key) {
		if (preg_match( "/$key/i", $agent )) {
			return $osAlias[$key];
			break;
		}
	}

	return 'Unknown';
}

/**
* @param string SQL with ordering As value and 'name field' AS text
* @param integer The length of the truncated headline
*/
function mosGetOrderingList( $sql, $chop='30' ) {
	global $database;
	global $_LANG;

	$order = array();
	$database->setQuery( $sql );
	if (!($orders = $database->loadObjectList())) {
		if ($database->getErrorNum()) {
			echo $database->stderr();
			return false;
		} else {
			$order[] = mosHTML::makeOption( 1, $_LANG->_( 'first' ) );
			return $order;
		}
	}
	$order[] = mosHTML::makeOption( 0, '0 '. $_LANG->_( 'first' ) );
	for ($i=0, $n=count( $orders ); $i < $n; $i++) {

		if (strlen($orders[$i]->text) > $chop) {
			$text = substr($orders[$i]->text,0,$chop)."...";
		} else {
			$text = $orders[$i]->text;
		}

		$order[] = mosHTML::makeOption( $orders[$i]->value, $orders[$i]->value.' ('.$text.')' );
	}
	$order[] = mosHTML::makeOption( $orders[$i-1]->value+1, ($orders[$i-1]->value+1).' '. $_LANG->_( 'last' ) );

	return $order;
}

/**
* Makes a variable safe to display in forms
*
* Object parameters that are non-string, array, object or start with underscore
* will be converted
* @param object An object to be parsed
* @param int The optional quote style for the htmlspecialchars function
* @param string|array An optional single field name or array of field names not
*					 to be parsed (eg, for a textarea)
*/
function mosMakeHtmlSafe( &$mixed, $quote_style=ENT_QUOTES, $exclude_keys='' ) {
	if (is_object( $mixed )) {
		foreach (get_object_vars( $mixed ) as $k => $v) {
			if (is_array( $v ) || is_object( $v ) || $v == NULL || substr( $k, 1, 1 ) == '_' ) {
				continue;
			}
			if (is_string( $exclude_keys ) && $k == $exclude_keys) {
				continue;
			} else if (is_array( $exclude_keys ) && in_array( $k, $exclude_keys )) {
				continue;
			}
			$mixed->$k = htmlspecialchars( $v, $quote_style );
		}
	}
}

/**
* Checks whether a menu option is within the users access level
* @param int Item id number
* @param string The menu option
* @param int The users group ID number
* @param database A database connector object
* @return boolean True if the visitor's group at least equal to the menu access
*/
function mosMenuCheck( $Itemid, $menu_option, $task, $gid ) {
	global $database;
	$dblink="index.php?option=$menu_option";
	if ($Itemid!="" && $Itemid!=0 && $Itemid!=99999999) {
		$query = "SELECT access"
		. "\n FROM #__menu"
		. "\n WHERE id = $Itemid"
		;
		$database->setQuery( $query );
	} else {
		if ($task!="") {
			$dblink.="&task=$task";
		}
		$query = "SELECT access"
		. "\n FROM #__menu"
		. "\n WHERE link LIKE '$dblink%'"
		;
		$database->setQuery( $query );
	}
	$results = $database->loadObjectList();
	$access = 0;
	//echo "<pre>"; print_r($results); echo "</pre>";
	foreach ($results as $result) {
		$access = max( $access, $result->access );
	}
	return ($access <= $gid);
}

/**
* Returns formated date according to current local and adds time offset
* @param string date in datetime format
* @param string format optional format for strftime
* @param offset time offset if different than global one
* @returns formated date
*/
function mosFormatDate( $date, $format="", $offset="" ){
	global $mosConfig_offset;

	if ( $format == '' ) {
		// %Y-%m-%d %H:%M:%S
		//after conversion $format = $GLOBALS['_LANG']->_( 'DATE_FORMAT_LC' );
		$format = _DATE_FORMAT_LC;
	}
	if ( $offset == '' ) {
		$offset = $mosConfig_offset;
	}
	if ( $date && ereg( "([0-9]{4})-([0-9]{2})-([0-9]{2})[ ]([0-9]{2}):([0-9]{2}):([0-9]{2})", $date, $regs ) ) {
		$date = mktime( $regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1] );
		$date = $date > -1 ? strftime( $format, $date + ($offset*60*60) ) : '-';
	}
	return $date;
}

/**
* Returns current date according to current local and time offset
* @param string format optional format for strftime
* @returns current date
*/
function mosCurrentDate( $format="" ) {
	global $mosConfig_offset;
	if ($format=="") {
		$format = _DATE_FORMAT_LC;
	}
	$date = strftime( $format, time() + ($mosConfig_offset*60*60) );
	return $date;
}

/**
* Utility function to provide ToolTips
* @param string ToolTip text
* @param string Box title
* @returns HTML code for ToolTip
*/
function mosToolTip( $tooltip, $title='', $width='', $image='tooltip.png', $text='', $href='#', $link=1 ) {
	global $mosConfig_live_site;
	global $_LANG;

	if ( $width ) {
		$width = ', WIDTH, \''.$width .'\'';
	}
	if ( $title ) {
		$title = ', CAPTION, \''. $_LANG->_( $title ) .'\'';
	}
	if ( !$text ) {
		$image 	= $mosConfig_live_site . '/includes/js/ThemeOffice/'. $image;
		$text 	= '<img src="'. $image .'" border="0" />';
	}
	else{
		$text 	= $_LANG->_( $text );
    }
	$style = 'style="text-decoration: none; color: #333;"';
	if ( $href ) {
		$style = '';
	}
	else{ $href = "#"; }

	if ( $link ) {
		$tip = "<a href=\"". $href ."\" onMouseOver=\"return overlib('" . $_LANG->_( $tooltip ) . "'". $title .", BELOW, RIGHT". $width .");\" onmouseout=\"return nd();\" ". $style .">". $text ."</a>";
	} else {
		$tip = "<span onMouseOver=\"return overlib('" . $_LANG->_( $tooltip ) . "'". $title .", BELOW, RIGHT". $width .");\" onmouseout=\"return nd();\" ". $style .">". $text ."</span>";
	}

	return $tip;
}

/**
* Utility function to provide Warning Icons
* @param string Warning text
* @param string Box title
* @returns HTML code for Warning
*/
function mosWarning($warning, $title='') {
	global $mosConfig_live_site, $_LANG;

	$title = $_LANG->_( 'Joomla Warning' );
	$tip = "<a href=\"#\" onMouseOver=\"return overlib('" . $warning . "', CAPTION, '$title', BELOW, RIGHT);\" onmouseout=\"return nd();\"><img src=\"" . $mosConfig_live_site . "/includes/js/ThemeOffice/warning.png\" border=\"0\" /></a>";
	return $tip;
}

function mosCreateGUID(){
	srand((double)microtime()*1000000);
	$r = rand ;
	$u = uniqid(getmypid() . $r . (double)microtime()*1000000,1);
	$m = md5 ($u);
	return($m);
}

function mosCompressID( $ID ){
	return(Base64_encode(pack("H*",$ID)));
}

function mosExpandID( $ID ) {
	return ( implode(unpack("H*",Base64_decode($ID)), '') );
}

/**
* Function to create a mail object for futher use (uses phpMailer)
* @param string From e-mail address
* @param string From name
* @param string E-mail subject
* @param string Message body
* @return object Mail object
*/
function mosCreateMail( $from='', $fromname='', $subject, $body ) {
	global $mosConfig_absolute_path, $mosConfig_sendmail;
	global $mosConfig_smtpauth, $mosConfig_smtpuser;
	global $mosConfig_smtppass, $mosConfig_smtphost;
	global $mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_mailer;
	global $_LANG;

	$mail = new mosPHPMailer();

	$mail->PluginDir = $mosConfig_absolute_path .'/includes/phpmailer/';
	$mail->SetLanguage( 'en', $mosConfig_absolute_path . '/includes/phpmailer/language/' );
	$mail->CharSet 	= $_LANG->iso();
	$mail->IsMail();
	$mail->From 	= $from ? $from : $mosConfig_mailfrom;
	$mail->FromName = $fromname ? $fromname : $mosConfig_fromname;
	$mail->Mailer 	= $mosConfig_mailer;

	// Add smtp values if needed
	if ( $mosConfig_mailer == 'smtp' ) {
		$mail->SMTPAuth = $mosConfig_smtpauth;
		$mail->Username = $mosConfig_smtpuser;
		$mail->Password = $mosConfig_smtppass;
		$mail->Host 	= $mosConfig_smtphost;
	} else

	// Set sendmail path
	if ( $mosConfig_mailer == 'sendmail' ) {
		if (isset($mosConfig_sendmail))
			$mail->Sendmail = $mosConfig_sendmail;
	} // if

	$mail->Subject 	= $subject;
	$mail->Body 	= $body;

	return $mail;
}

/**
* Mail function (uses phpMailer)
* @param string From e-mail address
* @param string From name
* @param string/array Recipient e-mail address(es)
* @param string E-mail subject
* @param string Message body
* @param boolean false = plain text, true = HTML
* @param string/array CC e-mail address(es)
* @param string/array BCC e-mail address(es)
* @param string/array Attachment file name(s)
*/
function mosMail($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=NULL, $bcc=NULL, $attachment=NULL ) {
	global $mosConfig_debug;
	$mail = mosCreateMail( $from, $fromname, $subject, $body );

	// activate HTML formatted emails
	if ( $mode ) {
		$mail->IsHTML(true);
	}

	if( is_array($recipient) ) {
		foreach ($recipient as $to) {
			$mail->AddAddress($to);
		}
	} else {
		$mail->AddAddress($recipient);
	}
	if (isset($cc)) {
		if( is_array($cc) )
			foreach ($cc as $to) $mail->AddCC($to);
		else
			$mail->AddCC($cc);
	}
	if (isset($bcc)) {
		if( is_array($bcc) )
			foreach ($bcc as $to) $mail->AddBCC($to);
		else
			$mail->AddBCC($bcc);
	}
	if ($attachment) {
		if ( is_array($attachment) )
			foreach ($attachment as $fname) $mail->AddAttachment($fname);
		else
			$mail->AddAttachment($attachment);
	} // if
	$mailssend = $mail->Send();

	if( $mosConfig_debug ) {
		//$mosDebug->message( "Mails send: $mailssend");
	}
	if( $mail->error_count > 0 ) {
		//$mosDebug->message( "The mail message $fromname <$from> about $subject to $recipient <b>failed</b><br /><pre>$body</pre>", false );
		//$mosDebug->message( "Mailer Error: " . $mail->ErrorInfo . "" );
	}
	return $mailssend;
} // mosMail

/**
* Initialise GZIP
*/
function initGzip() {
	global $mosConfig_gzip, $do_gzip_compress;
	$do_gzip_compress = FALSE;
	if ($mosConfig_gzip == 1) {
		$phpver = phpversion();
		$useragent = mosGetParam( $_SERVER, 'HTTP_USER_AGENT', '' );
		$canZip = mosGetParam( $_SERVER, 'HTTP_ACCEPT_ENCODING', '' );

		if ( $phpver >= '4.0.4pl1' &&
				( strpos($useragent,'compatible') !== false ||
				  strpos($useragent,'Gecko')	  !== false
				)
			) {
			if ( extension_loaded('zlib') ) {
				// You cannot specify additional output handlers if 
				// zlib.output_compression is activated here
				ob_start( );
				return;
			}
		} else if ( $phpver > '4.0' ) {
			if ( strpos($canZip,'gzip') !== false ) {
				if (extension_loaded( 'zlib' )) {
					$do_gzip_compress = TRUE;
					ob_start();
					ob_implicit_flush(0);

					header( 'Content-Encoding: gzip' );
					return;
				}
			}
		}
	}
	ob_start();
}

/**
* Perform GZIP
*/
function doGzip() {
	global $do_gzip_compress;
	if ( $do_gzip_compress ) {
		/**
		*Borrowed from php.net!
		*/
		$gzip_contents = ob_get_contents();
		ob_end_clean();

		$gzip_size = strlen($gzip_contents);
		$gzip_crc = crc32($gzip_contents);

		$gzip_contents = gzcompress($gzip_contents, 9);
		$gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);

		echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
		echo $gzip_contents;
		echo pack('V', $gzip_crc);
		echo pack('V', $gzip_size);
	} else {
		ob_end_flush();
	}
}

/**
* Random password generator
* @return password
*/
function mosMakePassword() {
	$salt 		= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$len 		= strlen($salt);
	$makepass	= '';
	mt_srand(10000000*(double)microtime());
	for ($i = 0; $i < 8; $i++)
	$makepass .= $salt[mt_rand(0,$len - 1)];
	return $makepass;
}

if (!function_exists('html_entity_decode')) {
	/**
	* html_entity_decode function for backward compatability in PHP
	* @param string
	* @param string
	*/
	function html_entity_decode ($string, $opt = ENT_COMPAT) {

		$trans_tbl = get_html_translation_table (HTML_ENTITIES);
		$trans_tbl = array_flip ($trans_tbl);

		if ($opt & 1) { // Translating single quotes
			// Add single quote to translation table;
			// doesn't appear to be there by default
			$trans_tbl["&apos;"] = "'";
		}

		if (!($opt & 2)) { // Not translating double quotes
			// Remove double quote from translation table
			unset($trans_tbl["&quot;"]);
		}

		return strtr ($string, $trans_tbl);
	}
}

/**
* Plugin handler
* @package Joomla
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

/**
* Tab Creation handler
* @package Joomla
* @author Phil Taylor
*/
class mosTabs {
	/** @var int Use cookies */
	var $useCookies = 0;

	/**
	* Constructor
	* Includes files needed for displaying tabs and sets cookie options
	* @param int useCookies, if set to 1 cookie will hold last used tab between page refreshes
	*/
	function mosTabs($useCookies) {
		global $mosConfig_live_site, $_LANG;
		if ($_LANG->rtl()) {
			echo "<link id=\"luna-tab-style-sheet\" type=\"text/css\" rel=\"stylesheet\" href=\"" . $mosConfig_live_site. "/includes/js/tabs/tabpane_rtl.css\" />";

		} else {
			echo "<link id=\"luna-tab-style-sheet\" type=\"text/css\" rel=\"stylesheet\" href=\"" . $mosConfig_live_site. "/includes/js/tabs/tabpane.css\" />";
		}
		echo "<script type=\"text/javascript\" src=\"". $mosConfig_live_site . "/includes/js/tabs/tabpane_mini.js\"></script>";
		$this->useCookies = $useCookies;
	}

	/**
	* creates a tab pane and creates JS obj
	* @param string The Tab Pane Name
	*/
	function startPane($id){
		echo "<div class=\"tab-page\" id=\"".$id."\">";
		echo "<script type=\"text/javascript\">\n";
		echo "	var tabPane1 = new WebFXTabPane( document.getElementById( \"".$id."\" ), ".$this->useCookies." )\n";
		echo "</script>\n";
	}

	/**
	* Ends Tab Pane
	*/
	function endPane() {
		echo "</div>";
	}

	/*
	* Creates a tab with title text and starts that tabs page
	* @param tabText - This is what is displayed on the tab
	* @param paneid - This is the parent pane to build this tab on
	*/
	function startTab( $tabText, $paneid ) {
		echo "<div class=\"tab-page\" id=\"".$paneid."\">";
		echo "<h2 class=\"tab\">".$tabText."</h2>";
		echo "<script type=\"text/javascript\">\n";
		echo "  tabPane1.addTabPage( document.getElementById( \"".$paneid."\" ) );";
		echo "</script>";
	}

	/*
	* Ends a tab page
	*/
	function endTab() {
		echo "</div>";
	}
}

/**
* Common HTML Output Files
* @package Joomla
*/
class mosAdminMenus {
	/**
	* build the select list for Menu Ordering
	*/
	function Ordering( &$row, $id ) {
		global $database;

		if ( $id ) {
			$query = "SELECT ordering AS value, name AS text"
			. "\n FROM #__menu"
			. "\n WHERE menutype = '$row->menutype'"
			. "\n AND parent = $row->parent"
			. "\n AND published != -2"
			. "\n ORDER BY ordering"
			;
			$order = mosGetOrderingList( $query );
			$ordering = mosHTML::selectList( $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ) );
		} else {
			$ordering = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. _CMN_NEW_ITEM_LAST;
		}
		return $ordering;
	}

	/**
	* build the select list for access level
	*/
	function Access( &$row ) {
		global $database;

		$query = "SELECT id AS value, name AS text"
		. "\n FROM #__groups"
		. "\n ORDER BY id"
		;
		$database->setQuery( $query );
		$groups = $database->loadObjectList();
		$access = mosHTML::selectList( $groups, 'access', 'class="inputbox" size="3"', 'value', 'text', intval( $row->access ) );

		return $access;
	}

	/**
	* build the select list for parent item
	*/
	function Parent( &$row ) {
		global $database;
		global $_LANG;
		
		// get a list of the menu items
		$query = "SELECT m.*"
		. "\n FROM #__menu m"
		. "\n WHERE menutype = '$row->menutype'"
		. "\n AND published <> -2"
		. "\n ORDER BY ordering"
		;
		$database->setQuery( $query );
		$mitems = $database->loadObjectList();

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ( $mitems as $v ) {
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$list = mosTreeRecurse( 0, '', array(), $children, 9999, 0, 0 );

		// assemble menu items to the array
		$mitems = array();
		$mitems[] = mosHTML::makeOption( '0', $_LANG->_( 'Top' ) );
		$this_treename = '';
		foreach ( $list as $item ) {
			if ( $this_treename ) {
				if ( $item->id != $row->id && strpos( $item->treename, $this_treename ) === false) {
					$mitems[] = mosHTML::makeOption( $item->id, $item->treename );
				}
			} else {
				if ( $item->id != $row->id ) {
					$mitems[] = mosHTML::makeOption( $item->id, $item->treename );
				} else {
					$this_treename = "$item->treename/";
				}
			}
		}
		$parent = mosHTML::selectList( $mitems, 'parent', 'class="inputbox" size="1"', 'value', 'text', $row->parent );
		return $parent;
	}

	/**
	* build a radio button option for published state
	*/
	function Published( &$row ) {
		$published = mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );
		return $published;
	}

	/**
	* build the link/url of a menu item
	*/
	function Link( &$row, $id, $link=NULL ) {
		if ( $id ) {
			if ( $link ) {
				$link = $row->link;
			} else {
				$link = $row->link .'&amp;Itemid='. $row->id;
			}
		} else {
			$link = NULL;
		}
		return $link;
	}

	/**
	* build the select list for target window
	*/
	function Target( &$row ) {
		global $_LANG;

		$click[] = mosHTML::makeOption( '0', $_LANG->_( 'Parent Window With Browser Navigation' ) );
		$click[] = mosHTML::makeOption( '1', $_LANG->_( 'New Window With Browser Navigation' ) );
		$click[] = mosHTML::makeOption( '2', $_LANG->_( 'New Window Without Browser Navigation' ) );
		$target = mosHTML::selectList( $click, 'browserNav', 'class="inputbox" size="4"', 'value', 'text', intval( $row->browserNav ) );
		return $target;
	}

	/**
	* build the multiple select list for Menu Links/Pages
	*/
	function MenuLinks( &$lookup, $all=NULL, $none=NULL, $unassigned=1 ) {
		global $database;
		global $_LANG;

		// get a list of the menu items
		$query = "SELECT m.*"
		. "\n FROM #__menu m"
		. "\n WHERE type != 'url'"
		. "\n AND type != 'separator'"
		. "\n AND published = 1"
		. "\n ORDER BY menutype, parent, ordering"
		;
		$database->setQuery( $query );
		$mitems = $database->loadObjectList();
		$mitems_temp = $mitems;

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ( $mitems as $v ) {
			$id = $v->id;
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$list = mosTreeRecurse( intval( $mitems[0]->parent ), '', array(), $children, 9999, 0, 0 );

		// Code that adds menu name to Display of Page(s)
		$text_count = "0";
		$mitems_spacer = $mitems_temp[0]->menutype;
		foreach ($list as $list_a) {
			foreach ($mitems_temp as $mitems_a) {
				if ($mitems_a->id == $list_a->id) {
					// Code that inserts the blank line that seperates different menus
					if ($mitems_a->menutype <> $mitems_spacer) {
						$list_temp[] = mosHTML::makeOption( -999, '----' );
						$mitems_spacer = $mitems_a->menutype;
					}
					$text = $mitems_a->menutype." | ".$list_a->treename;
					$list_temp[] = mosHTML::makeOption( $list_a->id, $text );
					if ( strlen($text) > $text_count) {
						$text_count = strlen($text);
					}
				}
			}
		}
		$list = $list_temp;

		$mitems = array();
		if ( $all ) {
			// prepare an array with 'all' as the first item
			$mitems[] = mosHTML::makeOption( 0, $_LANG->_( 'All' ) );
			// adds space, in select box which is not saved
			$mitems[] = mosHTML::makeOption( -999, '----' );
		}
		if ( $none ) {
			// prepare an array with 'all' as the first item
			$mitems[] = mosHTML::makeOption( -999, $_LANG->_( 'None' ) );
			// adds space, in select box which is not saved
			$mitems[] = mosHTML::makeOption( -999, '----' );
		}
		if ( $none ) {
			// prepare an array with 'all' as the first item
			$mitems[] = mosHTML::makeOption( 99999999, $_LANG->_( 'Unassigned' ) );
			// adds space, in select box which is not saved
			$mitems[] = mosHTML::makeOption( -999, '----' );
		}
		// append the rest of the menu items to the array
		foreach ($list as $item) {
			$mitems[] = mosHTML::makeOption( $item->value, $item->text );
		}
		$pages = mosHTML::selectList( $mitems, 'selections[]', 'class="inputbox" size="26" multiple="multiple"', 'value', 'text', $lookup );
		return $pages;
	}


	/**
	* build the select list to choose a category
	*/
	function Category( &$menu, $id, $javascript='' ) {
		global $database;

		$query = "SELECT c.id AS `value`, c.section AS `id`, CONCAT_WS( ' / ', s.title, c.title) AS `text`"
		. "\n FROM #__sections AS s"
		. "\n INNER JOIN #__categories AS c ON c.section = s.id"
		. "\n WHERE s.scope = 'content'"
		. "\n ORDER BY s.name, c.name"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();
		$category = '';
		if ( $id ) {
			foreach ( $rows as $row ) {
				if ( $row->value == $menu->componentid ) {
					$category = $row->text;
				}
			}
			$category .= '<input type="hidden" name="componentid" value="'. $menu->componentid .'" />';
			$category .= '<input type="hidden" name="link" value="'. $menu->link .'" />';
		} else {
			$category = mosHTML::selectList( $rows, 'componentid', 'class="inputbox" size="10"'. $javascript, 'value', 'text' );
			$category .= '<input type="hidden" name="link" value="" />';
		}
		return $category;
	}

	/**
	* build the select list to choose a section
	*/
	function Section( &$menu, $id, $all=0 ) {
		global $database;
		global $_LANG;

		$query = "SELECT s.id AS `value`, s.id AS `id`, s.title AS `text`"
		. "\n FROM #__sections AS s"
		. "\n WHERE s.scope = 'content'"
		. "\n ORDER BY s.name"
		;
		$database->setQuery( $query );
		if ( $all ) {
			$rows[] = mosHTML::makeOption( 0, '- '. $_LANG->_( 'All Sections' ) .' -' );
			$rows = array_merge( $rows, $database->loadObjectList() );
		} else {
			$rows = $database->loadObjectList();
		}

		if ( $id ) {
			foreach ( $rows as $row ) {
				if ( $row->value == $menu->componentid ) {
					$section = $row->text;
				}
			}
			$section .= '<input type="hidden" name="componentid" value="'. $menu->componentid .'" />';
			$section .= '<input type="hidden" name="link" value="'. $menu->link .'" />';
		} else {
			$section = mosHTML::selectList( $rows, 'componentid', 'class="inputbox" size="10"', 'value', 'text' );
			$section .= '<input type="hidden" name="link" value="" />';
		}
		return $section;
	}

	/**
	* build the select list to choose a component
	*/
	function Component( &$menu, $id ) {
		global $database;

		$query = "SELECT c.id AS value, c.name AS text, c.link"
		. "\n FROM #__components AS c"
		. "\n WHERE c.link <> ''"
		. "\n ORDER BY c.name"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList( );

		if ( $id ) {
			// existing component, just show name
			foreach ( $rows as $row ) {
				if ( $row->value == $menu->componentid ) {
					$component = $row->text;
				}
			}
			$component .= '<input type="hidden" name="componentid" value="'. $menu->componentid .'" />';
		} else {
			$component = mosHTML::selectList( $rows, 'componentid', 'class="inputbox" size="10"', 'value', 'text' );
		}
		return $component;
	}

	/**
	* build the select list to choose a component
	*/
	function ComponentName( &$menu, $id ) {
		global $database;

		$query = "SELECT c.id AS value, c.name AS text, c.link"
		. "\n FROM #__components AS c"
		. "\n WHERE c.link <> ''"
		. "\n ORDER BY c.name"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList( );

		$component = 'Component';
		foreach ( $rows as $row ) {
			if ( $row->value == $menu->componentid ) {
				$component = $row->text;
			}
		}

		return $component;
	}

	/**
	* build the select list to choose an image
	*/
	function Images( $name, &$active, $javascript=NULL, $directory=NULL ) {
		global $mosConfig_absolute_path;
		global $_LANG;

		if ( !$javascript ) {
			$javascript = "onchange=\"javascript:if (document.forms[0].image.options[selectedIndex].value!='') {document.imagelib.src='../images/stories/' + document.forms[0].image.options[selectedIndex].value} else {document.imagelib.src='../images/blank.png'}\"";
		}
		if ( !$directory ) {
			$directory = '/images/stories';
		}

		$imageFiles = mosReadDirectory( $mosConfig_absolute_path . $directory );
		$images = array(  mosHTML::makeOption( '', '- '. $_LANG->_( 'Select Image' ) .' -' ) );
		foreach ( $imageFiles as $file ) {
			if ( eregi( "bmp|gif|jpg|png", $file ) ) {
				$images[] = mosHTML::makeOption( $file );
			}
		}
		$images = mosHTML::selectList( $images, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

		return $images;
	}

	/**
	* build the select list for Ordering of a specified Table
	*/
	function SpecificOrdering( &$row, $id, $query, $neworder=0 ) {
		global $database;
		global $_LANG;

		if ( $neworder ) {
			$text = $_LANG->_( 'descNewItemsFirst' );
		} else {
			$text = $_LANG->_( 'descNewItemsLast' );
		}

		if ( $id ) {
			$order = mosGetOrderingList( $query );
			$ordering = mosHTML::selectList( $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ) );
		} else {
			$ordering = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. $text;
		}
		return $ordering;
	}

	/**
	* Select list of active users
	*/
	function UserSelect( $name, $active, $nouser=0, $javascript=NULL, $order='name', $reg=1 ) {
		global $database, $my;
		global $_LANG;

		$and = '';
		if ( $reg ) {
		// does not include registered users in the list
			$and = "\n AND gid > 18";
		}

		$query = "SELECT id AS value, name AS text"
		. "\n FROM #__users"
		. "\n WHERE block = 0"
		. $and
		. "\n ORDER BY $order"
		;
		$database->setQuery( $query );
		if ( $nouser ) {
			$users[] = mosHTML::makeOption( '0', '- '. $_LANG->_( 'No User' ) .' -' );
			$users = array_merge( $users, $database->loadObjectList() );
		} else {
			$users = $database->loadObjectList();
		}

		$users = mosHTML::selectList( $users, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

		return $users;
	}

	/**
	* Select list of positions - generally used for location of images
	*/
	function Positions( $name, $active=NULL, $javascript=NULL, $none=1, $center=1, $left=1, $right=1 ) {
		global $_LANG;

		if ( $none ) {
			$pos[] = mosHTML::makeOption( '', $_LANG->_( 'None' ) );
		}
		if ( $center ) {
			$pos[] = mosHTML::makeOption( 'center', $_LANG->_( 'Center' ) );
		}
		if ( $left ) {
			$pos[] = mosHTML::makeOption( 'left', $_LANG->_( 'Left' ) );
		}
		if ( $right ) {
			$pos[] = mosHTML::makeOption( 'right', $_LANG->_( 'Right' ) );
		}

		$positions = mosHTML::selectList( $pos, $name, 'class="inputbox" size="1"'. $javascript, 'value', 'text', $active );

		return $positions;
	}

	/**
	* Select list of active categories for components
	*/
	function ComponentCategory( $name, $section, $active=NULL, $javascript=NULL, $order='ordering', $size=1, $sel_cat=1 ) {
		global $database;
		global $_LANG;

		$query = "SELECT id AS value, name AS text"
		. "\n FROM #__categories"
		. "\n WHERE section = '$section'"
		. "\n AND published = 1"
		. "\n ORDER BY $order"
		;
		$database->setQuery( $query );
		if ( $sel_cat ) {
			$categories[] = mosHTML::makeOption( '0', '- '. $_LANG->_( 'Select a Category' ) .' -' );
			$categories = array_merge( $categories, $database->loadObjectList() );
		} else {
			$categories = $database->loadObjectList();
		}

		if ( count( $categories ) < 1 ) {
			mosRedirect( 'index2.php?option=com_categories&section='. $section, $_LANG->_( 'You must create a category first.' ) );
		}

		$category = mosHTML::selectList( $categories, $name, 'class="inputbox" size="'. $size .'" '. $javascript, 'value', 'text', $active );

		return $category;
	}

	/**
	* Select list of active sections
	*/
	function SelectSection( $name, $active=NULL, $javascript=NULL, $order='ordering' ) {
		global $database;
		global $_LANG;

		$categories[] = mosHTML::makeOption( '0', '- '. $_LANG->_( 'Select Section' ) .' -' );
		$query = "SELECT id AS value, title AS text"
		. "\n FROM #__sections"
		. "\n WHERE published = 1"
		. "\n ORDER BY $order"
		;
		$database->setQuery( $query );
		$sections = array_merge( $categories, $database->loadObjectList() );

		$category = mosHTML::selectList( $sections, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

		return $category;
	}

	/**
	* Select list of menu items for a specific menu
	*/
	function Links2Menu( $type, $and ) {
		global $database;

		$query = "SELECT *"
		. "\n FROM #__menu"
		. "\n WHERE type = '$type'"
		. "\n AND published = 1"
		. $and
		;
		$database->setQuery( $query );
		$menus = $database->loadObjectList();

		return $menus;
	}

	/**
	* Select list of menus
	*/
	function MenuSelect( $name='menuselect', $javascript=NULL ) {
		global $database;

		$query = "SELECT params"
		. "\n FROM #__modules"
		. "\n WHERE module = 'mod_mainmenu'"
		;
		$database->setQuery( $query );
		$menus = $database->loadObjectList();
		$total = count( $menus );
		for( $i = 0; $i < $total; $i++ ) {
			$params = mosParseParams( $menus[$i]->params );
			$menuselect[$i]->value 	= $params->menutype;
			$menuselect[$i]->text 	= $params->menutype;
		}
		// sort array of objects
		SortArrayObjects( $menuselect, 'text', 1 );

		$menus = mosHTML::selectList( $menuselect, $name, 'class="inputbox" size="10" '. $javascript, 'value', 'text' );

		return $menus;
	}

	/**
	* Internal function to recursive scan the media manager directories
	* @param string Path to scan
	* @param string root path of this folder
	* @param array  Value array of all existing folders
	* @param array  Value array of all existing images
	*/
	function ReadImages( $imagePath, $folderPath, &$folders, &$images ) {
		$imgFiles = mosReadDirectory( $imagePath );

		foreach ($imgFiles as $file) {
			$ff_ 	= $folderPath . $file .'/';
			$ff 	= $folderPath . $file;
			$i_f 	= $imagePath .'/'. $file;

			if ( is_dir( $i_f ) && $file <> 'CVS' && $file <> '.svn') {
				$folders[] = mosHTML::makeOption( $ff_ );
				mosAdminMenus::ReadImages( $i_f, $ff_, $folders, $images );
			} else if ( eregi( "bmp|gif|jpg|png", $file ) && is_file( $i_f ) ) {
				// leading / we don't need
				$imageFile = substr( $ff, 1 );
				$images[$folderPath][] = mosHTML::makeOption( $imageFile, $file );
			}
		}
	}

	function GetImageFolders( &$folders, $path ) {
		$javascript 	= "onchange=\"changeDynaList( 'imagefiles', folderimages, document.adminForm.folders.options[document.adminForm.folders.selectedIndex].value, 0, 0);  previewImage( 'imagefiles', 'view_imagefiles', '$path/' );\"";
		$getfolders 	= mosHTML::selectList( $folders, 'folders', 'class="inputbox" size="1" '. $javascript, 'value', 'text', '/' );
		return $getfolders;
	}

	function GetImages( &$images, $path ) {
		if ( !isset($images['/'] ) ) {
			$images['/'][] = mosHTML::makeOption( '' );
		}

		//$javascript	= "onchange=\"previewImage( 'imagefiles', 'view_imagefiles', '$path/' )\" onfocus=\"previewImage( 'imagefiles', 'view_imagefiles', '$path/' )\"";
		$javascript	= "onchange=\"previewImage( 'imagefiles', 'view_imagefiles', '$path/' )\"";
		$getimages	= mosHTML::selectList( $images['/'], 'imagefiles', 'class="inputbox" size="10" multiple="multiple" '. $javascript , 'value', 'text', null );

		return $getimages;
	}

	function GetSavedImages( &$row, $path ) {
		$images2 = array();
		foreach( $row->images as $file ) {
			$temp = explode( '|', $file );
			if( strrchr($temp[0], '/') ) {
				$filename = substr( strrchr($temp[0], '/' ), 1 );
			} else {
				$filename = $temp[0];
			}
			$images2[] = mosHTML::makeOption( $file, $filename );
		}
		//$javascript	= "onchange=\"previewImage( 'imagelist', 'view_imagelist', '$path/' ); showImageProps( '$path/' ); \" onfocus=\"previewImage( 'imagelist', 'view_imagelist', '$path/' )\"";
		$javascript	= "onchange=\"previewImage( 'imagelist', 'view_imagelist', '$path/' ); showImageProps( '$path/' ); \"";
		$imagelist 	= mosHTML::selectList( $images2, 'imagelist', 'class="inputbox" size="10" '. $javascript, 'value', 'text' );

		return $imagelist;
	}

	/**
	* Checks to see if an image exists in the current templates image directory
 	* if it does it loads this image.  Otherwise the default image is loaded.
	* Also can be used in conjunction with the menulist param to create the chosen image
	* load the default or use no image
	*/
	function ImageCheck( $file, $directory='/images/M_images/', $param=NULL, $param_directory='/images/M_images/', $alt=NULL, $name='image', $type=1, $align='middle' ) {
		global $mosConfig_absolute_path, $mosConfig_live_site, $mainframe;
		$cur_template = $mainframe->getTemplate();

		if ( $param ) {
			$image = $mosConfig_live_site. $param_directory . $param;
			if ( $type ) {
				$image = '<img src="'. $image .'" align="'. $align .'" alt="'. $alt .'" name="'. $name .'" border="0" />';
			}
		} else if ( $param == -1 ) {
			$image = '';
		} else {
			if ( file_exists( $mosConfig_absolute_path .'/templates/'. $cur_template .'/images/'. $file ) ) {
				$image = $mosConfig_live_site .'/templates/'. $cur_template .'/images/'. $file;
			} else {
				// outputs only path to image
				$image = $mosConfig_live_site. $directory . $file;
			}

			// outputs actual html <img> tag
			if ( $type ) {
				$image = '<img src="'. $image .'" alt="'. $alt .'" align="'. $align .'" name="'. $name .'" border="0" />';
			}
		}

		return $image;
	}

	/**
	* Checks to see if an image exists in the current templates image directory
 	* if it does it loads this image.  Otherwise the default image is loaded.
	* Also can be used in conjunction with the menulist param to create the chosen image
	* load the default or use no image
	*/
	function ImageCheckAdmin( $file, $directory='/administrator/images/', $param=NULL, $param_directory='/administrator/images/', $alt=NULL, $name=NULL, $type=1, $align='middle' ) {
		global $mosConfig_absolute_path, $mosConfig_live_site, $mainframe;
		$cur_template = $mainframe->getTemplate();

		if ( $param ) {
			$image = $mosConfig_live_site. $param_directory . $param;
			if ( $type ) {
				$image = '<img src="'. $image .'" align="'. $align .'" alt="'. $alt .'" name="'. $name .'" border="0" />';
			}
		} else if ( $param == -1 ) {
			$image = '';
		} else {
			if ( file_exists( $mosConfig_absolute_path .'/administrator/templates/'. $cur_template .'/images/'. $file ) ) {
				$image = $mosConfig_live_site .'/administrator/templates/'. $cur_template .'/images/'. $file;
			} else {
				$image = $mosConfig_live_site. $directory . $file;
			}

			// outputs actual html <img> tag
			if ( $type ) {
				$image = '<img src="'. $image .'" alt="'. $alt .'" align="'. $align .'" name="'. $name .'" border="0" />';
			}
		}

		return $image;
	}

	function menutypes() {
		global $database;

		$query = "SELECT params"
		. "\n FROM #__modules"
		. "\n WHERE module = 'mod_mainmenu'"
		. "\n ORDER BY title"
		;
		$database->setQuery( $query	);
		$modMenus = $database->loadObjectList();

		$query = "SELECT menutype"
		. "\n FROM #__menu"
		. "\n GROUP BY menutype"
		. "\n ORDER BY menutype"
		;
		$database->setQuery( $query	);
		$menuMenus = $database->loadObjectList();

		$menuTypes = '';
		foreach ( $modMenus as $modMenu ) {
			$check = 1;
			mosMakeHtmlSafe( $modMenu) ;
			$modParams 	= mosParseParams( $modMenu->params );
			$menuType 	= @$modParams->menutype;
			if (!$menuType) {
				$menuType = 'mainmenu';
			}

			// stop duplicate menutype being shown
			if ( !is_array( $menuTypes) ) {
				// handling to create initial entry into array
				$menuTypes[] = $menuType;
			} else {
				$check = 1;
				foreach ( $menuTypes as $a ) {
					if ( $a == $menuType ) {
						$check = 0;
					}
				}
				if ( $check ) {
					$menuTypes[] = $menuType;
				}
			}

		}
		// add menutypes from jos_menu
		foreach ( $menuMenus as $menuMenu ) {
			$check = 1;
			foreach ( $menuTypes as $a ) {
				if ( $a == $menuMenu->menutype ) {
					$check = 0;
				}
			}
			if ( $check ) {
				$menuTypes[] = $menuMenu->menutype;
			}
		}

		// sorts menutypes
		asort( $menuTypes );

		return $menuTypes;
	}

	/*
	* loads files required for menu items
	*/
	function menuItem( $item ) {
		global $mosConfig_absolute_path;

		$path = $mosConfig_absolute_path .'/administrator/components/com_menus/'. $item .'/';
		include_once( $path . $item .'.class.php' );
		include_once( $path . $item .'.menu.html.php' );
	}
}


class mosCommonHTML {

	function ContentLegend( ) {
		global $_LANG;
		?>
		<table cellspacing="0" cellpadding="4" border="0" align="center">
		<tr align="center">
			<td>
			<img src="images/publish_y.png" width="12" height="12" border="0" alt="<?php echo $_LANG->_( 'Pending' ); ?>" />
			</td>
			<td>
			<?php echo $_LANG->_( 'Published, but is' ); ?> <u><?php echo $_LANG->_( 'Pending' ); ?></u> |
			</td>
			<td>
			<img src="images/publish_g.png" width="12" height="12" border="0" alt="<?php echo $_LANG->_( 'Visible' ); ?>" />
			</td>
			<td>
			<?php echo $_LANG->_( 'Published and is' ); ?> <u><?php echo $_LANG->_( 'Current' ); ?></u> |
			</td>
			<td>
			<img src="images/publish_r.png" width="12" height="12" border="0" alt="<?php echo $_LANG->_( 'Finished' ); ?>" />
			</td>
			<td>
			<?php echo $_LANG->_( 'Published, but has' ); ?> <u><?php echo $_LANG->_( 'Expired' ); ?></u> |
			</td>
			<td>
			<img src="images/publish_x.png" width="12" height="12" border="0" alt="<?php echo $_LANG->_( 'Finished' ); ?>" />
			</td>
			<td>
			<?php echo $_LANG->_( 'Not Published' ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="8" align="center">
			<?php echo $_LANG->_( 'Click on icon to toggle state.' ); ?>
			</td>
		</tr>
		</table>
		<?php
	}

	function menuLinksContent( &$menus ) {
		global $_LANG;
		?>
		<script language="javascript" type="text/javascript">
		function go2( pressbutton, menu, id ) {
			var form = document.adminForm;

			if (pressbutton == 'go2menu') {
				form.menu.value = menu;
				submitform( pressbutton );
				return;
			}

			if (pressbutton == 'go2menuitem') {
				form.menu.value 	= menu;
				form.menuid.value 	= id;
				submitform( pressbutton );
				return;
			}
		}
		</script>
		<?php
		foreach( $menus as $menu ) {
			?>
			<tr>
				<td colspan="2">
				<hr />
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'Menu' ); ?>
				</td>
				<td>
				<a href="javascript:go2( 'go2menu', '<?php echo $menu->menutype; ?>' );" title="<?php echo $_LANG->_( 'Go to Menu' ); ?>">
				<?php echo $menu->menutype; ?>
				</a>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'Link Name' ); ?>
				</td>
				<td>
				<strong>
				<a href="javascript:go2( 'go2menuitem', '<?php echo $menu->menutype; ?>', '<?php echo $menu->id; ?>' );" title="<?php echo $_LANG->_( 'Go to Menu Item' ); ?>">
				<?php echo $menu->name; ?>
				</a>
				</strong>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'State' ); ?>
				</td>
				<td>
				<?php
				switch ( $menu->published ) {
					case -2:
						echo '<font color="red">'. $_LANG->_( 'Trashed' ) .'</font>';
						break;
					case 0:
						echo $_LANG->_( 'UnPublished' );
						break;
					case 1:
					default:
						echo '<font color="green">'. $_LANG->_( 'Published' ) .'</font>';
						break;
				}
				?>
				</td>
			</tr>
			<?php
		}
		?>
		<input type="hidden" name="menu" value="" />
		<input type="hidden" name="menuid" value="" />
		<?php
	}

	function menuLinksSecCat( &$menus ) {
		global $_LANG;
		?>
		<script language="javascript" type="text/javascript">
		function go2( pressbutton, menu, id ) {
			var form = document.adminForm;

			if (pressbutton == 'go2menu') {
				form.menu.value = menu;
				submitform( pressbutton );
				return;
			}

			if (pressbutton == 'go2menuitem') {
				form.menu.value 	= menu;
				form.menuid.value 	= id;
				submitform( pressbutton );
				return;
			}
		}
		</script>
		<?php
		foreach( $menus as $menu ) {
			?>
			<tr>
				<td colspan="2">
				<hr/>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'Menu' ); ?>
				</td>
				<td>
				<a href="javascript:go2( 'go2menu', '<?php echo $menu->menutype; ?>' );" title="<?php echo $_LANG->_( 'Go to Menu' ); ?>">
				<?php echo $menu->menutype; ?>
				</a>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'Type' ); ?>
				</td>
				<td>
				<?php echo $menu->type; ?>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'Item Name' ); ?>
				</td>
				<td>
				<strong>
				<a href="javascript:go2( 'go2menuitem', '<?php echo $menu->menutype; ?>', '<?php echo $menu->id; ?>' );" title="<?php echo $_LANG->_( 'Go to Menu Item' ); ?>">
				<?php echo $menu->name; ?>
				</a>
				</strong>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'State' ); ?>
				</td>
				<td>
				<?php
				switch ( $menu->published ) {
					case -2:
						echo '<font color="red">'. $_LANG->_( 'Trashed' ) .'</font>';
						break;
					case 0:
						echo $_LANG->_( 'UnPublished' );
						break;
					case 1:
					default:
						echo '<font color="green">'. $_LANG->_( 'Published' ) .'</font>';
						break;
				}
				?>
				</td>
			</tr>
			<?php
		}
		?>
		<input type="hidden" name="menu" value="" />
		<input type="hidden" name="menuid" value="" />
		<?php
	}

	function checkedOut( &$row, $overlib=1 ) {
		global $_LANG;

		$hover = '';
		if ( $overlib ) {
			$date 				= mosFormatDate( $row->checked_out_time, '%A, %d %B %Y' );
			$time				= mosFormatDate( $row->checked_out_time, '%H:%M' );
			$checked_out_text 	= '<table>';
			$checked_out_text 	.= '<tr><td>'. $row->editor .'</td></tr>';
			$checked_out_text 	.= '<tr><td>'. $date .'</td></tr>';
			$checked_out_text 	.= '<tr><td>'. $time .'</td></tr>';
			$checked_out_text 	.= '</table>';
			$hover = 'onMouseOver="return overlib(\''. $checked_out_text .'\', CAPTION, \''. $_LANG->_( 'Checked Out' ) .'\', BELOW, RIGHT);" onMouseOut="return nd();"';
		}
		$checked	 		= '<img src="images/checked_out.png" '. $hover .'/>';

		return $checked;
	}

	/*
	* Loads all necessary files for JS Overlib tooltips
	*/
	function loadOverlib() {
		global  $mosConfig_live_site, $mainframe;
		
		if ( !$mainframe->get( 'loadOverlib' ) ) {
		// check if this function is already loaded
			?>
			<script language="Javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_mini.js"></script>
			<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
			<?php
			// change state so it isnt loaded a second time
			$mainframe->set( 'loadOverlib', true );
		}
	}


	/*
	* Loads all necessary files for JS Calendar
	*/
	function loadCalendar() {
		global  $mosConfig_live_site;
		global $_LANG;
		?>
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo $mosConfig_live_site;?>/includes/js/calendar/calendar-mos.css" title="<?php echo $_LANG->_( 'green' ); ?>" />
		<!-- import the calendar script -->
		<script type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/calendar/calendar_mini.js"></script>
		<!-- import the language module -->
		<script type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/calendar/lang/calendar-en.js"></script>
		<?php
	}

	function AccessProcessing( &$row, $i ) {
		if ( !$row->access ) {
			$color_access = 'style="color: green;"';
			$task_access = 'accessregistered';
		} else if ( $row->access == 1 ) {
			$color_access = 'style="color: red;"';
			$task_access = 'accessspecial';
		} else {
			$color_access = 'style="color: black;"';
			$task_access = 'accesspublic';
		}

		$href = '
		<a href="javascript: void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task_access .'\')" '. $color_access .'>
		'. $row->groupname .'
		</a>'
		;

		return $href;
	}

	function CheckedOutProcessing( &$row, $i ) {
		global $my;

		if ( $row->checked_out ) {
			$checked = mosCommonHTML::checkedOut( $row );
		} else {
			$checked = mosHTML::idBox( $i, $row->id, ($row->checked_out && $row->checked_out != $my->id ) );
		}

		return $checked;
	}

	function PublishedProcessing( &$row, $i ) {
		global $_LANG;

		$img 	= $row->published ? 'publish_g.png' : 'publish_x.png';
		$task 	= $row->published ? 'unpublish' : 'publish';
		$alt 	= $row->published ? $_LANG->_( 'Published' ) : $_LANG->_( 'Unpublished' );
		$action	= $row->published ? 'Unpublish Item' : 'Publish item';

		$href = '
		<a href="javascript: void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
		<img src="images/'. $img .'" border="0" alt="'. $alt .'" />
		</a>'
		;

		return $href;
	}
}

/**
* Sorts an Array of objects
*/
function SortArrayObjects_cmp( &$a, &$b ) {
	global $csort_cmp;

	if ( $a->$csort_cmp['key'] > $b->$csort_cmp['key'] ) {
		return $csort_cmp['direction'];
	}

	if ( $a->$csort_cmp['key'] < $b->$csort_cmp['key'] ) {
		return -1 * $csort_cmp['direction'];
	}

	return 0;
}

/**
* Sorts an Array of objects
* sort_direction [1 = Ascending] [-1 = Descending]
*/
function SortArrayObjects( &$a, $k, $sort_direction=1 ) {
	global $csort_cmp;

	$csort_cmp = array(
		'key'		  => $k,
		'direction'	=> $sort_direction
	);

	usort( $a, 'SortArrayObjects_cmp' );

	unset( $csort_cmp );
}

/**
* Sends mail to admin
*/
function mosSendAdminMail( $adminName, $adminEmail, $email, $type, $title, $author ) {
	global $mosConfig_live_site;
	global $_LANG;

	$subject = $_LANG->_( 'MAIL_SUB' )." '$type'";
	$message = $_LANG->_( 'MAIL_MSG' );
	eval ("\$message = \"$message\";");
	mosMail($mosConfig_mailfrom, $mosConfig_fromname, $adminEmail, $subject, $message);
}

/*
* Includes pathway file
*/
function mosPathWay() {
	$Itemid = mosGetParam($_REQUEST,'Itemid','');
	require $GLOBALS['mosConfig_absolute_path'] . '/includes/pathway.php';
}

/**
 * method that sets SSL-ness of a url
 */
function mosLink( $url, $ssl=0, $sef=1 ) {
	global $mosConfig_live_site, $mosConfig_unsecure_site, $mosConfig_secure_site;

	if ( ( $sef == 1 ) && ( function_exists('sefRelToAbs' ) ) )
	$url = sefRelToAbs( $url );

	if ( substr( $url,0,4 ) != 'http' )
		$url = $mosConfig_live_site .'/'. $url;

	//ensure that proper secure site url is used if ssl flag set and url doesn't already include it
	if ($ssl == 1 && strstr($url, $mosConfig_unsecure_site)) {
		$url = str_replace( $mosConfig_unsecure_site, $mosConfig_secure_site , $url );
	} elseif ($ssl == -1 && strstr($url, $mosConfig_secure_site)) {
		$url = str_replace( $mosConfig_secure_site, $mosConfig_unsecure_site , $url );
	}

	return $url;
}

/**
* Displays a not authorised message
*
* If the user is not logged in then an addition message is displayed.
*/
function mosNotAuth() {
	global $my, $_LANG;

	echo $_LANG->_('NOT_AUTH');
	if ($my->id < 1) {
		echo "<br />" . $_LANG->_('You need to login');
	}
}

/**
* Replaces &amp; with & for xhtml compliance
*
* Needed to handle unicode conflicts due to unicode conflicts
*/
function ampReplace( $text ) {
	$text = str_replace( '&#', '*-*', $text );
	$text = preg_replace( '|&(?![\w]+;)|', '&amp;', $text );
    $text = str_replace( '&amp;amp;', '&amp;', $text );
	$text = str_replace( '*-*', '&#', $text );

	return $text;
}

/**
* Prepares results from search for display
* @param string The source string
* @param int Number of chars to trim
* @param string The searchword to select around
* @return string
*/
function mosPrepareSearchContent( $text, $length=200, $searchword ) {
	// strips tags won't remove the actual jscript
	$text = preg_replace( "'<script[^>]*>.*?</script>'si", "", $text );
	$text = preg_replace( '/{.+?}/', '', $text);
	//$text = preg_replace( '/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2', $text );
	return mosSmartSubstr( strip_tags( $text ), $length, $searchword );
}

/**
* returns substring of characters around a searchword
* @param string The source string
* @param int Number of chars to return
* @param string The searchword to select around
* @return string
*/
function mosSmartSubstr($text, $length=200, $searchword) {
  $wordpos = strpos(strtolower($text), strtolower($searchword));
  $halfside = intval($wordpos - $length/2 - strlen($searchword));
  if ($wordpos && $halfside > 0) {
	  return '...' . substr($text, $halfside, $length);
  } else {
	return substr( $text, 0, $length);
  }
}

/**
 * Function to convert array to integer values
 * @param array
 * @param int A default value to assign if $array is not an array
 * @return array
 */
function mosArrayToInts( &$array, $default=null ) {
	if (is_array( $array )) {
		$n = count( $array );
		for ($i = 0; $i < $n; $i++) {
			$array[$i] = intval( $array[$i] );
		}
	} else {
		if (is_null( $default )) {
			return array();
		} else {
			return array( $default );
		}
	}
}

/**
 * Utility class for helping with patTemplate
 */
class patHTML {
	/**
	 * Converts a named array to an array or named rows suitable to option lists
	 * @param array The source array[key] = value
	 * @param mixed A value or array of selected values
	 * @param string The name for the value field
	 * @param string The name for selected attribute (use 'checked' for radio of box lists)
	 */
	function selectArray( &$source, $selected=null, $valueName='value', $selectedAttr='selected' ) {
		if (!is_array( $selected )) {
			$selected = array( $selected );
		}
		foreach ($source as $i => $row) {
			if (is_object( $row )) {
				$source[$i]->selected = in_array( $row->$valueName, $selected ) ? $selectedAttr . '="true"' : '';
			} else {
				$source[$i]['selected'] = in_array( $row[$valueName], $selected ) ? $selectedAttr . '="true"' : '';
			}
		}
	}

	/**
	 * Converts a named array to an array or named rows suitable to checkbox or radio lists
	 * @param array The source array[key] = value
	 * @param mixed A value or array of selected values
	 * @param string The name for the value field
	 */
	function checkArray( &$source, $selected=null, $valueName='value' ) {
		patHTML::selectArray( $source, $selected, $valueName, 'checked' );
	}

	/**
	 * @param mixed The value for the option
	 * @param string The text for the option
	 * @param string The name of the value parameter (default is value)
	 * @param string The name of the text parameter (default is text)
	 */
	function makeOption( $value, $text, $valueName='value', $textName='text' ) {
		return array(
			$valueName => $value,
			$textName => $text
		);
	}

	/**
	 * Writes a radio pair
	 * @param object Template object
	 * @param string The template name
	 * @param string The field name
	 * @param int The value of the field
	 * @param array Array of options
	 * @param string Optional template variable name
	 */
	function radioSet( &$tmpl, $template, $name, $value, $a, $varname=null ) {
		patHTML::checkArray( $a, $value );

		$tmpl->addVar( 'radio-set', 'name', $name );
		$tmpl->addRows( 'radio-set', $a );
		$tmpl->parseIntoVar( 'radio-set', $template, is_null( $varname ) ? $name : $varname );
	}

	/**
	 * Writes a radio pair
	 * @param object Template object
	 * @param string The template name
	 * @param string The field name
	 * @param int The value of the field
	 * @param string Optional template variable name
	 */
	function yesNoRadio( &$tmpl, $template, $name, $value, $varname=null ) {
		global $_LANG;
		$a = array(
			patHTML::makeOption( 0, $_LANG->_( $noText ) ),
			patHTML::makeOption( 1, $_LANG->_( $yesText ) )
		);
		patHTML::radioSet( $tmpl, $template, $name, $value, $a, $varname );
	}
}

/**
 * Provides a secure hash based on a seed
 * @param string Seed string
 * @return string
 */
function mosHash( $seed ) {
	return md5( $GLOBALS['mosConfig_secret'] . md5( $seed ) );
}

// ----- NO MORE CLASSES OR FUNCTIONS PASSED THIS POINT -----
// Post class declaration initialisations
// some version of PHP don't allow the instantiation of classes
// before they are defined

/** @global mosPlugin $_MAMBOTS */
$_MAMBOTS = new mosMambotHandler();
?>
