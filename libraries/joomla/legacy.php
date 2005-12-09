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

/* _ISO defined not used anymore. All output is forced as utf-8 */
DEFINE('_ISO','charset=utf-8');

/**
* Legacy function, use JPath::clean instead
* @deprecated As of version 1.1
*/
function mosPathName($p_path, $p_addtrailingslash = true) {
	return JPath::clean( $p_path, $p_addtrailingslash );
}

/**
* Legacy function, use JFolder::files or JFolder::folders instead
* @deprecated As of version 1.1
*/
function mosReadDirectory( $path, $filter='.', $recurse=false, $fullpath=false  ) {
	$arr = array();
	if (!@is_dir( $path )) {
		return $arr;
	}
	$handle = opendir( $path );

	while ($file = readdir($handle)) {
		$dir = mosPathName( $path.'/'.$file, false );
		$isDir = is_dir( $dir );
		if (($file <> ".") && ($file <> "..")) {
			if (preg_match( "/$filter/", $file )) {
				if ($fullpath) {
					$arr[] = trim( mosPathName( $path.'/'.$file, false ) );
				} else {
					$arr[] = trim( $file );
				}
			}
			if ($recurse && $isDir) {
				$arr2 = mosReadDirectory( $dir, $filter, $recurse, $fullpath );
				$arr = array_merge( $arr, $arr2 );
			}
		}
	}
	closedir($handle);
	asort($arr);
	return $arr;
}

/**
* Legacy function, use JFolder::create
* @deprecated As of version 1.1
*/
function mosMakePath($base, $path='', $mode = NULL)
{
	global $mosConfig_dirperms;

	// convert windows paths
	$path = str_replace( '\\', '/', $path );
	$path = str_replace( '//', '/', $path );

	// check if dir exists
	if (file_exists( $base . $path )) return true;

	// set mode
	$origmask = NULL;
	if (isset($mode)) {
		$origmask = @umask(0);
	} else {
		if ($mosConfig_dirperms=='') {
			// rely on umask
			$mode = 0777;
		} else {
			$origmask = @umask(0);
			$mode = octdec($mosConfig_dirperms);
		} // if
	} // if

	$parts = explode( '/', $path );
	$n = count( $parts );
	$ret = true;
	if ($n < 1) {
		$ret = @mkdir($base, $mode);
	} else {
		$path = $base;
		for ($i = 0; $i < $n; $i++) {
			$path .= $parts[$i] . '/';
			if (!file_exists( $path )) {
				if (!@mkdir( $path, $mode )) {
					$ret = false;
					break;
				}
			}
		}
	}
	if (isset($origmask)) @umask($origmask);
	return $ret;
}

/**
 * Legacy function, use JPath::setPermissions instead
 * @deprecated As of version 1.1
 */
function mosChmod( $path ) {
	return JPath::setPermissions( $path );
}

/**
 * Legacy function, use JPath::setPermissions instead
 * @deprecated As of version 1.1
 */
function mosChmodRecursive( $path, $filemode=NULL, $dirmode=NULL ) {
	return JPath::setPermissions( $path, $filemode, $dirmode );
}

/**
* Legacy function, use JPath::canCHMOD
* @deprecated As of version 1.1
*/
function mosIsChmodable( $file ) {
	return JPath::canCHMOD( $file );
}

/**
* Legacy function, replaced by geshi bot
* @deprecated As of version 1.1
*/
function mosShowSource( $filename, $withLineNums=false ) {

	ini_set('highlight.html', '000000');
	ini_set('highlight.default', '#800000');
	ini_set('highlight.keyword','#0000ff');
	ini_set('highlight.string', '#ff00ff');
	ini_set('highlight.comment','#008000');

	if (!($source = @highlight_file( $filename, true ))) {
		return JText::_( 'Operation Failed' );
	}
	$source = explode("<br />", $source);

	$ln = 1;

	$txt = '';
	foreach( $source as $line ) {
		$txt .= "<code>";
		if ($withLineNums) {
			$txt .= "<font color=\"#aaaaaa\">";
			$txt .= str_replace( ' ', '&nbsp;', sprintf( "%4d:", $ln ) );
			$txt .= "</font>";
		}
		$txt .= "$line<br /><code>";
		$ln++;
	}
	return $txt;
}

