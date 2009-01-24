<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) );

define('DS', DIRECTORY_SEPARATOR);

require_once JPATH_BASE .DS.'includes'.DS.'defines.php';
require_once JPATH_BASE .DS.'includes'.DS.'framework.php';
require_once JPATH_BASE .DS.'includes'.DS.'helper.php';
require_once JPATH_BASE .DS.'includes'.DS.'toolbar.php';

JDEBUG ? $_PROFILER->mark( 'afterLoad' ) : null;

/**
 * CREATE THE APPLICATION
 *
 * NOTE :
 */
$mainframe =& JFactory::getApplication('administrator');

/**
 * INITIALISE THE APPLICATION
 *
 * NOTE :
 */
$mainframe->initialise(array(
	'language' => $mainframe->getUserState( "application.lang", 'lang' )
));
// Profiling 
JDEBUG ? $_PROFILER->mark('afterInitialise') : null;

/**
 * ROUTE THE APPLICATION
 *
 * NOTE :
 */
$mainframe->route();
// Profiling
JDEBUG ? $_PROFILER->mark('afterRoute') : null;

/**
 * DISPATCH THE APPLICATION
 *
 * NOTE :
 */
$mainframe->dispatch();
// Profiling
JDEBUG ? $_PROFILER->mark('afterDispatch') : null;

/**
 * RENDER THE APPLICATION
 *
 * NOTE :
 */
$mainframe->render();
// Profiling
JDEBUG ? $_PROFILER->mark( 'afterRender' ) : null;

/**
 * RETURN THE RESPONSE
 */
echo JResponse::toString($mainframe->getCfg('gzip'));
