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
require_once( $mosConfig_absolute_path . '/includes/joomla.php' );
include_once( $mosConfig_absolute_path . '/language/'. $mosConfig_lang .'.php' );
require_once( $mosConfig_absolute_path . '/administrator/includes/admin.php' );

$option = strtolower( mosGetParam( $_REQUEST, 'option', '' ) );
if ($option == '') {
	$option = 'com_admin';
}
// must start the session before we create the mainframe object
session_name( md5( $mosConfig_live_site ) );
session_start();

// mainframe is an API workhorse, lots of 'core' interaction routines
$mainframe = new mosMainFrame( $database, $option, '..', true );

// initialise some common request directives
$task 		= mosGetParam( $_REQUEST, 'task', '' );
$act 		= strtolower( mosGetParam( $_REQUEST, 'act', '' ) );
$section 	= mosGetParam( $_REQUEST, 'section', '' );
$no_html 	= strtolower( mosGetParam( $_REQUEST, 'no_html', '' ) );
$id         = intval( mosGetParam( $_REQUEST, 'id' ) );

if ($option == 'logout') {
	require 'logout.php';
	exit();
}

// restore some session variables
$my 			= new mosUser( $database );
$my->id 		= mosGetParam( $_SESSION, 'session_user_id', '' );
$my->username 	= mosGetParam( $_SESSION, 'session_username', '' );
$my->usertype 	= mosGetParam( $_SESSION, 'session_usertype', '' );
$my->gid 		= mosGetParam( $_SESSION, 'session_gid', '' );
$my->params		= mosGetParam( $_SESSION, 'session_user_params', '' );
$session_id 	= mosGetParam( $_SESSION, 'session_id', '' );
$logintime 		= mosGetParam( $_SESSION, 'session_logintime', '' );

// check against db record of session
if ( $session_id == md5( $my->id . $my->username . $my->usertype . $logintime ) ) {
	$query = "SELECT *"
	. "\n FROM #__session"
	. "\n WHERE session_id = '$session_id'"
	. "\n AND username = " . $database->Quote( $my->username )
	. "\n AND userid = " . intval( $my->id )
	;
	$database->setQuery( $query );
	if (!$result = $database->query()) {
		echo $database->stderr();
	}
	if ($database->getNumRows( $result ) <> 1) {
		echo "<script>document.location.href='index.php'</script>\n";
		exit();
	}
} else {
	echo "<script>document.location.href='$mosConfig_live_site/administrator/index.php'</script>\n";
	exit();
}

// update session timestamp
$current_time = time();
$query = "UPDATE #__session"
. "\n SET time = '$current_time'"
. "\n WHERE session_id = '$session_id'"
;
$database->setQuery( $query );
$database->query();

// timeout old sessions
$past = time()-1800;
$query = "DELETE FROM #__session"
. "\n WHERE time < '$past'"
;
$database->setQuery( $query );
$database->query();

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