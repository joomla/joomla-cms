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
* Legacy function, use mosFS::getNativePath instead
* @deprecated As of version 1.1
*/
function mosPathName($p_path, $p_addtrailingslash = true) {
	return mosFS::getNativePath( $p_path, $p_addtrailingslash );
}

/**
* Legacy function, use mosFS::listFiles or mosFS::listFolders instead
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
 * Legacy function, use mosFS::CHMOD instead
 * @deprecated As of version 1.1
 */
function mosChmod( $path ) {
	return mosFS::CHMOD( $path );
}

/**
 * Legacy function, use mosFS::CHMOD instead
 * @deprecated As of version 1.1
 */
function mosChmodRecursive( $path, $filemode=NULL, $dirmode=NULL ) {
	return mosFS::CHMOD( $path, $filemode, $dirmode );
}

/**
* Legacy function, use mosFS::canCHMOD
* @deprecated As of version 1.1
*/
function mosIsChmodable( $file ) {
	return mosFS::canCHMOD( $file );
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
		parent::__construct( $db, $client );
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