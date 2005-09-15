<?php
/**
* @version $Id: index2.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** Set flag that this is a parent file */
define( '_VALID_MOS', 1 );

if (!file_exists( '../configuration.php' )) {
	header( 'Location: ../installation/index.php' );
	exit();
}

require_once( '../globals.php' );
require_once( '../configuration.php' );

// enables switching to secure https
require_once( $mosConfig_absolute_path .'/includes/mambo.ssl.init.php' );

require_once( $mosConfig_absolute_path .'/includes/mambo.php' );
require_once( $mosConfig_absolute_path .'/administrator/includes/admin.php' );

// must start the session before we create the mainframe object
session_name( md5( $mosConfig_live_site ) );
session_start();

if (!mosGetParam( $_SESSION, 'session_id' )) {
	mosRedirect( 'index.php' );
}

// mainframe is an API workhorse, lots of 'core' interaction routines
$mainframe = new mosMainFrame( $database, '', true );
$mainframe->initSession( 'php' );

/** get the information about the current user from the sessions table */
$my = $mainframe->getUser();

// double check (this one is used on timeouts)
if ($my->id < 1 || !$acl->acl_check( 'login', 'administrator', 'users', $my->usertype )) {
	$mainframe->logout();

	$handle = '';
	if ($mainframe->getCfg( 'savestate' ) && $option != 'logout' && $option != 'com_media') {
		$ser = mosFactory::getSerializer();
		$handle = '?handle=' . $ser->serializeState();
	}
	mosRedirect( 'index.php' . $handle );
}
// TODO: fix this patch to get gid to work properly
$my->gid = array_shift( $acl->get_object_groups( $acl->get_object_id( 'users', $my->id, 'ARO' ), 'ARO' ) );

// check to see if state is saved, if so retrieve it
$handle = mosGetParam( $_REQUEST, 'handle', null );
if ($mainframe->getCfg( 'savestate' ) && $handle) {
	$ser = mosFactory::getSerializer();
	$ser->deserializeState( $handle );
}

// initialise some common request directives
$option 	= strtolower( mosGetParam( $_REQUEST, 'option', 'com_admin' ) );
$task 		= mosGetParam( $_REQUEST, 'task', '' );
$act 		= strtolower( mosGetParam( $_REQUEST, 'act', '' ) );
$section 	= mosGetParam( $_REQUEST, 'section', '' );
$no_html 	= strtolower( mosGetParam( $_REQUEST, 'no_html', 0 ) );
$id 		= intval( mosGetParam( $_REQUEST, 'id', '' ) );
$cid		= mosGetParam( $_POST, 'cid', null );
mosArrayToInts( $cid, 0 );

if ($option == 'logout') {
	$mainframe->logout();
	mosRedirect( $mosConfig_live_site );
}

$_LANG =& mosFactory::getLanguage( $option, true );
$_LANG->debug( $mosConfig_debug );

$cur_template = $mainframe->getTemplate();

// precapture the output of the component
require_once( $mosConfig_absolute_path . '/editor/editor.php' );

ob_start();
if ($path = $mainframe->getPath( 'admin' )) {
		require_once ($path);
} else {
	?>
	<img src="images/logo.png" border="0" alt="<?php echo $_LANG->_( 'Joomla! Logo' ); ?>" />
	<br />
	<?php
}

$_MOS_OPTION['buffer'] = ob_get_contents();
ob_end_clean();

initGzip();

// start the html output
if ($no_html == 0) {
	// loads template file
	if ( !file_exists( $mosConfig_absolute_path .'/administrator/templates/'. $cur_template .'/index.php' ) ) {
		echo $_LANG->_( 'TEMPLATE NOT FOUND' ). $cur_template;
	} else {
		require_once( $mosConfig_absolute_path .'/administrator/templates/'. $cur_template .'/index.php' );
		if ($mosConfig_debug) {
			echo '<!-- '. time() .' -->';
		}
	}
} else {
	mosMainBody_Admin();
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
if ($_VERSION->DEV_STATUS == 'Dev') {
	$GLOBALS['_MOS_PROFILER']->mark( 's execution time' );
	echo $GLOBALS['_MOS_PROFILER']->report();
}
?>