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
include_once( $mosConfig_absolute_path . '/language/'. $mosConfig_lang. '.php' );
require_once( $mosConfig_absolute_path . '/administrator/includes/admin.php' );

$option = trim( strtolower( mosGetParam( $_REQUEST, 'option', '' ) ) );

// must start the session before we create the mainframe object
session_name( md5( $mosConfig_live_site ) );
session_start();

// mainframe is an API workhorse, lots of 'core' interaction routines
$mainframe = new mosMainFrame( $database, $option, '..', true );

// initialise some common request directives
$task		= mosGetParam( $_REQUEST, 'task', '' );
$act		= strtolower( mosGetParam( $_REQUEST, 'act', '' ) );
$section	= mosGetParam( $_REQUEST, 'section', '' );
$mosmsg		= strip_tags( mosGetParam( $_REQUEST, 'mosmsg', '' ) );
$no_html	= strtolower( mosGetParam( $_REQUEST, 'no_html', '' ) );

if ($option == 'logout') {
	require 'logout.php';
	exit();
}

// restore some session variables
$my = new mosUser( $database );
$my->id 		= mosGetParam( $_SESSION, 'session_user_id', '' );
$my->username 	= mosGetParam( $_SESSION, 'session_username', '' );
$my->usertype 	= mosGetParam( $_SESSION, 'session_usertype', '' );
$my->gid 		= mosGetParam( $_SESSION, 'session_gid', '' );

$session_id 	= mosGetParam( $_SESSION, 'session_id', '' );
$logintime 		= mosGetParam( $_SESSION, 'session_logintime', '' );

// check against db record of session
if ( $session_id == md5( $my->id . $my->username . $my->usertype . $logintime ) ) {
	$query = "SELECT *"
	. "\n FROM #__session"
	. "\n WHERE session_id = '$session_id'"
	. "\n AND username = '" . $database->getEscaped( $my->username ) . "'"
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
	echo "<script>document.location.href='index.php'</script>\n";
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

// start the html output
if ($no_html) {
	if ($path = $mainframe->getPath( 'admin' )) {
		require $path;
	}
	exit;
}

initGzip();

?>
<?php echo "<?xml version=\"1.0\"?>"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $mosConfig_sitename; ?> - Administration [Joomla]</title>
<link rel="stylesheet" href="templates/<?php echo $mainframe->getTemplate(); ?>/css/template_css.css" type="text/css">
<link rel="stylesheet" href="templates/<?php echo $mainframe->getTemplate(); ?>/css/theme.css" type="text/css">
<script language="JavaScript" src="../includes/js/JSCookMenu_mini.js" type="text/javascript"></script>
<script language="JavaScript" src="includes/js/ThemeOffice/theme.js" type="text/javascript"></script>
<script language="JavaScript" src="../includes/js/joomla.javascript.js" type="text/javascript"></script>
<meta http-equiv="Content-Type" content="text/html; <?php echo _ISO; ?>" />
<?php
$mainframe->set( 'loadEditor', true );
include_once( $mosConfig_absolute_path . '/editor/editor.php' );
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