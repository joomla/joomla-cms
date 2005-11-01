<?php

/**
* @version $Id: joomla.legacy.php 712 2005-10-28 05:18:19Z pasamio $
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
*/
function mosPathName($p_path, $p_addtrailingslash = true) {
	return mosFS::getNativePath( $p_path, $p_addtrailingslash );
}

/**
* Legacy function, use mosFS::listFiles or mosFS::listFolders instead
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
 */
function mosChmod( $path ) {
	return mosFS::CHMOD( $path );
}

/**
 * Legacy function, use mosFS::CHMOD instead
 */
function mosChmodRecursive( $path, $filemode=NULL, $dirmode=NULL ) {
	return mosFS::CHMOD( $path, $filemode, $dirmode );
}

/**
* Legacy function, use mosFS::canCHMOD
*/
function mosIsChmodable( $file ) {
	return mosFS::canCHMOD( $file );
}


/**
* Legacy function, use $_VERSION->getLongVersion() instead
*/
global $_VERSION;
$version = $_VERSION->PRODUCT .' '. $_VERSION->RELEASE .'.'. $_VERSION->DEV_LEVEL .' '
. $_VERSION->DEV_STATUS
.' [ '.$_VERSION->CODENAME .' ] '. $_VERSION->RELDATE .' '
. $_VERSION->RELTIME .' '. $_VERSION->RELTZ;
?>
