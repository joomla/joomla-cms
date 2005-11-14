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

// displays offline/maintanance page or bar
if ($mosConfig_offline == 1) {
	require( 'offline.php' );
}

// load system bot group
$_MAMBOTS->loadBotGroup( 'system' );

// trigger the onStart events
$_MAMBOTS->trigger( 'onBeforeStart' );

// retrieve some expected url (or form) arguments
$option 	= strtolower( mosGetParam( $_REQUEST, 'option' ) );
$Itemid 	= strtolower( mosGetParam( $_REQUEST, 'Itemid',0 ) );
$no_html 	= intval( mosGetParam( $_REQUEST, 'no_html', 0 ) );
$do_pdf 	= intval( mosGetParam( $_REQUEST, 'do_pdf', 0 ) );

// mainframe is an API workhorse, lots of 'core' interaction routines
$mainframe = new mosMainFrame( $database, $option, '.' );
$mainframe->initSession();

// trigger the onAfterStart events
$_MAMBOTS->trigger( 'onAfterStart' );

// get the information about the current user from the sessions table
$my = $mainframe->getUser();

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

$cur_template = $mainframe->getTemplate();

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

// display the offline alert if an admin is logged in
if (defined( '_ADMIN_OFFLINE' )) {
	include( 'offlinebar.php' );
}

// start basic HTML
if ( $no_html == 0 ) {
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<?php echo $mainframe->getHead(); ?>
	<link rel="stylesheet" href="templates/<?php echo $cur_template;?>/css/template_css.css" type="text/css" />
	<link rel="shortcut icon" href="<?php echo $mosConfig_live_site; ?>/images/favicon.ico" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex, nofollow" />
	</head>
	<body class="contentpane">
	<?php mosMainBody(); ?>
	</body>
	</html>
	<?php
} else {
	mosMainBody();
}
doGzip();
?>

