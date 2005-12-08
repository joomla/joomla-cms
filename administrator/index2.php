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

// initialise some common request directives
$option 	= strtolower( mosGetParam( $_REQUEST, 'option', 'com_admin' ) );
$task 		= mosGetParam( $_REQUEST, 'task', '' );
$section 	= mosGetParam( $_REQUEST, 'section', '' );
$no_html 	= strtolower( mosGetParam( $_REQUEST, 'no_html', 0 ) );
$id         = intval( mosGetParam( $_REQUEST, 'id' ) );
$cid		= mosGetParam( $_POST, 'cid', null );

// create the mainframe object
$mainframe =& new JAdministrator($option);

//get the database object
$database =& $mainframe->getDBO();

// load system bot group
JBotLoader::importGroup( 'system' );

$_PROFILER->mark( 'onBeforeStart' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );



//get the acl object (for backwards compatibility)
$acl =& JFactory::getACL();

// create the session
$mainframe->setSession( $mainframe->getCfg('live_site').$mainframe->_client );

if (is_null(JSession::get('guest')) || JSession::get('guest')) {
	$handle = mosGetParam( $_REQUEST, 'handle', null );
	mosRedirect( 'index.php' . $handle);
}

// trigger the onStart events
$mainframe->triggerEvent( 'onAfterStart' );

$_PROFILER->mark( 'onAfterStart' );

if ($option == 'logout') {
	$mainframe->logout();
	mosRedirect( JURL_SITE );
}

// get the information about the current user from the sessions table
$my   = $mainframe->getUser();

$lang =& $mainframe->getLanguage();
$lang->load(trim($option));

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

header( 'Content-Type: text/html; charset=UTF-8');

// start the html output
if ($no_html == 0) {
	// loads template file
	$template = $mainframe->getTemplate();
	if ( !file_exists( JPATH_ADMINISTRATOR .'/templates/'. $template .'/index.php' ) ) {
		echo sprintf( JText::_( 'TEMPLATE NOT FOUND' ), $template );
	} else {
		require_once( JPATH_ADMINISTRATOR .'/templates/'. $template .'/index.php' );
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
