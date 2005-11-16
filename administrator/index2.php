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

// Set flag that this is a parent file
define( '_VALID_MOS', 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once ( 'includes/defines.php');
require_once ( 'includes/administrator.php' );

$_PROFILER->mark( 'onBeforeStart' );

// load system bot group
$_MAMBOTS->loadBotGroup( 'system' );

// trigger the onStart events
$_MAMBOTS->trigger( 'onBeforeStart' );


// mainframe is an API workhorse, lots of 'core' interaction routines
$mainframe =& new JAdministrator();
$mainframe->initSession( );

if (JSession::get('guest')) {
	mosRedirect( 'index.php' );
}

// trigger the onStart events
$_MAMBOTS->trigger( 'onAfterStart' );

$_PROFILER->mark( 'onAfterStart' );

/** get the information about the current user from the sessions table */
$my = $mainframe->getUser();

$handle = mosGetParam( $_REQUEST, 'handle', null );

// double check (this one is used on timeouts)
if ($my->id < 1) {
	$mainframe->logout();
	mosRedirect( 'index.php' . $handle );
}

// TODO: fix this patch to get gid to work properly
//$my->gid = array_shift( $acl->get_object_groups( $acl->get_object_id( 'users', $my->id, 'ARO' ), 'ARO' ) );


// initialise some common request directives
$option 	= strtolower( mosGetParam( $_REQUEST, 'option', 'com_admin' ) );
$task 		= mosGetParam( $_REQUEST, 'task', '' );
$act 		= strtolower( mosGetParam( $_REQUEST, 'act', '' ) );
$section 	= mosGetParam( $_REQUEST, 'section', '' );
$no_html 	= strtolower( mosGetParam( $_REQUEST, 'no_html', 0 ) );
$id         = intval( mosGetParam( $_REQUEST, 'id' ) );
$cid		= mosGetParam( $_POST, 'cid', null );

if ($option == 'logout') {
	$mainframe->logout();
	mosRedirect( $mosConfig_live_site );
}

$cur_template = $mainframe->getTemplate();

// set for overlib check
$mainframe->set( 'loadOverlib', false );

$_PROFILER->mark( 'onBeforeBuffer' );

ob_start();
if ($path = $mainframe->getPath( 'admin' )) {
		require_once ( $path );
} else {
	?>
	<img src="images/joomla_logo_black.jpg" border="0" alt="<?php echo 'Joomla! Logo'; ?>" />
	<br />
	<?php
}

$_MOS_OPTION['buffer'] = ob_get_contents();
ob_end_clean();

$_PROFILER->mark( 'onAfterBuffer' );

$_PROFILER->mark( 'onBeforeOutput' );

initGzip();

header(' Content-Type: text/html; charset=UTF-8');

// start the html output
if ($no_html == 0) {
	// loads template file
	if ( !file_exists( JPATH_ADMINISTRATOR .'/templates/'. $cur_template .'/index.php' ) ) {
		echo JText::_( 'TEMPLATE' ) .' '. $cur_template .' '. JText::_( 'NOT FOUND' );
	} else {
		require_once( JPATH_ADMINISTRATOR .'/templates/'. $cur_template .'/index.php' );
	}
} else {
	mosMainBody_Admin();
}

$_PROFILER->mark( 'onAfterOutput' );

doGzip();

if ($mainframe->getCfg('debug')) {
	echo $_PROFILER->report();
	echo $_PROFILER->getMemory();
}
?>