<?php
/**
* @version $Id: mambo.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );
define( '_MOS_MAMBO_INCLUDED', 1 );

/**
 * Page generation time
 * @package Mambo
 */
class mosProfiler {
	/** @var int */
	var $start=0;
	/** @var string */
	var $prefix='';
	/** @var array */
	var $buffer= null;

	/**
	 * Constructor
	 * @var string Prefix for mark messages
	 */
	function mosProfiler( $prefix='' ) {
		$this->start = $this->getmicrotime();
		$this->prefix = $prefix;
		$this->buffer = array();
	}

	/**
	 * Output a time mark
	 * @var string A label for the time mark
	 */
	function mark( $label ) {
		$mark = sprintf ( "\n<div class=\"profiler\">$this->prefix %.3f $label</div>", $this->getmicrotime() - $this->start );
		$this->buffer[] = $mark;
		return $mark;
	}

	/**
	 * Reports on the buffered marks
	 * @param string Glue string
	 */
	function report( $glue='' ) {
		return implode( $glue, $this->buffer );
	}

	/**
	 * @return float The current time
	 */
	function getmicrotime(){
		list( $usec, $sec ) = explode( ' ', microtime() );
		return ((float)$usec + (float)$sec);
	}

	function getMemory() {
		static $isWin;

		if (function_exists( 'memory_get_usage' )) {
			return memory_get_usage();
		} else {
			if (is_null( $isWin )) {
				$isWin = (substr(PHP_OS, 0, 3) == 'WIN');
			}
			if ($isWin) {
				// Windows workaround
				$output = array();
				$pid = getmypid();
				exec( 'tasklist /FI "PID eq ' . $pid . '" /FO LIST', $output );
				return substr( $output[5], strpos( $output[5], ':' ) + 1 );
			} else {
				return 0;
			}
		}
	}
}

// page generation speed calculator
$GLOBALS['_MOS_PROFILER'] = new mosProfiler( 'Core' );

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

if (empty( $mosConfig_zero_date )) {
	$mosConfig_zero_date = '0000-00-00 00:00:00';
}

$path = $mosConfig_absolute_path . '/includes';
require_once( $path .'/version.php' );
require_once( $path .'/mambo.factory.php' );
require_once( $path .'/database.php' );
require_once( $path .'/phpmailer/class.phpmailer.php' );
require_once( $path .'/mamboxml.php' );
require_once( $path .'/mambo.files.php' );
require_once( $path .'/mambo.cache.php' );
require_once( $path .'/phpInputFilter/class.inputfilter.php' );

$database = new database( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix, @$mosConfig_dbtype );
if ($database->getErrorNum()) {
	$mosSystemError = $database->getErrorNum();
	$basePath = dirname( __FILE__ );
	include $basePath . '/../configuration.php';
	include $basePath . '/../offline.php';
	exit();
}
$database->debug( @$mosConfig_debug_db );
$acl =& mosFactory::getACL( $database );

