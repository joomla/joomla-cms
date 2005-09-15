<?php
/**
* @version $Id: index.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** Set flag that this is a parent file */
define( '_VALID_MOS', 1 );

// checks for configuration file, if none found loads installation page
if ( !file_exists( 'configuration.php' ) || filesize( 'configuration.php' ) < 10 ) {
	header( 'Location: installation/index.php' );
	exit();
}

include_once( 'globals.php' );
require_once( 'configuration.php' );

// enables switching to secure https
require_once( 'includes/mambo.ssl.init.php' );

require_once( 'includes/mambo.php' );

// displays offline/maintanance page or bar
if ( $mosConfig_offline == 1 ){

	// mainframe is an API workhorse, lots of 'core' interaction routines
	$mainframe = new mosMainFrame( $database, $option='' );
	$mainframe->initSession();

	// get the information about the current user from the sessions table
	$my = $mainframe->getUser();

	// get gid
	$query = '
	SELECT gid
	FROM #__users
	WHERE id = '. intval( $my->id );
	;
	$database->setQuery( $query );
	$userstate = $database->loadResult();

	// if superadministrator, administrator or manager show offline message bar + site
	if ( $userstate == '25' || $userstate == '24' || $userstate == '23') {
		include( 'offlinebar.php' );
	}
	else {
		include( 'offline.php' );
		exit();
	}
}

// load system bot group
$_MAMBOTS->loadBotGroup( 'system' );

// trigger the onStart events
$_MAMBOTS->trigger( 'onStart' );

// checks for existance of external 3rd party SEf and loads it instead of inbuilt SEF
if (file_exists( 'components/com_sef/sef.php' )) {
	require_once( 'components/com_sef/sef.php' );
} else {
	require_once( 'includes/sef.php' );
}
require_once( 'includes/frontend.php' );

$GLOBALS['_MOS_PROFILER']->mark( 's before check itemid, queries=' . $database->_ticker );

/*
Installation sub folder check, removed for work with CVS
if (file_exists( 'installation/index.php' )) {
	include ('offline.php');
	exit();
}
*/
/** retrieve some expected url (or form) arguments */
$option = trim( strtolower( mosGetParam( $_REQUEST, 'option' ) ) );
$Itemid = intval( mosGetParam( $_REQUEST, 'Itemid', null ) );

