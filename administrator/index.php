<?php
/**
* @version $Id$
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

require_once( JPATH_BASE.'/includes/defines.php'     );
require_once( JPATH_BASE.'/includes/application.php' );

/**
 * CREATE THE APPLICATION
 */
$mainframe = new JAdministrator();

// load the configuration
$mainframe->setConfiguration(JPATH_CONFIGURATION . DS . 'configuration.php');

// create the session
$mainframe->setSession( JURI::resolve('/', -1).$mainframe->getClientId());

// set the language
$mainframe->setLanguage($mainframe->getUserState( "application.lang", 'lang' ));

// load the legacy libraries if enabled
$mainframe->setLegacy();

// load system plugin group
JPluginHelper::importPlugin( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

// load a stored user state if it exists
$mainframe->loadStoredUserState();

// trigger the onStart events
$mainframe->triggerEvent( 'onAfterStart' );

JDEBUG ? $_PROFILER->mark( 'afterStartFramework' ) :  null;

/**
 * Set the version variable as a global
 */
$GLOBALS['_VERSION'] = new JVersion();

// set for overlib check
$mainframe->set( 'loadOverlib', false );

// trigger the onBeforeDisplay events
$mainframe->triggerEvent( 'onBeforeDisplay' );


/**
 * EXECUTE THE APPLICATION
 *
 * Note: This section of initialization must be performed last.
 */
$option = JAdministratorHelper::findOption();
$mainframe->execute( $option );

// trigger the onAfterDisplay events
$mainframe->triggerEvent( 'onAfterDisplay' );

JDEBUG ? $_PROFILER->mark( 'afterDisplayOutput' ) : null ;
JDEBUG ? $_PROFILER->report( true, $mainframe->getCfg( 'debug_db' ) ) : null;

?>