/**
 * @package Mambo
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
 * @package Mambo
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
		echo 'Task ' . $task . ' not found';

		return null;
	}
	/**
	 * Basic method if the registered method is not found
	 * @param string The name of the method in the derived class
	 * @return null
	 */
	function methodNotFound( $name ) {
		echo 'Method ' . $name . ' not found';

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
* Class to support singleton objects
* @package Mambo
*/
class mosSingleton
{
	/**
	 * If an instance exists return the instance, otherwise created it
	 * @param string The name of the class
	 * @param string  A callback funtion that creates the instance
	 * @return an object instance
	 */

	function &getInstance($singletonClassName, $callback = null)
	{
		if (!isset($GLOBALS['_MOS_SINGLETON'][strtolower($singletonClassName)])) {
			mosSingleton::_createInstance($singletonClassName, $callback);
		}
		return $GLOBALS['_MOS_SINGLETON'][strtolower($singletonClassName)];
	}

	function _createInstance($singletonClassName, $callback = null)
	{
		if (!isset($GLOBALS['_MOS_SINGLETON'])) {
			$GLOBALS['_MOS_SINGLETON'] = array();
		}
		if(is_null($callback)) {
			$result = new $singletonClassName;
		} else {
			 if (strstr($callback, '::')) { 		// classname::staticMethod
				list($class, $method) = explode('::', $callback);
				$result =& call_user_func_array(array($class, $method), null);
			} else if (strstr($callback, '->')) { 	// object->method
				// use a stupid name ($objet_123456789 because) of problems when the object
				// name is the same as this var name
				list($object_123456789, $method) = explode('->', $callback);
				global $$object_123456789;
				$result =& call_user_func_array(array($$object_123456789, $method), null);
			} else { 								// function
				$result =& call_user_func_array($callback);
			}
		}

		$GLOBALS['_MOS_SINGLETON'][strtolower($singletonClassName)] =& $result;
	}
}

/**
* Joomla! Mainframe class
*
* Provide many supporting API functions
* @package Mambo
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
	/** @var string The path to the current template directory  */
	var $_templatePath		= null;
	/** @var string The URL of the current template directory */
	var $_templateURL		= null;
	/** @var boolean True if the admin client */
	var $_isAdmin 			= null;

	/**
	* Class constructor
	* @param database A database connection object
	* @param string The url option [DEPRECATED]
	* @param string The path of the mos directory
	*/
	function mosMainFrame( &$db, $option, $isAdmin=false ) {
		$this->_db =& $db;

		$this->_setTemplate( $isAdmin );

		if (!isset( $_SESSION['session_userstate'] )) {
			$_SESSION['session_userstate'] = array();
		}
		$this->_userstate =& $_SESSION['session_userstate'];

		$this->_head 			= array();
		$this->_head['title'] 	= $GLOBALS['mosConfig_sitename'];
		$this->_head['meta'] 	= array();
		$this->_head['custom'] 	= array();
		$this->_isAdmin 		= (boolean) $isAdmin;
	}

	/**
	* @param string
	*/
	function setPageTitle( $title=null ) {
		if ( @$GLOBALS['mosConfig_pagetitles'] ) {
			$title = trim( htmlspecialchars( $title ) );
			switch ( $GLOBALS['mosConfig_pagetitles_format'] ) {
				case 2:
					// <title> Title - Site </title>
					$title = $title .' - '. $GLOBALS['mosConfig_sitename'];
					break;

				case 3:
					// <title> Title </title>
					break;

				case 1:
				default:
					// <title> Site - Title </title>
					$title = $GLOBALS['mosConfig_sitename'] . ' - '. $title;
					break;
			}
			$this->_head['title'] = $title;
		}
	}
	/**
	* @param string The value of the name attibute
	* @param string The value of the content attibute
	* @param string Text to display before the tag
	* @param string Text to display after the tag
	*/
	function addMetaTag( $name, $content, $prepend='', $append='' ) {
		$name 		= trim( htmlspecialchars( $name ) );
		$content 	= trim( htmlspecialchars( $content ) );
		$prepend 	= trim( $prepend );
		$append 	= trim( $append );
		$this->_head['meta'][] = array( $name, $content, $prepend, $append );
	}
	/**
	* @param string The value of the name attibute
	* @param string The value of the content attibute to append to the existing
	* Tags ordered in with Site Keywords and Description first
	*/
	function appendMetaTag( $name, $content ) {
		$name 	= trim( strip_tags( $name ) );
		$name 	= htmlspecialchars( $name );
		$n 		= count( $this->_head['meta'] );
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
	/** Is admin interface?
	 * @return boolean
	 */
	function isAdmin() {
		return $this->_isAdmin;
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
	function getUserState( $var_name, $var_default=null ) {
		if (is_array( $this->_userstate )) {
			return mosGetParam( $this->_userstate, $var_name, $var_default );
		} else {
			return $var_default;
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
			return $var_default;
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
				if ( !$acl->acl_check( 'login', $client, 'users', $user->usertype ) ) {
					return false;
				}

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

		$user->id 		= intval( $this->_session->userid );
		$user->username = $this->_session->username;
		$user->usertype = $this->_session->usertype;
		$user->gid 		= intval( $this->_session->gid );

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
		$user->params = new mosParameters( $params );

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
	/**
	 * Sets the active template
	 * @param boolean True for administator templates
	 */
	function _setTemplate( $isAdmin=false ) {
		global $Itemid;

		$mosConfig_absolute_path 	= $this->getCfg( 'absolute_path' );
		$mosConfig_live_site 		= $this->getCfg( 'live_site' );

		// Default template
		$query = "SELECT template"
		. "\n FROM #__templates_menu"
		. "\n WHERE client_id = '0'"
		. "\n AND menuid = '0'"
		;
		$this->_db->setQuery( $query );
		$cur_template = $this->_db->loadResult();

		// Assigned template
		if ( isset( $Itemid ) && $Itemid != '' && $Itemid != 0) {
			$query = "SELECT template"
			. "\n FROM #__templates_menu"
			. "\n WHERE client_id = '0'"
			. "\n AND menuid = '$Itemid'"
			;
			$this->_db->setQuery( $query );
			$cur_template = $this->_db->loadResult() ? $this->_db->loadResult() : $cur_template;
		}

		if ( $isAdmin ) {
			$query = "SELECT template"
			. "\n FROM #__templates_menu"
			. "\n WHERE client_id = '1'"
			. "\n AND menuid = '0'"
			;
			$this->_db->setQuery( $query );
			$cur_template = $this->_db->loadResult();
			$path = $mosConfig_absolute_path .'/administrator/templates/'. $cur_template .'/index.php';
			if (!file_exists( $path )) {
				$cur_template = 'mambo_admin';
			}
			$this->_templatePath 	= mosFS::getNativePath( $mosConfig_absolute_path . '/administrator/templates/' . $cur_template );
			$this->_templateURL 	= $mosConfig_live_site . '/administrator/templates/' . $cur_template;
		} else {
			// TemplateChooser Start
			$mos_user_template 		= mosGetParam( $_COOKIE, 'mos_user_template', '' );
			$mos_change_template 	= mosGetParam( $_REQUEST, 'mos_change_template', $mos_user_template );
			if ($mos_change_template) {
				// check that template exists in case it was deleted
				if ( file_exists( $mosConfig_absolute_path .'/templates/'. $mos_change_template .'/index.php' ) ) {
					$lifetime 		= 60*10;
					$cur_template 	= $mos_change_template;
					setcookie( 'mos_user_template', $mos_change_template, time()+$lifetime);
				} else {
					setcookie( 'mos_user_template', '', time()-3600 );
				}
			}
			// TemplateChooser End
			$this->_templatePath 	= mosFS::getNativePath( $mosConfig_absolute_path . '/templates/' . $cur_template );
			$this->_templateURL 	= $mosConfig_live_site . '/templates/' . $cur_template;
		}

		$this->_template = $cur_template;
	}

	/**
	 * Gets the name of the current template
	 * @return string
	 */
	function getTemplate() {
		return $this->_template;
	}

	/**
	 * Get the path to the current template
	 */
	function getTemplatePath() {
		return $this->_templatePath;
	}

	/**
	 * Get the path to the current template
	 */
	function getTemplateURL() {
		return $this->_templateURL;
	}

	/**
	 * Tries to find a file in the administrator or site areas
	 * @param string A file name
	 * @param int 0 to check site, 1 to check site and admin only, -1 to check admin only
	 */
	function _checkPath( $path, $checkAdmin=1 ) {
		global $mosConfig_absolute_path;

		$file = $mosConfig_absolute_path . $path;
		if ($checkAdmin > -1 && file_exists( $file )) {
			return $file;
		} else if ($checkAdmin != 0) {
			$file = $mosConfig_absolute_path . '/administrator' . $path;
			if (file_exists( $file )) {
				return $file;
			}
		}

		return null;
	}
	/**
	* Returns a stored path variable
	*
	*/
	function getPath( $varname, $user_option=null ) {
		global $mosConfig_absolute_path;

		if ( !$user_option ) {
			$user_option = $GLOBALS['option'];
		}
		$result = null;
		$name 	= substr( $user_option, 4 );
		if (isset( $this->_path->$varname ) ) {
			$result = $this->_path->$varname;
		} else {
			switch ($varname) {
				case 'front':
					$result = $this->_checkPath( '/components/'. $user_option .'/'. $name .'.php', 0 );
					break;

				case 'html':
				case 'front_html':
					if ( !( $result = $this->_checkPath( '/templates/'. $this->_template .'/components/'. $name .'.html.php', 0 ) ) ) {
						$result = $this->_checkPath( '/components/'. $user_option .'/'. $name .'.html.php', 0 );
					}
					break;

				case 'linkbar':
					$result = $this->_checkPath( '/components/'. $user_option .'/linkbar.'. $name .'.php', -1 );
					break;

				case 'toolbar':
					$result = $this->_checkPath( '/components/'. $user_option .'/toolbar.'. $name .'.php', -1 );
					break;

				case 'toolbar_html':
					$result = $this->_checkPath( '/components/'. $user_option .'/toolbar.'. $name .'.html.php', -1 );
					break;

				case 'toolbar_default':
				case 'toolbar_front':
					$result = $this->_checkPath( '/includes/HTML_toolbar.php', 0 );
					break;

				case 'admin':
					$path 	= '/components/'. $user_option .'/admin.'. $name .'.php';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'admin_html':
					$path	= '/components/'. $user_option .'/admin.'. $name .'.html.php';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'class':
					if ( !( $result = $this->_checkPath( '/components/'. $user_option .'/'. $name .'.class.php' ) ) ) {
						$result = $this->_checkPath( '/includes/'. $name .'.php' );
					}
					break;

				case 'com_xml':
					$path 	= '/components/'. $user_option .'/'. $name .'.xml';
					$result = $this->_checkPath( $path, 1 );
					break;

				case 'mod0_xml':
					// Site modules
					if ( $user_option == '' ) {
						$path = '/modules/custom.xml';
					} else {
						$path = '/modules/'. $user_option .'.xml';
					}
					$result = $this->_checkPath( $path, 0 );
					break;

				case 'mod1_xml':
					// admin modules
					if ($user_option == '') {
						$path = '/modules/custom.xml';
					} else {
						$path = '/modules/'. $user_option .'.xml';
					}
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'bot_xml':
					// Site mambots
					$path 	= '/mambots/'. $user_option .'.xml';
					$result = $this->_checkPath( $path, 0 );
					break;

				case 'menu_xml':
					$path 	= '/components/com_menus/'. $user_option .'/'. $user_option .'.xml';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'commonmenu_xml':
					$path 	= '/components/com_menus/menu.common.xml';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'blogmenu_xml':
					$path 	= '/components/com_menus/menu.content.blog.xml';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'tablemenu_xml':
					$path 	= '/components/com_menus/menu.content.table.xml';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'installer_html':
					$path 	= '/components/com_installer/'. $user_option .'/'. $user_option .'.html.php';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'installer_class':
					$path 	= '/components/com_installer/'. $user_option .'/'. $user_option .'.class.php';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'admin_functions':
					$path 	= '/components/'. $user_option .'/'. $name .'.functions.php';
					$result = $this->_checkPath( $path, -1 );
					break;
			}
		}

		return $result;
	}

	/**
	* @return correct Itemid for Content Item
	* Itemid given is last successful code block
	* ie Code blocks ordered in inheritance preference, lowest to highest
	*/
	function getItemid( $id, $typed=1, $link=1, $bs=1, $bc=1, $gbs=1 ) {
		global $Itemid;

		$_Itemid = NULL;

		if ( !$_Itemid && $typed ) {
			// Search for link - static content
			$query = "SELECT id "
			. "\n FROM #__menu "
			. "\n WHERE type='content_typed'"
			. "\n AND published = '1'"
			. "\n AND link = 'index.php?option=com_content&task=view&id=$id'"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}

		if ( !$_Itemid && $link ) {
			// Search for link - content item
			$query = "SELECT id"
			. "\n FROM #__menu"
			. "\n WHERE type='content_item_link'"
			. "\n AND published = '1'"
			. "\n AND link = 'index.php?option=com_content&task=view&id=$id'"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}

		if ( !$_Itemid ) {
			// Search for table - content section
			$query = "SELECT m.id "
			. "\n FROM #__content AS i"
			. "\n LEFT JOIN #__sections AS s ON i.sectionid=s.id"
			. "\n LEFT JOIN #__menu AS m ON m.componentid=s.id "
			. "\n WHERE m.type='content_section'"
			. "\n AND m.published = '1'"
			. "\n AND i.id = $id"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}

		if ( !$_Itemid ) {
			// Search for table - content category
			$query = "SELECT m.id "
			. "\n FROM #__content AS i"
			. "\n LEFT JOIN #__categories AS c ON i.catid=c.id"
			. "\n LEFT JOIN #__menu AS m ON m.componentid=c.id "
			. "\n WHERE m.type = 'content_category'"
			. "\n AND m.published = '1'"
			. "\n AND i.id = $id"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}

		if ( !$_Itemid && $bs ) {
			// Search for specific blog - content section
			$query = "SELECT m.id "
			. "\n FROM #__content AS i"
			. "\n LEFT JOIN #__sections AS s ON i.sectionid=s.id"
			. "\n LEFT JOIN #__menu AS m ON m.componentid=s.id "
			. "\n WHERE m.type = 'content_blog_section'"
			. "\n AND m.published = '1'"
			. "\n AND i.id = $id"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}

		if ( !$_Itemid && $bc ) {
			// Search for specific blog - content category
			$query = "SELECT m.id "
			. "\n FROM #__content AS i"
			. "\n LEFT JOIN #__categories AS c ON i.catid=c.id"
			. "\n LEFT JOIN #__menu AS m ON m.componentid=c.id "
			. "\n WHERE m.type = 'content_blog_category'"
			. "\n AND m.published = '1'"
			. "\n AND i.id = $id"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}

		if ( !$_Itemid && $gbs ) {
			// Search for global blog - content section
			$query = "SELECT id "
			. "\n FROM #__menu "
			. "\n WHERE type = 'content_blog_section'"
			. "\n AND published = '1'"
			. "\n AND componentid = 0"
			;
			$this->_db->setQuery( $query );
			$_Itemid = $this->_db->loadResult();
		}


		if ( $_Itemid != '' ) {
			return $_Itemid;
		} else {
			// If no Itemid found, use Itemid of page
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
		."\n AND m.published = '1'"
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
		. "\n  AND m.published = '1'"
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
		."\n AND published = '1'"
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
		."\n AND published = '1'"
		;
		$this->_db->setQuery( $query );
		$count = $this->_db->loadResult();

		return $count;
	}

	/**
	* @return number of Content Item Links
	*/
	function getContentItemLinkCount() {
		$query = "SELECT COUNT( id )"
		."\n FROM #__menu "
		."\n WHERE type = 'content_item_link'"
		."\n AND published = '1'"
		;
		$this->_db->setQuery( $query );
		$count = $this->_db->loadResult();

		return $count;
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
	 * Gets the client name
	 * @param int The client identifier
	 * @return strint The text name of the client
	 */
	function getClientName( $client_id ) {
		 // do not translate
		$clients = array( 'site', 'admin', 'installer' );
		return mosGetParam( $clients, $client_id, 'unknown' );
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
	 * @return string The name of the temp directory
	 */
	function getTempDirectory() {
		$dir = mosFS::getNativePath( $GLOBALS['mosConfig_absolute_path'] . '/media' );
		return $dir;
	}

	/**
	 * Set the Pages SEO Title, meta keywords & description
	 */
	function setPageMeta( $title, $keywords, $descrip ) {
		// Dynamic Page Title
		$this->SetPageTitle( $title );

		// Meta Keywords
		if ( $keywords ) {
			$this->appendMetaTag( 'keywords', $keywords );
		}

		// Meta Description
		if ( $descrip ) {
			$this->appendMetaTag( 'description', $descrip );
		}
	}

	/*
	* Get the current date and time taking into account the offset from the server time
	*/
	function getDateTime() {
		global $mosConfig_offset;

		$format	= 'Y-m-d H:i:s';
		$now 	= date( $format, time() + $mosConfig_offset * 60 * 60 );

		return $now;
	}

	/**
	* @param string The name of the property
	* @param mixed The value of the property to set
	* @since 4.5.3
	*/
	function set( $property, $value=null ) {
		$this->$property = $value;
	}

	/**
	* @param string The name of the property
	* @param mixed  The default value
	* @return mixed The value of the property
	* @since 4.5.3
	*/
	function get($property, $default=null) {
		if(isset($this->$property)) {
			return $this->$property;
		} else {
			return $default;
		}
	}

	function getAdmins() {
		global $database;

		$query = "SELECT *"
		. "\n FROM #__users"
		."\n WHERE gid = '25'"
		. "\n AND block = '0'"
		. "\n AND sendEmail = '1'"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();

		return $rows;
	}
}

/**
* Component database table class
* @package Mambo
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

	/**
	 * @param int The client number
	 * @param boolean Add trailing slash to path
	 */
	function getBasePath( $client, $addTrailingSlash=true ) {
		switch ($client) {
			case '1':
				$dir =  '/administrator/components';
				break;
			default:
				$dir = '/components';
		}

		return mosFS::getNativePath( $GLOBALS['mosConfig_absolute_path'] . $dir, $addTrailingSlash );
	}
}

/**
* Utility class for all HTML drawing classes
* @package Mambo
*/
class mosHTML {
	function makeOption( $value, $text='', $value_name='value', $text_name='text' ) {
		$obj = new stdClass;
		$obj->$value_name = $value;
		$obj->$text_name = trim( $text ) ? $text : $value;
		return $obj;
	}

  function writableCell( $folder ) {

  	echo '<tr>';
  	echo '<td class="item">' . $folder . '/</td>';
  	echo '<td align="left">';
  	echo is_writable( "../$folder" ) ? '<b><font color="green">Writeable</font></b>' : '<b><font color="red">Unwriteable</font></b>' . '</td>';
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
	function integerSelectList( $start, $end, $inc, $tag_name, $tag_attribs, $selected, $format='' ) {
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
	function radioList( &$arr, $tag_name, $tag_attribs, $selected=null, $key='value', $text='text', $spacer='&nbsp;&nbsp;&nbsp;&nbsp;' ) {
		reset( $arr );
		$html = '';
		for ($i=0, $n=count( $arr ); $i < $n; $i++ ) {
			$k 		= $arr[$i]->$key;
			$t 		= $arr[$i]->$text;
			$id 	= @$arr[$i]->id;

			if ( $id ) {
				$label = $id;
			} else {
				$label = $tag_name . $k;
			}


			if (is_array( $selected )) {
				foreach ($selected as $obj) {
					$k2 = $obj->$key;
					if ( $k == $k2 ) {
						$active = 'selected="selected"';
						break;
					}
				}
			} else {
				$active = ( $k == $selected ? 'checked="checked"' : '' );
			}
			$html .= '<input type="radio" name="'. $tag_name .'" value="'. $k .'" id="'. $label .'" '. $tag_attribs .' '. $active .'/>';
			$html .= '<label for="'. $label .'">';
			$html .= $t;
			$html .= '</label>';
			$html .= $spacer;
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
		global $mosConfig_live_site, $_LANG;

		$alts = array(
			'none' 	=> $_LANG->_( 'No Sorting' ),
			'asc' 	=> $_LANG->_( 'Sort Ascending' ),
			'desc' 	=> $_LANG->_( 'Sort Descending' ),
		);
		$next_state = 'asc';
		if ($state == 'asc') {
			$next_state = 'desc';
		} else if ($state == 'desc') {
			$next_state = 'none';
		}
		$alt 	= $_LANG->_( $alts[$next_state] );
		$link 	= $base_href .'&amp;field='. $field .'&amp;order='. $next_state;
		$image	= $mosConfig_live_site .'/images/M_images/sort_'. $state .'.png';
		$html 	= '<a href="'. $link .'" >';
		$html 	.= '<img src="'. $image .'" width="12" height="12" border="0" alt="{'. $alt .'}" />';
		$html 	.= '</a>';

		return $html;
	}

	/**
	* ## This function has been depreciated in the conversion to pT,  it is no longer used by the core but is being kept for backward compatability of 3PD ##
	* Writes Close Button
	*/
	function CloseButton ( &$params, $hide_js=NULL, $output=1 ) {
		global $_LANG;

		// displays close button in Pop-up window
		if ( $params->get( 'popup' ) && !$hide_js ) {
			if ( $output ) {
				?>
				<div align="center" style="margin-top: 30px; margin-bottom: 30px;">
				<a href='javascript:window.close();'>
				<span class="small">
				<?php echo $_LANG->_( 'PROMPT_CLOSE' );?>
				</span>
				</a>
				</div>
				<?php
			} else {
				$output = '
				<div align="center" style="margin-top: 30px; margin-bottom: 30px;">
				<a href=\'javascript:window.close();\'>
				<span class="small">
				'. $_LANG->_( 'PROMPT_CLOSE' ) .'
				</span>
				</a>
				</div>'
				;

				return $output;
			}
		}
	}

	/**
	* ## This function has been depreciated in the conversion to pT,  it is no longer used by the core but is being kept for backward compatability of 3PD ##
	* Writes Back Button
	*/
	function BackButton ( &$params, $hide_js=NULL, $output=1 ) {
		global $_LANG;

		// Back Button
		if ( $params->get( 'back_button' ) && !$params->get( 'popup' ) && !$hide_js) {
			if ( $output ) {
				?>
				<div class="back_button">
				<a href='javascript:history.go(-1)'>
				<?php echo $_LANG->_( 'Back' ); ?>
				</a>
				</div>
				<?php
			} else {
				$output = '
				<div class="back_button">
				<a href="javascript:history.go(-1)">
				'. $_LANG->_( 'Back' ).'
				</a>
				</div>'
				;

				return $output;
			}
		}
	}

	/**
	* ## This function has been depreciated in the conversion to pT,  it is no longer used by the core but is being kept for backward compatability of 3PD ##
	* Writes Print icon
	*/
	function PrintIcon( &$row, &$params, $hide_js, $link, $status=NULL, $output=1 ) {
		global $mosConfig_live_site, $mosConfig_absolute_path, $cur_template, $Itemid, $_LANG;

		if ( $params->get( 'print' )  && !$hide_js ) {
			// use default settings if none declared
			if ( !$status ) {
				$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
			}

			// checks template image directory for image, if non found default are loaded
			if ( $params->get( 'icons' ) ) {
				$image = mosAdminMenus::ImageCheck( 'printButton.png', '/images/M_images/', NULL, NULL, $_LANG->_( 'Print' ) );
			} else {
				$image = $_LANG->_( 'ICON_SEP' ) .'&nbsp;'. $_LANG->_( 'Print' ). '&nbsp;'. $_LANG->_( 'ICON_SEP' );
			}

			if ( $output ) {
				if ( $params->get( 'popup' ) ) {
					// Print Button - used in pop-up window
					?>
					<td align="right" width="1%" class="buttonheading">
						<a href="#" onclick="javascript:window.print(); return false" style="font-weight: normal;" title="<?php echo $_LANG->_( 'Print' ); ?>">
							<?php echo $image;?>
						</a>
					</td>
					<?php
				} else {
					$onclick = "javascript:window.open( '$link', 'win1', '$status' );";
					// Print Preview button - used when viewing page
					?>
					<td align="right" width="1%" class="buttonheading">
						<a href="#" onclick="<?php echo $onclick;?>" style="font-weight: normal;" title="<?php echo $_LANG->_( 'Print' ); ?>">
							<?php echo $image;?>
						</a>
					</td>
					<?php
				}
			} else {
				if ( $params->get( 'popup' ) ) {
					// Print Button - used in pop-up window
					$output = '
					<td align="right" width="1%" class="buttonheading">
						<a href="#" onclick="javascript:window.print(); return false" style="font-weight: normal;" title="'. $_LANG->_( 'Print' ) .'">
							'. $image .'
						</a>
					</td>'
					;

					return $output;
				} else {
					$onclick = "javascript:window.open( '$link', 'win1', '$status' );";
					// Print Preview button - used when viewing page
					$output = '
					<td align="right" width="1%" class="buttonheading">
						<a href="#" onclick="'. $onclick .'" style="font-weight: normal;" title="'. $_LANG->_( 'Print' ) .'">
							'. $image .'
						</a>
					</td>'
					;

					return $output;
				}
			}
		}
	}

	/**
	* simple Javascript Cloaking
	* email cloacking
 	* by default replaces an email with a mailto link with email cloacked
	*/
	function emailCloaking( $mail, $mailto=1, $text='', $email=1, $noscript=0 ) {
		global $_LANG;

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
		$replacement 	.= "var addy". $rand ." = '". @$mail[0] ."' + '&#64;' + '". implode( "' + '&#46;' + '", $mail_parts ) ."'; \n";
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
		$replacement 	.= "</script> \n";
		if ( $noscript ) {
			$replacement 	.= "<noscript> \n";
			$replacement 	.= $_LANG->_( 'CLOAKING' );
			$replacement 	.= "\n</noscript> \n";
		}

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
* @package Mambo
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
			$this->_error = 'Your Category must contain a title.';
			return false;
		}
		if (trim( $this->name ) == '') {
			$this->_error = 'Your Category must have a name.';
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
			$this->_error = 'There is a category already with that name, please try again.';
			return false;
		}

		// remove <br /> take being automatically added to empty description field
	 	$length		= strlen( $this->description ) < 9;
	 	$search1 	= strstr( $this->description, '<br />');
	 	$search2 	= strstr( $this->description, '<br>');
	 	if ( $length && ( $search1 || $search2 ) ) {
	 		$this->description = NULL;
	 	}

		return true;
	}
}

/**
* Section database table class
* @package Mambo
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
			$this->_error = 'Your Section must contain a title.';

			return false;
		}
		if (trim( $this->name ) == '') {
			$this->_error = 'Your Section must have a name.';

			return false;
		}
		// check for existing name
		$query = "SELECT id"
		. "\n FROM #__sections"
		. "\n WHERE name = '$this->name'"
		. "\n AND scope = '$this->scope'"
		;
		$this->_db->setQuery( $query );

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = 'There is a section already with that name, please try again.';
			return false;
		}
		// remove <br /> take being automatically added to empty description
	 	$length		= strlen( $this->description ) < 9;
	 	$search1 	= strstr( $this->description, '<br />');
	 	$search2 	= strstr( $this->description, '<br>');
	 	if ( $length && ( $search1 || $search2 ) ) {
	 		$this->description = NULL;
	 	}

		return true;
	}
}

/**
* Module database table class
* @package Mambo
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

		// specific filters
		$iFilter = new InputFilter( null, null, 1, 1 );
		if ( !empty( $this->introtext ) ) {
			$this->introtext = trim( $iFilter->process( $this->introtext ) );
		}
		if ( !empty( $this->fulltext ) ) {
			$this->fulltext =  trim( $iFilter->process( $this->fulltext ) );
		}

		if (trim( str_replace( '&nbsp;', '', $this->fulltext ) ) == '') {
			$this->fulltext = '';
		}

	 	// remove <br /> take being automatically added to empty fulltext
	 	$length		= strlen( $this->fulltext ) < 8;
	 	$search1 	= strstr( $this->fulltext, '<br />');
	 	$search2 	= strstr( $this->fulltext, '<br>');
	 	if ( $length && ( $search1 || $search2 ) ) {
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

		if ( $mapKeysToText ) {
			$query = "SELECT name"
			. "\n FROM #__sections"
			. "\n WHERE id = $this->sectionid"
			;
			$database->setQuery( $query );
			$this->sectionid = $database->loadResult();

			$query = "SELECT name"
			. "\n FROM #__categories"
			. "\n WHERE id = $this->catid"
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
* @package Mambo
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
* Provides access to the mos_templates table
* @package Mambo
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
		global $mosConfig_uniquemail, $mosConfig_username_length, $mosConfig_password_length;
		global $_LANG;

		// filter malicious code
		$this->filter();

		// checks whether name is blank
		if ( trim( $this->name ) == '' ) {
			$this->_error = $_LANG->_( 'REGWARN_NAME' );
			return false;
		}

		// checks whether username is blank
		if ( trim( $this->username ) == '' ) {
			$this->_error = $_LANG->_( 'REGWARN_UNAME' );
			return false;
		}

		// checks whether username utilises valid characters
		if ( eregi( "[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", $this->username ) ) {
			$this->_error = sprintf( $_LANG->_('VALID_AZ09'), $_LANG->_( 'PROMPT_UNAME' ), ( $mosConfig_username_length - 1 ) );
			return false;
		}

		// checks whether username is proper length
		if ( strlen( $this->username ) < $mosConfig_username_length ) {
			$this->_error = sprintf( $_LANG->_('VALID_AZ09'), $_LANG->_( 'PROMPT_UNAME' ), ( $mosConfig_username_length - 1 ) );
			return false;
		}

		// checks whether password utilises valid characters
		if ( eregi( "[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", $this->password ) ) {
			$this->_error = sprintf( $_LANG->_('VALID_AZ09'), $_LANG->_( 'REGISTER_PASS' ), ( $mosConfig_password_length - 1 ) );
			return false;
		}

		// checks whether password is proper length
		if ( strlen( $this->password ) < $mosConfig_password_length ) {
			$this->_error = sprintf( $_LANG->_('VALID_AZ09'), $_LANG->_( 'REGISTER_PASS' ), ( $mosConfig_password_length - 1 ) );
			return false;
		}

		// checks whether an email addy has been entered
		//if ( ( trim( $this->email == '' )) || ( preg_match( "/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $this->email ) == false ) ) {
		if ( trim( $this->email == '' ) || ( strchr( $this->email, '@' ) == false ) ) {
			$this->_error = $_LANG->_( 'REGWARN_MAIL' );
			return false;
		}

		// check for existing email
		if ($mosConfig_uniquemail) {
			$query = "SELECT id FROM #__users "
			. "\n WHERE email = '$this->email'"
			. "\n AND id != '$this->id'"
			;
			$this->_db->setQuery( $query );

			$xid = intval( $this->_db->loadResult() );
			if ($xid && $xid != intval( $this->id )) {
				$this->_error = $_LANG->_( 'REGWARN_EMAIL_INUSE' );
				return false;
			}
		}

		// check for existing username
		$query = "SELECT id FROM #__users "
		. "\n WHERE LOWER( username ) = LOWER( '$this->username' )"
		. "\n AND id != '$this->id'"
		;
		$this->_db->setQuery( $query );
		$xid = intval( $this->_db->loadResult() );
		if ( $xid && $xid != intval( $this->id ) ) {
			$this->_error = $_LANG->_( 'REGWARN_INUSE' );
			return false;
		}

		return true;
	}

	/**
	 * Custom store method
	 */
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
			$object_id = $acl->get_object_id( $section_value, $this->$k, 'ARO' );

			$groups = $acl->get_object_groups( $object_id, 'ARO' );
			$acl->del_group_object( $groups[0], $section_value, $this->$k, 'ARO' );
			$acl->add_group_object( $this->gid, $section_value, $this->$k, 'ARO' );

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

	/**
	 * Custom delete method
	 */
	function delete( $oid=null ) {
		global $acl;

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		$aro_id = $acl->get_object_id( 'users', $this->$k, 'ARO' );

		$query = "DELETE FROM $this->_tbl"
		. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
		;
		$this->_db->setQuery( $query );

		if ($this->_db->query()) {
			// cleanup related data

			// :: private messaging
			$query = "DELETE FROM #__messages_cfg"
			. "\n WHERE user_id = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->_error = $this->_db->getErrorMsg();
				return false;
			}
			$query = "DELETE FROM #__messages"
			. "\n WHERE user_id_to = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->_error = $this->_db->getErrorMsg();
				return false;
			}
			// #__core_acl_aro
			$query = "DELETE FROM #__core_acl_aro"
			. "\n WHERE value = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->_error = $this->_db->getErrorMsg();
				return false;
			}
			// #__core_acl_groups_aro_map
			$query = "DELETE FROM #__core_acl_groups_aro_map"
			. "\n WHERE aro_id = '". $aro_id ."'"
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
	 * @param string The value for the group
	 * @param string The name for the group
	 * @param string If RECURSE, will drill into child groups
	 * @param string Ordering for the list
	 * @return array
	 */
	function getUserListFromGroup( $value, $name, $recurse='NO_RECURSE', $order='name' ) {
		global $acl;

		$group_id = $acl->get_group_id( $value, $name, $group_type = 'ARO');
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
				if (is_null( $safeHtmlFilter )) {
					$safeHtmlFilter = new InputFilter( null, null, 1, 1 );
				}
				$arr[$name] = $safeHtmlFilter->process( $arr[$name] );
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
* @param value the input string or array
*/
function mosStripslashes(&$value) {
	$ret = '';
	if (is_string($value)) {
		$ret = stripslashes($value);
	} else {
		if (is_array($value)) {
			$ret = array();
			while (list($key,$val) = each($value)) {
				$ret[$key] = mosStripslashes($val);
			} // while
		} else {
			$ret = $value;
		} // if
	} // if
	return $ret;
} // mosStripSlashes

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
					$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? mosStripslashes( $array[$ak] ) : $array[$ak];
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
	// specific filters
	$iFilter = new InputFilter();
	$url = $iFilter->process( $url );
	if ( !empty( $msg ) ) {
		$msg = $iFilter->process( $msg );
	}

	if ($iFilter->badAttributeValue( array( 'href', $url ))) {
		$url = $GLOBALS['mosConfig_live_site'];
	}

	if ( trim( $msg ) ) {
	 	if ( strpos( $url, '?' ) ) {
			$url .= '&mosmsg=' . urlencode( $msg );
		} else {
			$url .= '?mosmsg=' . urlencode( $msg );
		}
	}

	if ( headers_sent() ) {
		echo "<script>document.location.href='$url';</script>\n";
	} else {
		@ob_end_clean(); // clear output buffer
		header( "HTTP/1.1 301 Moved Permanently" );
		header( "Location: $url" );
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

/**
 * @param int The root id
 * @param string The indent string (usually empty)
 * @param array Just use array()
 * @param array An array of assembled child relations
 * @param int The maximum level
 * @param int Recursive level
 * @param int Type - 1 is a bit spacier
 */
function mosTreeRecurse( $id, $indent, $list, &$children, $maxlevel=9999, $level=0, $type=1 ) {
	if ( @$children[$id] && $level <= $maxlevel ) {
		foreach ($children[$id] as $v) {
			$id = $v->id;

			if ( $type ) {
				$pre 	= '<sup>L</sup>&nbsp;';
				$spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			} else {
				$pre 	= '- ';
				$spacer = '&nbsp;&nbsp;';
			}

			if ( $v->parent === 0 ) {
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
* @package Mambo
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

	/**
	 * Constructor
	 */
	function mosMambot( &$db ) {
		$this->mosDBTable( '#__mambots', 'id', $db );
	}

	function check( ) {
		global $mosConfig_absolute_path;
		global $_LANG;

		// check to see if file exists
		$path = $mosConfig_absolute_path .'/mambots/'. $this->folder .'/'. $this->element .'.php';
		if (!file_exists( $path )) {
			$this->_error = $_LANG->_( 'No file with that name exists, please double check the name of the file' );
			return false;
		}
		return true;
	}
}

/**
* Module database table class
* @package Mambo
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
			$this->_error = 'Your Module must contain a title.';
			return false;
		}

		return true;
	}
	/**
	 * @param int The client number
	 * @param boolean Add trailing slash to path
	 */
	function getBasePath( $client, $addTrailingSlash=true ) {
		global $mosConfig_absolute_path;

		switch ($client) {
			case '1':
				$dir =  '/administrator/modules';
				break;
			default:
				$dir = '/modules';
				break;
		}

		return mosFS::getNativePath( $mosConfig_absolute_path . $dir, $addTrailingSlash );
	}
}

/**
* Session database table class
* @package Mambo
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

/**
 * @param object
 * @return array
 */
function mosObjectToArray( $p_obj ) {
	$retarray = null;
	if (is_object( $p_obj )) {
		$retarray = array();

		foreach (get_object_vars( $p_obj ) as $k => $v) {
			if (is_object( $v )) {
				$retarray[$k] = mosObjectToArray( $v );
			} else {
				$retarray[$k] = $v;
			}
		}
	}
	return $retarray;
}

/**
 * @param string SQL with ordering As value and 'name field' AS text
 * @param integer The length of the truncated headline
 */
function mosGetOrderingList( $sql, $chop='30' ) {
	global $database, $_LANG;

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
function mosMakeHtmlSafe( &$source, $quote_style=ENT_QUOTES, $exclude_keys='' ) {
	if (is_array( $source )) {
		$process = &$source;
	} else {
		$process = array( &$source );
	}

	$n = count( $process );
	for ($i = 0; $i < $n; $i++) {
		$mixed =& $process[$i];
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
		} else if (is_string( $mixed )) {
			$mixed = htmlspecialchars( $mixed, $quote_style );
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

	$dblink = 'index.php?option='. $menu_option;
	if ( $Itemid != '' && $Itemid != 0 ) {
		$query = "SELECT access"
		. "\n FROM #__menu"
		. "\n WHERE id = '$Itemid'"
		;
		$database->setQuery( $query );
	} else {
		if ($task!='') {
			$dblink .= '&task='. $task;
		}
		$query = "SELECT access"
		. "\n FROM #__menu"
		. "\n WHERE link LIKE '$dblink%'"
		;
		$database->setQuery( $query );
	}
	$results 	= $database->loadObjectList();
	$access 	= 0;

	foreach ($results as $result) {
		$access = max( $access, $result->access );
	}

	return ( $access <= $gid );
}

/**
* Returns formated date according to current local and adds time offset
* @param string date in datetime format
* @param string format optional format for strftime
* @param offset time offset if different than global one
* @returns formated date
*/
function mosFormatDate( $date, $format='', $offset='' ){
	global $mosConfig_offset;

	if ( $format == '' ) {
		$format = $GLOBALS['_LANG']->_( 'DATE_FORMAT_LC' );
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
function mosCurrentDate( $format='' ) {
	global $mosConfig_offset;

	if ( $format == '' ) {
		$format = $GLOBALS['_LANG']->_( 'DATE_FORMAT_LC' );
	}
	$date = strftime( $format, time() + ( $mosConfig_offset * 60 * 60 ) );

	return $date;
}

/**
* Utility function to provide ToolTips
* @param string ToolTip text
* @param string Box title
* @returns HTML code for ToolTip
*/
function mosToolTip( $tooltip, $title='', $width='', $image='tooltip.png', $text='', $href='#', $position='BELOW, RIGHT' ) {
	global $mosConfig_live_site;

	$tooltip 	= addslashes( $tooltip );
	$title 		= addslashes( $title );
	$class		= 'class="tooltip"';

	if ( $width ) {
		$width = ', WIDTH, \''.$width .'\'';
	}
	if ( $title ) {
		$title = ', CAPTION, \''.$title .'\'';
	}
	if ( !$text ) {
		$image 	= $mosConfig_live_site . '/includes/js/ThemeOffice/'. $image;
		$text 	= '<img src="'. $image .'" border="0" alt=""/>';
	}

	$onclick 	= 'onClick="return false;"';
	if ( $href <> '#' && $href ) {
		$onclick 	= '';
		$class 		= 'class="tooltiplink"';
	}
	$tip	= '<a href="'. $href .'" '. $onclick .' onmouseover="return overlib(\'' . $tooltip . '\''. $title .', '. $position . $width .');" onmouseout="return nd();" '. $class .'>';
	$tip 	.= '<span class="editlinktip">';
	$tip 	.= $text;
	$tip	.= '</span>';
	$tip	.= '</a>';

	return $tip;
}

/**
* Utility function to provide Warning Icons
* @param string Warning text
* @param string Box title
* @returns HTML code for Warning
*/
function mosWarning($warning, $title='Joomla! Warning') {
	global $mosConfig_live_site, $_LANG;

	$title = $_LANG->_( 'Joomla! Warning' );
	$tip = "<a href=\"#\" onMouseOver=\"return overlib('" . $warning . "', CAPTION, '$title', BELOW, RIGHT);\" onmouseout=\"return nd();\"><img src=\"" . $mosConfig_live_site . "/includes/js/ThemeOffice/warning.png\" border=\"0\" /></a>";

	return $tip;
}

function mosCreateGUID(){
	srand((double)microtime()*1000000);
	$r = rand();
	$u = uniqid(getmypid() . $r . (double)microtime()*1000000,1);
	$m = md5 ($u);

	return $m;
}

function mosRandomNumber($max){
	srand((double)microtime()*1000000);

	return rand( 0, $max );
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
	global $mosConfig_absolute_path, $mosConfig_sendmail, $_LANG;
	global $mosConfig_smtpauth, $mosConfig_smtpuser;
	global $mosConfig_smtppass, $mosConfig_smtphost;
	global $mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_mailer;

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
function mosMail( $from, $fromname, $recipient, $subject, $body, $mode=0, $cc=NULL, $bcc=NULL, $attachment=NULL ) {
	global $mosConfig_debug;

	$mail = mosCreateMail( $from, $fromname, $subject, $body );

	// activate HTML formatted emails
	if ( $mode ) {
		$mail->IsHTML( true );
	}

	if ( isset( $recipient ) ) {
		if ( is_array( $recipient ) ) {
			foreach ( $recipient as $to ) {
				$mail->AddAddress( $to );
			}
		} else {
			$mail->AddAddress( $recipient );
		}
	}
	if ( isset ($cc ) ) {
		if ( is_array( $cc ) ) {
			foreach ( $cc as $to ) {
				$mail->AddCC( $to );
			}
		} else {
			$mail->AddCC( $cc );
		}
	}
	if ( isset( $bcc ) ) {
		if ( is_array( $bcc ) ) {
			foreach ( $bcc as $to ) {
				$mail->AddBCC( $to );
			}
		} else {
			$mail->AddBCC( $bcc );
		}
	}
	if ( $attachment ) {
		if ( is_array( $attachment ) ) {
			foreach ( $attachment as $fname ) {
				$mail->AddAttachment( $fname );
			}
		} else {
			$mail->AddAttachment( $attachment );
		}
	}
	$mailssend = $mail->Send();

	if ( $mosConfig_debug ) {
		//$mosDebug->message( "Mails send: $mailssend");
	}
	if ( $mail->error_count > 0 ) {
		//$mosDebug->message( "The mail message $fromname <$from> about $subject to $recipient <b>failed</b><br /><pre>$body</pre>", false );
		//$mosDebug->message( "Mailer Error: " . $mail->ErrorInfo . "" );
	}

	return $mailssend;
}

/**
* Initialise GZIP
*/
function initGzip() {
	global $mosConfig_gzip, $do_gzip_compress;

	$do_gzip_compress = FALSE;

	if ( $mosConfig_gzip == 1 ) {
		$phpver 	= phpversion();
		$useragent 	= mosGetParam( $_SERVER, 'HTTP_USER_AGENT', '' );
		$canZip 	= mosGetParam( $_SERVER, 'HTTP_ACCEPT_ENCODING', '' );

		if ( $phpver >= '4.0.4pl1' && ( strpos( $useragent, 'compatible' ) !== false || strpos( $useragent,'Gecko') !== false ) ) {
			if ( extension_loaded('zlib') ) {
				ob_start( 'ob_gzhandler' );
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

		$gzip_size 	= strlen( $gzip_contents );
		$gzip_crc 	= crc32( $gzip_contents );

		$gzip_contents = gzcompress( $gzip_contents, 9 );
		$gzip_contents = substr( $gzip_contents, 0, strlen( $gzip_contents ) - 4 );

		echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
		echo $gzip_contents;
		echo pack( 'V', $gzip_crc );
		echo pack( 'V', $gzip_size );
	} else {
		ob_end_flush();
	}
}

/**
* Random password generator
* @return password
*/
function mosMakePassword() {
	$salt 		= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$len 		= strlen($salt);
	$makepass	= '';
	mt_srand(10000000*(double)microtime());
	for ( $i = 0; $i < 8; $i++ ) {
		$makepass .= $salt[mt_rand(0,$len - 1)];
	}

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
* @package Mambo
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

		if (isset( $my )) {
			$gid = $my->gid;
		} else {
			$gid = 0;
		}

		$group = trim( $group );
		$query = "SELECT folder, element, published, params"
		. "\n FROM #__mambots"
		. "\n WHERE published >= 1"
		. "\n AND access <= $gid"
		. "\n AND folder = '$group'"
		. "\n ORDER BY ordering"
		;
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
	/** returns a copy of a named bot
	* @param string	name of the bot
	* @return mambot copy of the bot or null
	*/
	function getBot( $name ) {
		$retBot = null;
		if( $this->_bots ) {
			foreach ($this->_bots as $bot) {
				if ( $bot->element == $name ) {
					$retBot = $bot;
					break;
				}
			}
		}
		return $retBot;
	}
}

/**
* Tab Creation handler
* @package Mambo
*/
class mosTabs {
	/** @var int Use cookies */
	var $useCookies = 0;

	/**
	* Constructor
	* Includes files needed for displaying tabs and sets cookie options
	* @param int useCookies, if set to 1 cookie will hold last used tab between page refreshes
	*/

	function mosTabs( $useCookies, $link=1 ) {
		global $mainframe;
		global $mosConfig_live_site;
		global $_LANG;

		echo '<script type="text/javascript" src="'. $mosConfig_live_site . '/includes/js/tabs/tabpane.js"></script>';
		$this->useCookies = $useCookies;

		$tpath = mosFS::getNativePath( $mainframe->getTemplatePath() . 'images/tabs' );
		if (is_dir( $tpath )) {
			$turl = $mainframe->getTemplateURL() . '/images/tabs/';
		} else {
			$turl = $mosConfig_live_site . '/includes/js/tabs/';
		}

		if ( $link ) {
			?>
			<link rel="stylesheet" href="<?php echo $mosConfig_live_site; ?>/includes/js/tabs/tabpane<?php echo ($_LANG->rtl() ? '_rtl': ''); ?>.css" type="text/css" id="luna-tab-style-sheet" />
			<?php
		}
		?>
		<style type="text/css">
		.dynamic-tab-pane-control .tab-row .tab {
			background-image: url(<?php echo $turl;?>tab.png);
		}
		.dynamic-tab-pane-control .tab-row .tab.selected {
			background-image:	url(<?php echo $turl;?>tab_active.png) !important;
		}
		.dynamic-tab-pane-control .tab-row .tab.hover {
			background-image:	url(<?php echo $turl;?>tab_hover.png);
		</style>
		<?php
	}

	/**
	* creates a tab pane and creates JS obj
	* @param string The Tab Pane Name
	*/
	function startPane($id){
		echo '<div class="tab-page" id="'. $id .'">';
		echo "<script type=\"text/javascript\">\n";
		echo "   var tabPane1 = new WebFXTabPane( document.getElementById( \"".$id."\" ), ".$this->useCookies." )\n";
		echo "</script>\n";
	}

	/**
	* Ends Tab Pane
	*/
	function endPane() {
		echo '</div>';
	}

	/*
	* Creates a tab with title text and starts that tabs page
	* @param tabText - This is what is displayed on the tab
	* @param paneid - This is the parent pane to build this tab on
	*/
	function startTab( $tabText, $paneid ) {
		echo '<div class="tab-page" id="'. $paneid .'">';
		echo '<h2 class="tab">'. $tabText .'</h2>';
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
* @package Mambo
*/
class mosAdminMenus {
	/**
	 * build the select list for access level
	 * @param object
	 * @param string Alternative name for the control
	 * @return string The HTML list element
	 */
	function Access( &$row, $ctrlName='access' ) {
		global $database;

		$query = "SELECT id AS value, name AS text"
		. "\n FROM #__groups"
		. "\n ORDER BY id"
		;
		$database->setQuery( $query );
		$groups = $database->loadObjectList();
		$access = mosHTML::selectList( $groups, $ctrlName, 'class="inputbox" size="3"', 'value', 'text', intval( $row->access ) );
		return $access;
	}

	/**
	 * build a radio button option for published state
	 * @param object
	 * @param string Alternative name for the control
	 * @return string The HTML
	 */
	function Published( &$row, $ctrlName='published' ) {
		$published = mosHTML::yesnoRadioList( $ctrlName, 'class="inputbox"', $row->published );
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
	 * @param object
	 * @param string Alternative name for the control
	 * @return string The HTML list element
	 */
	function Target( &$row, $ctrlName='browserNav' ) {
		global $_LANG;

		$click[] = mosHTML::makeOption( '0', $_LANG->_( 'Parent Window With Browser Navigation' ) );
		$click[] = mosHTML::makeOption( '1', $_LANG->_( 'New Window With Browser Navigation' ) );
		$click[] = mosHTML::makeOption( '2', $_LANG->_( 'New Window Without Browser Navigation' ) );
		$target = mosHTML::selectList( $click, $ctrlName, 'class="inputbox" size="4"', 'value', 'text', intval( $row->browserNav ) );

		return $target;
	}

	/**
	* build the select list to choose an image
	*/
	function Images( $name, &$active, $javascript=NULL, $directory=NULL ) {
		global $mosConfig_absolute_path, $_LANG;

		if ( !$javascript ) {
			$javascript = "onchange=\"javascript:if (document.forms[0].image.options[selectedIndex].value!='') {document.imagelib.src='../images/stories/' + document.forms[0].image.options[selectedIndex].value} else {document.imagelib.src='../images/blank.png'}\"";
		}
		if ( !$directory ) {
			$directory = '/images/stories';
		}

		$imageFiles = mosReadDirectory( $mosConfig_absolute_path . $directory );
		$images = array(  mosHTML::makeOption( '', '- '. $_LANG->_( 'Select Image' ) .' -' ) );
		foreach ( $imageFiles as $file ) {
			if ( eregi( "\.(bmp|gif|jpg|png)$", $file ) ) {
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
		global $database, $_LANG;

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

			if ( is_dir( $i_f ) && $file <> 'CVS' ) {
				$folders[] = mosHTML::makeOption( $ff_ );
				mosAdminMenus::ReadImages( $i_f, $ff_, $folders, $images );
			} else if ( eregi( "\.(bmp|gif|jpg|png)$", $file ) && is_file( $i_f ) ) {
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

		if ( $name == 'image' && $alt ) {
			$name = $alt;
		}

		if ( $param ) {
			$image = $mosConfig_live_site. $param_directory . $param;
			if ( $type ) {
				$image = '<img src="'. $image .'" align="'. $align .'" alt="'. $alt .'" title="'. $alt .'" name="'. $name .'" border="0" />';
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
				$image = '<img src="'. $image .'" alt="'. $alt .'" title="'. $alt .'" align="'. $align .'" name="'. $name .'" border="0" />';
			}
		}

		return $image;
	}

	/** @deprecated Use mosContentFactory::buildCategoryLinks instead */
	function Category( &$menu, $id, $javascript='' ) {
		mosFS::load( '@class', 'com_content' );
		return mosContentFactory::buildCategoryLinks( $menu, $id, $javascript );
	}

	/** @deprecated Use mosAdminHTML::imageCheck instead */
	function ImageCheckAdmin( $file, $directory='/administrator/images/', $param=NULL, $param_directory='/administrator/images/', $alt=NULL, $name=NULL, $type=1, $align='middle' ) {
		mosFS::load( '/administrator/includes/admin.php' );
		mosAdminHTML::imageCheck( $file, $directory, $param, $param_directory, $alt, $name, $type, $align );
	}

	/** @deprecated Use mosMenuFactory::buildOrderingList instead */
	function Ordering( &$row, $id ) {
		mosFS::load( '@class', 'com_menus' );
		return mosMenuFactory::buildOrderingList( $row, $id );
	}

	/** @deprecated Use mosMenuFactory::buildParentList instead */
	function Parent( &$row, $ctrlName='parent' ) {
		mosFS::load( '@class', 'com_menus' );
		return mosMenuFactory::buildParentList( $row, $ctrlName );
	}

	/** @deprecated Use mosContentFactory::buildSectionLinks instead */
	function Section( &$menu, $id, $all=0 ) {
		mosFS::load( '@class', 'com_content' );
		return mosContentFactory::buildSectionLinks( $menu, $id, $all );
	}

	/** @deprecated Use mosContentFactory::buildSelectSection instead */
	function SelectSection( $name, $active=NULL, $javascript=NULL, $order='ordering' ) {
		mosFS::load( '@class', 'com_content' );
		return mosContentFactory::buildSelectSection( $name, $active, $javascript, $order );
	}

}

/**
 * @package Mambo
 */
class mosCommonHTML {

	/**
	 * Loads all necessary files for JS Overlib tooltips
	 */
	function loadOverlib() {
		global  $mosConfig_live_site;
		?>
		<script language="javascript" type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_mini.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_hideform_mini.js"></script>
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
		<?php
	}

	/**
	 * Loads all necessary files for JS Calendar
	 */
	function loadCalendar() {
		global  $mosConfig_live_site;
		?>
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo $mosConfig_live_site;?>/includes/js/calendar/calendar-mos.css" title="green" />
		<!-- import the calendar script -->
		<script type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/calendar/calendar.js"></script>
		<!-- import the language module -->
		<script type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/calendar/lang/calendar-en.js"></script>
		<?php
	}

	function tOrder_img( &$lists, $current ) {
		global $mosConfig_live_site;

		if ( $current == $lists['tOrder'] ) {
			if ( $lists['tOrderDir'] == 'ASC' ) {
				$lists['tOrder_img'] 	= '&nbsp;&nbsp;<img src="'. $mosConfig_live_site .'/images/M_images/sort_desc.png" border="0" />';
			} else {
				$lists['tOrder_img'] 	= '&nbsp;&nbsp;<img src="'. $mosConfig_live_site .'/images/M_images/sort_asc.png" border="0" />';
			}

			return $lists['tOrder_img'];
		}
	}

	/**
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	function tOrder( &$lists, $text, $order, $js='', $title='' ) {
		global $_LANG;

		if ( !$title ) {
			$title = $_LANG->_( 'Order by' ) . ' ' .$text;
		}
		if ( $js ) {
			$link = "javascript:$js('$order','". $lists['tOrderDir'] ."')";
		} else {
			$link = "javascript:tableOrdering('$order','". $lists['tOrderDir'] ."')";
		}
		$html = '<a href="'.$link.'" title="'.$title.'" class="col_ordering">';
		$html .= $text;
		$html .= mosCommonHTML::tOrder_img( $lists, $order );
		$html .= '</a>';

		return $html;
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
	global $mosConfig_mailfrom, $mosConfig_fromname;
	global $_LANG;

	$subject = $_LANG->_( 'MAIL_SUB' )." '$type'";
	$message = $_LANG->_( 'MAIL_MSG' );
	eval ("\$message = \"$message\";");
	mosMail( $mosConfig_mailfrom, $mosConfig_fromname, $adminEmail, $subject, $message);
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
	$text = str_replace( '&amp;', '&', $text );
	$text = preg_replace( '|&(?![\w]+;)|', '&amp;', $text );
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
 * Function to convert array to string values
 */
function mosArrayToStr( &$array, $default=null ) {
	if (is_array( $array )) {
		$n = count( $array );
		for ($i = 0; $i < $n; $i++) {
			$array[$i] = strval( $array[$i] );
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
 * Format a backtrace error
 */
function mosBackTrace() {
	if (function_exists( 'debug_backtrace' )) {
		echo '<div align="left">';
		foreach( debug_backtrace() as $back) {
			if (@$back['file']) {
				echo '<br />' . str_replace( MOSFS_ROOT, '', $back['file'] ) . ':' . $back['line'];
			}
		}
		echo '</div>';
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
	 * @param string The text for the No button
	 * @param string The text for the Yes button
	 */
	function yesNoRadio( &$tmpl, $template, $name, $value, $varname=null, $noText='No', $yesText='Yes' ) {
		global $_LANG;
		$a = array(
			patHTML::makeOption( 0, $_LANG->_( $noText ) ),
			patHTML::makeOption( 1, $_LANG->_( $yesText ) )
		);
		patHTML::radioSet( $tmpl, $template, $name, $value, $a, $varname );
	}
}

// ----- NO MORE CLASSES OR FUNCTIONS PASSED THIS POINT -----
// Post class declaration initialisations
// some version of PHP don't allow the instantiation of classes
// before they are defined

/** @global mosPlugin $_MAMBOTS */
$_MAMBOTS = new mosMambotHandler();
?>