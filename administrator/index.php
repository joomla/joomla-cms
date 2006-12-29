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
require_once( JPATH_BASE.'/includes/framework.php'   );
require_once( JPATH_BASE.'/includes/application.php' );
require_once( JPATH_BASE.'/includes/menubar.html.php' );

/**
 * CREATE THE APPLICATION
 * 
 * NOTE :
 */
$mainframe = new JAdministrator();

// load the configuration
$mainframe->setConfiguration(JPATH_CONFIGURATION.DS.'configuration.php');

// create the session
$mainframe->setSession(JURI::resolve('/', -1).$mainframe->getClientId());

/**
 * INITIALISE THE APPLICATION
 * 
 * NOTE :
 */
JPluginHelper::importPlugin('system');

$mainframe->initialise(array(
	'language' => $mainframe->getUserState( "application.lang", 'lang' )
));

// load a stored user state if it exists -> move into plugin ?
$mainframe->loadStoredUserState();

// trigger the onAfterInitialise events
$mainframe->triggerEvent('onAfterInitialise');
JDEBUG ? $_PROFILER->mark('afterInitialise') : null;

// set for overlib check
$mainframe->set('loadOverlib', false);

/**
 * EXECUTE THE APPLICATION
 * 
 * NOTE :
 */
$option = JAdministratorHelper::findOption();
$mainframe->execute($option);

// trigger the onAfterDisplay events
$mainframe->triggerEvent('onAfterExecute');
JDEBUG ? $_PROFILER->mark('afterExecute') : null;

/**
 * DISPLAY THE APPLICATION
 * 
 * NOTE :
 */
$mainframe->display($option);

// trigger the onAfterDisplay events
$mainframe->triggerEvent( 'onAfterDisplay' );
JDEBUG ? $_PROFILER->mark( 'afterDisplay' ) : null;

/**
 * RETURN THE RESPONSE
 */
echo JResponse::toString($mainframe->getCfg('gzip'));
?>