/**
* Legacy function, use mosLoadModules('pathway'); instead
* @deprecated As of version 1.1
*/
function mosPathWay() {
	mosLoadModules('pathway', -1);
}


/**
* Legacy class, derive from JApplication instead
* @deprecated As of version 1.1
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
}

/**
* Legacy class, derive from JModel instead
* @deprecated As of version 1.1
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
* Legacy class, derive from JModel instead
* @deprecated As of version 1.1
*/
jimport( 'joomla.database.database' );
jimport( 'joomla.database.drivers.mysql' );
/**
 * @package Joomla
 * @deprecated As of version 1.1
 */
class database extends JDatabaseMySQL {
	function __construct ($host='localhost', $user, $pass, $db='', $table_prefix='', $offline = true) {
		parent::__construct( $host, $user, $pass, $db, $table_prefix );
	}
}

/**
* Legacy class, use JFactory::getCache instead
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
* @deprecated As of version 1.1
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
 * Legacy class, will be replaced by full MVC implementation in 1.2
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
 * Legacy class, will be replaced by full MVC implementation in 1.2
 * @deprecated As of version 1.1
 */
class mosMambotHandler extends JEventHandler {
	function __construct() {
		parent::__construct();
	}

	/**
	* Loads all the bot files for a particular group
	* @param string The group name, relates to the sub-directory in the mambots directory
	*/
	function loadBotGroup( $group ) {
		return JBotLoader::importGroup($group);
	}
	/**
	 * Loads the bot file
	 * @param string The folder (group)
	 * @param string The elements (name of file without extension)
	 * @param int Published state
	 * @param string The params for the bot
	 */
	function loadBot( $folder, $element, $published, $params='' ) {
		return JBotLoader::import($folder, $element, $published, $params='' );
	}
}

/**
 * Legacy class, removed
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
 *  use JBotLoader::importBot and JBotLoader::import to load bot code
 *  @deprecated As of version 1.1
 */
$_MAMBOTS = new mosMambotHandler();

/**
* Legacy function, use JApplication::getBrowser() instead
* @deprecated As of version 1.1
*/
function mosGetBrowser( $agent ) {
	$browser = JApplication::getBrowser();
	return $browser->getBrowser();
}

/**
* Legacy function, use JApplication::getBrowser() instead
* @deprecated As of version 1.1
*/
function mosGetOS( $agent ) {
	$browser = JApplication::getBrowser();
	return $browser->getPlatform();
}

/**
* Legacy function, use mosParameters::parse()
* @deprecated As of version 1.1
*/
function mosParseParams( $txt ) {
	return mosParameters::parse( $txt );
}

/**
* Legacy function, use $_VERSION->getLongVersion() instead
* @deprecated As of version 1.1
*/
global $_VERSION;
$version = $_VERSION->PRODUCT .' '. $_VERSION->RELEASE .'.'. $_VERSION->DEV_LEVEL .' '
. $_VERSION->DEV_STATUS
.' [ '.$_VERSION->CODENAME .' ] '. $_VERSION->RELDATE .' '
. $_VERSION->RELTIME .' '. $_VERSION->RELTZ;


/**
* Load the site language file (the old way - to be deprecated)
* @deprecated As of version 1.1
*/
global $mosConfig_lang;
$file = JPATH_SITE .'/language/' . $mosConfig_lang .'.php';
if (file_exists( $file )) {
	require_once( $file);
} else {
	$file = JPATH_SITE .'/language/english.php';
	if (file_exists( $file )) {
		require_once( $file );
	}
}

?>