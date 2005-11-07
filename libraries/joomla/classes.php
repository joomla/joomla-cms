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
 * Page generation time
 * @package Joomla
 * @since 1.0
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

/**
 * @package Joomla
 * @abstract
 * @since 1.0
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
		echo $GLOBALS['_LANG']->_( 'ALERTNOTAUTH' );

		return null;
	}
}

/**
* Class to support function caching
* @package Joomla
* @since 1.0
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
* @since 1.0
*/
class mosMainFrame extends JObject {
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
	var $_client 			= null;

	/**
	* Class constructor
	* @param database A database connection object
	* @param string The url option [DEPRECATED]
	* @param string The path of the mos directory [DEPRECATED]
	*/
	function __construct( &$db, $option, $basePath=null, $client=0 ) {
		
		$this->_db =& $db;

		if (!isset( $_SESSION['session_userstate'] )) {
			$_SESSION['session_userstate'] = array();
		}
		$this->_userstate =& $_SESSION['session_userstate'];

		$this->_head 			= array();
		$this->_head['title'] 	= $GLOBALS['mosConfig_sitename'];
		$this->_head['meta'] 	= array();
		$this->_head['custom'] 	= array();
		$this->_client 		    = $client;
		
		$this->_setTemplate( );
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
		global $database, $acl, $_MAMBOTS, $_LANG;

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

			foreach($results as $result) {
				if ($result > 0) {

					$user = new mosUser( $database );
					$user->load( intval( $result ) );

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
	
					mosCache::cleanCache();
					return true;
				} 
			}
			return false;
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

		$user = new mosUser( $this->_db);

		if (intval( $this->_session->userid )) {
			$user->load($this->_session->userid);
			$user->params = new mosParameters($user->params);
		} 

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

	function _setTemplate( ) {
		global $Itemid,$mosConfig_live_site;
		$mosConfig_absolute_path = $this->getCfg( 'absolute_path' );

		if ($this->isAdmin()) {
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
			
			$this->_templatePath 	= mosFS::getNativePath( $mosConfig_absolute_path . '/administrator/templates/' . $cur_template );
			$this->_templateURL 	= $mosConfig_live_site . '/administrator/templates/' . $cur_template;
			
		} else {
			$assigned = ( !empty( $Itemid ) ? " OR menuid = $Itemid" : '' );
			
			$query = "SELECT template"
			. "\n FROM #__templates_menu"
			. "\n WHERE client_id = 0"
			. "\n AND ( menuid = 0 $assigned )"
			. "\n ORDER BY menuid DESC"
			. "\n LIMIT 1"
			;
			$this->_db->setQuery( $query );
			$cur_template = $this->_db->loadResult();
			
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
	 * @return string
	 * @since 1.1
	 */
	function getTemplatePath() {
		return $this->_templatePath;
	}

	/**
	 * Get the path to the current template
	 * @return string
	 * @since 1.1
	 */
	function getTemplateURL() {
		return $this->_templateURL;
	}

	/**
	 * Gets the client id
	 * @param mixed A client identifier
	 * @since 1.1
	 */
	function getClient( ) {
		return $this->_client;
	}

	/**
	 * Tries to find a file in the administrator or site areas
	 * @param string A file name
	 * @param int 0 to check site, 1 to check site and admin only, -1 to check admin only
	 * @since 1.1 
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
	* @return string
	*
	*/
	function getPath( $varname, $user_option=null ) {
		// check needed for handling of custom/new module xml file loading
		$check = ( ( $varname == 'mod0_xml' ) || ( $varname == 'mod1_xml' ) );
		if ( !$user_option && !$check ) {
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
	 * Gets the base path for the client
	 * @param mixed A client identifier
	 * @param boolean True (default) to add traling slash
	 */
	function getBasePath( $addTrailingSlash=true, $client = null ) {
		global $mosConfig_absolute_path;

		$client = is_null($client) ? $this->_client : $client;
		  
		switch ($client) {
			
			case '2':
				return mosFS::getNativePath( $mosConfig_absolute_path . '/installation', $addTrailingSlash );
				break;

			case '1':
				return mosFS::getNativePath( $mosConfig_absolute_path . '/administrator', $addTrailingSlash );
				break;
				
			case '0':
			default:
				return mosFS::getNativePath( $mosConfig_absolute_path, $addTrailingSlash );
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
				if ( isset($_SERVER['HTTP_USER_AGENT']) ) {
					$agent = $_SERVER['HTTP_USER_AGENT'];
				} else {
					$agent = 'Unknown';
				}
				
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

	/** Is admin interface?
	 * @return boolean
	 * @since 1.0.2
	 */
	function isAdmin() {
		return ($this->_client == 1) ?  true : false;
	}
	
	/** Is site interface?
	 * @return boolean
	 * @since 1.1
	 */
	function isSite() {
		return ($this->_client == 0) ?  true : false;
	}
	
	/** Is admin interface?
	 * @return boolean
	 * @since 1.1
	 */
	function isInstall() {
		return ($this->_client == 2) ?  true : false;
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
