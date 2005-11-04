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

$option = strtolower( mosGetParam( $_REQUEST, 'option', 'com_admin' ) );

// must start the session before we create the mainframe object
session_name( md5( $mosConfig_live_site ) );
session_start();

if (!mosGetParam( $_SESSION, 'session_id' )) {
	mosRedirect( 'index.php' );
}

// mainframe is an API workhorse, lots of 'core' interaction routines
$mainframe = new mosMainFrame( $database, $option, '..', 1 );
$mainframe->initSession( 'php' );

// trigger the onStart events
$_MAMBOTS->trigger( 'onAfterStart' );

// get the information about the current user from the sessions table
$my = $mainframe->getUser();
// TODO: fix this patch to get gid to work properly
//$my->gid = array_shift( $acl->get_object_groups( $acl->get_object_id( 'users', $my->id, 'ARO' ), 'ARO' ) );

// initialise some common request directives
$task		= mosGetParam( $_REQUEST, 'task', '' );
$act		= strtolower( mosGetParam( $_REQUEST, 'act', '' ) );
$section	= mosGetParam( $_REQUEST, 'section', '' );
$no_html	= strtolower( mosGetParam( $_REQUEST, 'no_html', '' ) );
$mosmsg		= strip_tags( mosGetParam( $_REQUEST, 'mosmsg', '' ) );

if ($option == 'logout') {
	$mainframe->logout();
	mosRedirect( $mosConfig_live_site );
}

$params = $database->loadResult();
$my->params = new mosParameters( $params );

$session_id = mosGetParam( $_SESSION, 'session_id', '' );
$logintime 	= mosGetParam( $_SESSION, 'session_logintime', '' );

// check against db record of session
$_LANG =& JFactory::getLanguage( $option );
$_LANG->debug( $mosConfig_debug );

// start the html output
if ($no_html) {
	if ($path = $mainframe->getPath( 'admin' )) {
		require $path;
	}
	exit;
}

initGzip();
header(' Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $mosConfig_sitename; ?> - Administration [Joomla]</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"" />
<link rel="stylesheet" href="templates/<?php echo $mainframe->getTemplate(); ?>/css/template_css.css" type="text/css">
<link rel="stylesheet" href="templates/<?php echo $mainframe->getTemplate(); ?>/css/<?php echo ($_LANG->rtl()) ? 'theme_rtl.css' : 'theme.css' ?>" type="text/css">
<script language="JavaScript" src="../includes/js/JSCookMenu_mini.js" type="text/javascript"></script>
<script language="JavaScript" src="includes/js/ThemeOffice/<?php echo ($_LANG->rtl()) ? 'theme_rtl.js' : 'theme.js' ?>" type="text/javascript"></script>
<script language="JavaScript" src="../includes/js/joomla.javascript.js" type="text/javascript"></script>

<?php
$mainframe->set( 'loadEditor', true );
initEditor();
?>
</head>
<body>

<?php
if ($mosmsg) {
	if (!get_magic_quotes_gpc()) {
		$mosmsg = addslashes( $mosmsg );
	}
	echo "\n<script language=\"javascript\" type=\"text/javascript\">alert('$mosmsg');</script>";
}

// Show list of items to edit or delete or create new
if ($path = $mainframe->getPath( 'admin' )) {
	require $path;
} else {
	?>
	<img src="images/joomla_logo_black.jpg" border="0" alt="Joomla! Logo" />
	<br />
	<?php
}
?>
</body>
</html>
<?php
doGzip();
?>
