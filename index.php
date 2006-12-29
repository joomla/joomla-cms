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

require_once ( JPATH_BASE .'/includes/defines.php'     );
require_once ( JPATH_BASE .'/includes/framework.php'   );
require_once ( JPATH_BASE .'/includes/application.php' );


/**
 * CREATE THE APPLICATION
 * 
 * NOTE :
 */
$mainframe = new JSite();

// looad the configuration settings
$mainframe->setConfiguration(JPATH_CONFIGURATION.DS.'configuration.php');

// create the session
$mainframe->setSession( JURI::resolve('/', -1).$mainframe->getClientId());

/**
 * INITIALISE THE APPLICATION
 * 
 * NOTE :
 */
JPluginHelper::importPlugin('system');

// set the language
$mainframe->initialise();

// trigger the onAfterInitialise events
$mainframe->triggerEvent('onAfterInitialise');
JDEBUG ? $_PROFILER->mark('afterInitialise') : null;

// authorization
$Itemid = JSiteHelper::findItemid();
$mainframe->authorize($Itemid);

// set for overlib check
$mainframe->set('loadOverlib', false);

/**
 * EXECUTE THE APPLICATION
 * 
 * NOTE :
 */
$option = JSiteHelper::findOption();
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
$mainframe->triggerEvent('onAfterDisplay');
JDEBUG ? $_PROFILER->mark('afterDisplay') : null;

/**
 * RETURN THE RESPONSE
 */
echo JResponse::toString($mainframe->getCfg('gzip'));
?>