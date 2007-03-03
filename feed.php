<?php
/**
* @version		$Id: feed.php 5061 2006-09-14 21:23:40Z Jinx $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );

require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'application.php' );

/**
 * CREATE THE APPLICATION
 *
 * NOTE :
 */
$mainframe = new JSite();

// load the configuration settings
$mainframe->loadConfiguration(JPATH_CONFIGURATION.DS.'configuration.php');

// create the session
$mainframe->loadSession(JUtility::getHash($mainframe->getClientId()));

/**
 * INITIALISE THE APPLICATION
 *
 * NOTE :
 */
JPluginHelper::importPlugin( 'system' );

// set the language
$mainframe->initialise();

// trigger the onAfterInitialise events
JDEBUG ? $_PROFILER->mark('afterInitialise') : null;
$mainframe->triggerEvent('onAfterInitialise');


// authorization
$Itemid = JSiteHelper::findItemid();
$mainframe->authorize($Itemid);

//if ($mainframe->getCfg('offline') && $user->get('gid') < '23' ) {
//	$file = 'offline.php';
//}

/**
 * EXECUTE THE APPLICATION
 *
 * NOTE :
 */
$params = array(
	'format' =>  JRequest::getVar( 'format', 'rss2.0', '', 'string' )
);

JRequest::setVar('format', 'feed');
$document =& JFactory::getDocument('feed');

$option = JSiteHelper::findOption();
$document->setBuffer( JComponentHelper::renderComponent($option), 'component');

$document->setTitle( $mainframe->getCfg('sitename' ));

// trigger the onAfterDisplay events
JDEBUG ? $_PROFILER->mark('afterExecute') : null;
$mainframe->triggerEvent('onAfterExecute');


/**
 * DISPLAY THE APPLICATION
 *
 * NOTE :
 */
JResponse::setBody($document->render( false, $params));

// trigger the onAfterDisplay events
JDEBUG ? $_PROFILER->mark('afterDisplay') : null;
$mainframe->triggerEvent('onAfterDisplay');

/**
 * CLOSE THE SESSION
 */
JSession::close();

/**
 * RETURN THE RESPONSE
 */
echo JResponse::toString($mainframe->getCfg('gzip'));

JDEBUG ? $_PROFILER->mark( 'afterDisplayOutput' ) : null;