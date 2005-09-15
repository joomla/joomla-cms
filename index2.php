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
define( "_VALID_MOS", 1 );

include_once ('globals.php');
require_once ('configuration.php');

// enables switching to secure https
require_once( 'includes/mambo.ssl.init.php' );

require_once ('includes/mambo.php');
if (file_exists( 'components/com_sef/sef.php' )) {
	require_once( 'components/com_sef/sef.php' );
} else {
	require_once( 'includes/sef.php' );
}
require_once ('includes/frontend.php');

// retrieve some expected url (or form) arguments
$option 	= trim( strtolower( mosGetParam( $_REQUEST, 'option' ) ) );
$no_html 	= intval( mosGetParam( $_REQUEST, 'no_html', 0 ) );
$Itemid 	= strtolower( trim( mosGetParam( $_REQUEST, 'Itemid',0 ) ) );
$act 		= mosGetParam( $_REQUEST, 'act', '' );
$do_pdf 	= intval( mosGetParam( $_REQUEST, 'do_pdf', 0 ) );
$pop	 	= intval( mosGetParam( $_REQUEST, 'pop', 0 ) );
$id 		= intval( mosGetParam( $_REQUEST, 'id', 0 ) );

// mainframe is an API workhorse, lots of 'core' interaction routines
$mainframe = new mosMainFrame( $database, $option );

$mainframe->initSession();
// get the information about the current user from the sessions table
$my = $mainframe->getUser();

// loads english language file by default
if ( $mosConfig_lang == '' ) {
	$mosConfig_lang = 'english';
}
$_LANG = mosFactory::getLanguage( $option );
$_LANG->debug( $mosConfig_debug );

$cur_template = $mainframe->getTemplate();

// displays offline/maintanance page or bar
if ($mosConfig_offline == 1){
	// get gid
	$query = '
	SELECT gid
	FROM #__users
	WHERE id = '. intval( $my->id );
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

if ( $option == 'login' ) {
	$mainframe->login();
	mosRedirect('index.php');
} else if ( $option == 'logout' ) {
	$mainframe->logout();
	mosRedirect( 'index.php' );
}

if ( $do_pdf == 1 ){
	include_once('includes/pdf.php');
	exit();
}

$gid = intval( $my->gid );

// precapture the output of the component
require_once( $mosConfig_absolute_path . '/editor/editor.php' );

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

initGzip();

header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
header( "Cache-Control: no-store, no-cache, must-revalidate" );
header( "Cache-Control: post-check=0, pre-check=0", false );
header( "Pragma: no-cache" );

// print template
if ( $pop == 1 ){
	// load from template directory if one can be found, otherwise run default index2.php template
	$path = 'templates/' .$cur_template .'/print.php';
	if ( file_exists( $path ) ) {
		include_once( $path );
		exit();
	}
}

// start basic HTML
if ( $no_html == 0 ) {
	// xml prolog
	echo '<?xml version="1.0" encoding="'. $_LANG->iso() .'"?' .'>';
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $_LANG->isoCode();?>">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_LANG->iso(); ?>" />
	<?php mosShowHead(); ?>
	<link rel="stylesheet" href="templates/<?php echo $cur_template;?>/css/template_css.css" type="text/css" />
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