if ($option == '') {
	if ($Itemid) {
		$query = "SELECT id, link"
		. "\n FROM #__menu"
		. "\n WHERE menutype='mainmenu'"
		. "\n AND id = '$Itemid'"
		. "\n AND published = '1'"
		;
		$database->setQuery( $query );
	} else {
		$query = "SELECT id, link"
		. "\n FROM #__menu"
		. "\n WHERE menutype='mainmenu' AND published='1'"
		. "\n ORDER BY parent, ordering"
		;
		$database->setQuery( $query, 0, 1 );
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

/** mainframe is an API workhorse, lots of 'core' interaction routines */
$mainframe = new mosMainFrame( $database, $option );
$mainframe->initSession();

// checking if we can find the Itemid thru the content
if ( $option == 'com_content' && $Itemid === 0 ) {
	$id = intval( mosGetParam( $_REQUEST, 'id', 0 ) );
	$Itemid = $mainframe->getItemid( $id );
}

/** do we have a valid Itemid yet?? */
if ( $Itemid === 0 ) {
	/** Nope, just use the homepage then. */
	$query = "SELECT id"
	. "\n FROM #__menu"
	. "\n WHERE menutype='mainmenu'"
	. "\n AND published='1'"
	. "\n ORDER BY parent, ordering"
	;
	$database->setQuery( $query, 0, 1 );
	$Itemid = $database->loadResult();
}

/** patch to lessen the impact on templates */
if ($option == 'search') {
	$option = 'com_search';
}

/** get the information about the current user from the sessions table */
$my = $mainframe->getUser();

$GLOBALS['_MOS_PROFILER']->mark( 's before create language, queries=' . $database->_ticker );

// loads language depending on the user settings
$_LANG = mosFactory::getLanguage( $option );
$_LANG->debug( $mosConfig_debug );

// frontend login & logout controls
$return 	= mosGetParam( $_REQUEST, 'return', NULL );
$message 	= mosGetParam( $_POST, 'message', 0 );
if ($option == 'login') {
// Log in
	if (!$mainframe->login()) {
		$mainframe->logout();
		mosErrorAlert( $_LANG->_( 'LOGIN_INCORRECT' ) );
	}

	// JS Popup message
	if ( $message ) {
		?>
		<script language="javascript" type="text/javascript">
		<!--//
		alert( "<?php echo $_LANG->_( 'LOGIN_SUCCESS' ); ?>" );
		//-->
		</script>
		<?php
	}

	if ( $return ) {
		mosRedirect( $return );
	} else {
		mosRedirect( 'index.php' );
	}
} else if ( $option == 'logout' ) {
// Log Out
	$mainframe->logout();

	// JS Popup message
	if ( $message ) {
		?>
		<script language="javascript" type="text/javascript">
		<!--//
		alert( "<?php echo $_LANG->_( 'LOGOUT_SUCCESS' ); ?>" );
		//-->
		</script>
		<?php
	}

	if ( $return ) {
		mosRedirect( $return );
	} else {
		mosRedirect( 'index.php' );
	}
}

$gid = intval( $my->gid );

// gets template for page
$cur_template = $mainframe->getTemplate();

/** @global A places to store information from processing of the component */
$_MOS_OPTION = array();

// precapture the output of the component
require_once( $mosConfig_absolute_path . '/editor/editor.php' );

$GLOBALS['_MOS_PROFILER']->mark( 's before do component, queries=' . $database->_ticker );

ob_start();
if ($path = $mainframe->getPath( 'front' )) {
	$task = mosGetParam( $_REQUEST, 'task', '' );
	$ret = mosMenuCheck( $Itemid, $option, $task, $gid );
	if ($ret) {
		require_once( $path );
	} else {
		mosNotAuth();
	}
} else {
	echo $_LANG->_( 'NOT_EXIST' );
}
$_MOS_OPTION['buffer'] = ob_get_contents();
ob_end_clean();

$GLOBALS['_MOS_PROFILER']->mark( 's before output template, queries=' . $database->_ticker );

// trigger the onBeforeDisplay events
$_MAMBOTS->trigger( 'onBeforeDisplay' );

initGzip();

// Page caching
// For now page caching will only be used for anonymous users

$pageCache = mosFactory::getCache('page', 'mosCache_Page');
$pageCache->setCaching(!$my->id && $GLOBALS['mosConfig_page_caching']);
$pageCache->setCacheValidation(true);

// Compute unique cache identifier for the page we're about
// to cache. We'll assume that the page's output depends on
// the HTTP GET variables

$cacheId = $pageCache->generateId($_GET);

if(!$pageCache->start($cacheId, 'page')) {

	if ($mosConfig_offline == 0) {
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: '. gmdate( 'D, d M Y H:i:s' ) .' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
	}

	// loads template file
	if ( !file_exists( 'templates/'. $cur_template .'/index.php' ) ) {
		echo $_LANG->_( 'TEMPLATE NOT FOUND' ). $cur_template;
	} else {
		require_once( 'templates/'. $cur_template .'/index.php' );
		if ($mosConfig_debug) {
			echo '<!-- '. time() .' -->';
		}
	}

	$pageCache->end();
}

// displays queries performed for page
if (@$mosConfig_debug_dblog) {
	echo $database->_ticker . ' queries executed';
	echo '<pre>';
 	foreach ($database->_log as $k=>$sql) {
 	    echo $k+1 . "\n" . $sql . '<hr />';
	}
	echo '</pre>';
}

doGzip();
if ( $_VERSION->DEV_STATUS == 'Dev' ) {
	$GLOBALS['_MOS_PROFILER']->mark( 's execution time, queries=' . $database->_ticker );
	echo $GLOBALS['_MOS_PROFILER']->report();
	//echo var_dump($_GET);
}
?>