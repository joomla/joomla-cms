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

require_once ( JPATH_BASE .'/includes/defines.php'     );
require_once ( JPATH_BASE .'/includes/application.php' );
require_once ( JPATH_BASE .'/includes/template.php'  );

// create the mainframe object
$mainframe = new JSite();

// set the configuration
$mainframe->setConfiguration(JPATH_CONFIGURATION . DS . 'configuration.php');

//get the database object
$database =& $mainframe->getDBO();

// load system bot group
JPluginHelper::importPlugin( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

// Get the global option variable and create the pathway
$option = strtolower( JRequest::getVar( 'option' ) );
$mainframe->_createPathWay( );	

//get the acl object (for backwards compatibility)
$acl =& JFactory::getACL();

// create session
$mainframe->setSession( $mainframe->getCfg('live_site').$mainframe->getClientId() );

// login user
if ($option == 'login') {
	$mainframe->login();
}

// logout user
if ($option == 'logout') {
	$mainframe->logout();
}

$Itemid 	= JRequest::getVar( 'Itemid', 0, '', 'int' );
$no_html 	= JRequest::getVar( 'no_html', 0, '', 'int' );
$do_pdf 	= JRequest::getVar( 'do_pdf', 0, '', 'int' );

// trigger the onAfterStart events
$mainframe->triggerEvent( 'onAfterStart' );

// get the information about the current user from the sessions table
$user	= & $mainframe->getUser();
$my		= $user->_model;

$lang =& $mainframe->getLanguage();
$lang->load(trim($option));

// patch to lessen the impact on templates
if ($option == 'search') {
	$option = 'com_search';
}

//render raw component output
if($no_html == 1) {
	$path = JApplicationHelper::getPath( 'front', $option );
	$task 	= JRequest::getVar( 'task' );
	
	//load common language files
	$lang =& $mainframe->getLanguage();
	$lang->load($option);
	require_once( $path );	
	exit();	
}

// loads template file
$cur_template = $mainframe->getTemplate();
$file     = 'component.html';

// displays offline/maintanance page or bar
if ($mainframe->getCfg('offline') && $my->gid < '23') {
	$file = 'offline.php';
}

$document =& $mainframe->getDocument();
$document->parse($cur_template, $file);

header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

initDocument($document, $file);
$document->display( $file, $mainframe->getCfg('gzip') );
?>