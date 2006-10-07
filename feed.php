<?php
/**
* @version $Id: feed.php 5061 2006-09-14 21:23:40Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/application.php' );

// create the mainframe object
$mainframe = new JSite();

// set the configuration
$mainframe->setConfiguration(JPATH_CONFIGURATION . DS . 'configuration.php');

// load system plugin group
JPluginHelper::importPlugin( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

// create the session
$mainframe->setSession( $mainframe->getCfg('live_site').$mainframe->getClientId() );

// trigger the onAfterStart events
$mainframe->triggerEvent( 'onAfterStart' );

JDEBUG ? $_PROFILER->mark( 'afterStartFramework' ) : null;

// authorization
$Itemid = JSiteHelper::findItemid();
$mainframe->authorize($Itemid);

//if ($mainframe->getCfg('offline') && $user->get('gid') < '23' ) {
//	$file = 'offline.php';
//}

$option = JSiteHelper::findOption(); 
$params = array(
	'format' =>  JRequest::getVar( 'format', 'rss2.0', '', 'string' )
);

$document =& JFactory::getDocument('feed');
$document->setTitle( $mainframe->getCfg('sitename' ));
$document->display( false, $mainframe->getCfg('gzip'), $params);

JDEBUG ? $_PROFILER->mark( 'afterDisplayOutput' ) : null;

//TODO :: log debug information to file
?>