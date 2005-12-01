<?php
/**
* @version $Id: index.php 1244 2005-11-29 02:39:31Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define( '_VALID_MOS', 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once ( 'includes/defines.php' );
require_once ( 'includes/joomla.php' );

// create the mainframe object
$mainframe =& new JSite();

//get the database object (for backwards compatibility)
$database =& $mainframe->getDBO();

// load system bot group
JBotLoader::importGroup( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

//get the acl object (for backwards compatibility)
$acl =& JFactory::getACL();

// create the session
$mainframe->_createSession( $mainframe->getCfg('live_site').$mainframe->_client );

// retrieve some expected url (or form) arguments
$option = trim( strtolower( mosGetParam( $_REQUEST, 'option' ) ) );

// frontend login & logout controls
$return = mosGetParam( $_REQUEST, 'return', NULL );
if ($option == 'login') {
	if (!$mainframe->login()) {
		$mainframe->logout();
		mosErrorAlert( JText::_( 'LOGIN_INCORRECT' ) );
	}

	if ($return) {
		mosRedirect( $return );
	} else {
		mosRedirect( 'index.php' );
	}
} 

if ($option == 'logout') {
	$mainframe->logout();

	if ($return) {
		mosRedirect( $return );
	} else {
		mosRedirect( 'index.php' );
	}
}

// get the information about the current user from the sessions table
$my = $mainframe->getUser();

// displays offline page
if ($mainframe->getCfg('offline')) {
	// if superadministrator, administrator or manager show offline message bar + site
	if ( $my->gid < '23') {
		header(' Content-Type: text/htm; charset=UTF-8');
		require_once( 'templates/_system/offline.php' );
		exit();
	}
}

$Itemid = intval( mosGetParam( $_REQUEST, 'Itemid', null ) );

if ($option == '') {
	if ($Itemid) {
		$query = "SELECT id, link"
		. "\n FROM #__menu"
		. "\n WHERE menutype = 'mainmenu'"
		. "\n AND id = '$Itemid'"
		. "\n AND published = '1'"
		;
		$database->setQuery( $query );
	} else {
		$query = "SELECT id, link"
		. "\n FROM #__menu"
		. "\n WHERE menutype = 'mainmenu'"
		. "\n AND published = 1"
		. "\n ORDER BY parent, ordering LIMIT 1"
		;
		$database->setQuery( $query );
	}
	$menu = new mosMenu( $database );
	if ($database->loadObject( $menu )) {
		$Itemid = $menu->id;
	}
	$link = $menu->link;
	if (($pos = strpos( $link, '?' )) !== false) {
		$link = substr( $link, $pos+1 ). '&Itemid='.$Itemid;
	}
	parse_str( $link, $temp );
	/** this is a patch, need to rework when globals are handled better */
	foreach ($temp as $k=>$v) {
		$GLOBALS[$k] = $v;
		$_REQUEST[$k] = $v;
		if ($k == 'option') {
			$option = $v;
		}
	}
}
if ( !$Itemid ) {
// when no Itemid give a default value
	$Itemid = 99999999;
}

// trigger the onAfterStart events
$mainframe->triggerEvent( 'onAfterStart' );

// checking if we can find the Itemid thru the content
if ( $option == 'com_content' && $Itemid === 0 ) {
	$id = intval( mosGetParam( $_REQUEST, 'id', 0 ) );
	$Itemid = JApplicationHelper::getItemid( $id );
}

/** do we have a valid Itemid yet?? */
if ( $Itemid === 0 ) {
	/** Nope, just use the homepage then. */
	$query = "SELECT id"
	. "\n FROM #__menu"
	. "\n WHERE menutype = 'mainmenu'"
	. "\n AND published = 1"
	. "\n ORDER BY parent, ordering"
	. "\n LIMIT 1"
	;
	$database->setQuery( $query );
	$Itemid = $database->loadResult();
}

// patch to lessen the impact on templates
if ($option == 'search') {
	$option = 'com_search';
}

$lang =& $mainframe->getLanguage();
$lang->load(trim($option));

// set for overlib check
$mainframe->set( 'loadOverlib', false );

$gid = intval( $my->gid );

/** @global A places to store information from processing of the component */
$_MOS_OPTION = array();

ob_start();
if ($path = $mainframe->getPath( 'front' )) {
	$task 	= mosGetParam( $_REQUEST, 'task', '' );
	$ret 	= mosMenuCheck( $Itemid, $option, $task, $gid );
	if ($ret) {
		require_once( $path );
	} else {
		mosNotAuth();
	}
} else {
	header("HTTP/1.0 404 Not Found");
	echo JText::_( 'NOT_EXIST' );
}
$_MOS_OPTION['buffer'] = ob_get_contents();
ob_end_clean();

require_once( 'includes/template.php' );

initGzip();

header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );
header(' Content-Type: text/html; charset=utf-8');

// loads template file
$cur_template = $mainframe->getTemplate();
if ( !file_exists( 'templates/'. $cur_template .'/index.php' ) ) {
	$msg = sprintf( JText::_( 'TEMPLATE_WARN' ), $cur_template );
	echo "<font color=red><b>". $msg ."</b></font>";
} else {
	require_once( 'templates/'. $cur_template .'/index.php' );
	echo "<!-- ".time()." -->";
}

// displays queries performed for page
if ($mosConfig_debug) {
	echo $database->_ticker . ' queries executed';
	echo '<pre>';
 	foreach ($database->_log as $k=>$sql) {
 		echo $k+1 . "\n" . $sql . '<hr />';
	}
}

doGzip();
?>
