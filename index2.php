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
require_once ( 'includes/joomla.php' );

// create the mainframe object
$mainframe =& new JSite();

// load system bot group
JBotLoader::importGroup( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

// create session
$mainframe->_createSession( $mainframe->getCfg('live_site').$mainframe->_client );

// get the information about the current user from the sessions table
$my = $mainframe->getUser();

// displays offline/maintanance page or bar
if ($mosConfig_offline == 1) {
	// if superadministrator, administrator or manager show offline message bar + site
	if ( $my->gid < '23') {
		header(' Content-Type: text/htm; charset=UTF-8');
		require_once( 'templates/_system/offline.php' );
		exit();
	}
}

// retrieve some expected url (or form) arguments
$option 	= strtolower( mosGetParam( $_REQUEST, 'option' ) );
$Itemid 	= strtolower( mosGetParam( $_REQUEST, 'Itemid',0 ) );
$no_html 	= intval( mosGetParam( $_REQUEST, 'no_html', 0 ) );
$do_pdf 	= intval( mosGetParam( $_REQUEST, 'do_pdf', 0 ) );


// trigger the onAfterStart events
$mainframe->triggerEvent( 'onAfterStart' );

$lang =& $mainframe->getLanguage();
$lang->load(trim($option));

// patch to lessen the impact on templates
if ($option == 'search') {
	$option = 'com_search';
}

if ($option == 'login') {
	$mainframe->login();
	mosRedirect('index.php');
} else if ($option == 'logout') {
	$mainframe->logout();
	mosRedirect( 'index.php' );
}

if ( $do_pdf == 1 ){
	jimport('joomla.pdf');
	exit();
}

$gid = intval( $my->gid );

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
header(' Content-Type: text/htm; charset=UTF-8');

// loads template file
$template = $mainframe->getTemplate();
if ( !file_exists( 'templates/'. $template .'/index2.php' ) ) {
	require_once( 'templates/_system/index2.php' );
} else {
	require_once( 'templates/'. $template .'/index2.php' );
}

echo "<!-- ".time()." -->";
doGzip();
?>

