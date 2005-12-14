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
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once ( 'includes'. DIRECTORY_SEPARATOR .'defines.php');
require_once(  'includes'. DIRECTORY_SEPARATOR .'administrator.php' );

$option = mosGetParam( $_REQUEST, 'option', NULL );
$handle = mosGetParam( $_POST, 'handle', NULL );

// create the mainframe object
$mainframe =& new JAdministrator($option);

//get the database object
$database =& $mainframe->getDBO();

// load system plugin group
JPluginHelper::importGroup( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

//get the acl object (for backwards compatibility)
$acl =& JFactory::getACL();

//create the session
$mainframe->setSession( $mainframe->getCfg('live_site').$mainframe->_client );

// trigger the onAfterStart events
$mainframe->triggerEvent( 'onAfterStart' );

if (isset( $_POST['submit'] )) {
	if ($mainframe->login()) {
		$mainframe->setUserState( 'lang', mosGetParam( $_REQUEST, 'lang', $mosConfig_lang ) );
		JSession::pause();
		/** cannot using mosredirect as this stuffs up the cookie in IIS */
		$handle = isset($handle) ? ('?handle=' . $handle) : '';
		mosErrorAlert( '', "document.location.href='index2.php" . $handle . "'", 2 );
	} else {
		mosErrorAlert( JText::_( 'validUserPassAccess' ), "document.location.href='index.php'" );
	}
}

initGzip();
header( 'Content-Type: text/html; charset=UTF-8');

$template = $mainframe->getTemplate();
$path = JPATH_ADMINISTRATOR . '/templates/' . $template . '/login.php';
require_once( $path );

doGzip();
?>