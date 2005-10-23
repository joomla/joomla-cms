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

if (!file_exists( '../configuration.php' )) {
	header( 'Location: ../installation/index.php' );
	exit();
}

require_once( '../globals.php' );
require_once( '../configuration.php' );

// enables switching to secure https
require_once( $mosConfig_absolute_path . '/includes/joomla.php' );
require_once( $mosConfig_absolute_path . '/administrator/includes/admin.php' );

// load system bot group
$_MAMBOTS->loadBotGroup( 'system' );

// trigger the onStart events
$_MAMBOTS->trigger( 'onBeforeStart' );

// must start the session before we create the mainframe object
session_name( md5( $mosConfig_live_site ) );
session_start();

if (!mosGetParam( $_SESSION, 'session_id' )) {
	mosRedirect( 'index.php' );
}

$option = strtolower( mosGetParam( $_REQUEST, 'option', '' ) );
if ($option == '') {
	$option = 'com_admin';
}

// mainframe is an API workhorse, lots of 'core' interaction routines
$mainframe = new mosMainFrame( $database, $option, '..', true );
$mainframe->initSession( 'php' );

// trigger the onStart events
$_MAMBOTS->trigger( 'onAfterStart' );

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

$_LANG =& mosFactory::getLanguage( $option, true );
$_LANG->debug( $mosConfig_debug );

$cur_template = $mainframe->getTemplate();

// set for overlib check
$mainframe->set( 'loadOverlib', false );

// precapture the output of the component
require_once( $mosConfig_absolute_path . '/editor/editor.php' );


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

initGzip();

header(' Content-Type: text/html; charset=UTF-8');

// start the html output
if ($no_html == 0) {
	// loads template file
	if ( !file_exists( $mosConfig_absolute_path .'/administrator/templates/'. $cur_template .'/index.php' ) ) {
		echo $_LANG->_( 'TEMPLATE' ) .' '. $cur_template .' '. $_LANG->_( 'NOT FOUND' );
	} else {
		require_once( $mosConfig_absolute_path .'/administrator/templates/'. $cur_template .'/index.php' );
	}
} else {
	mosMainBody_Admin();
}

doGzip();
?>