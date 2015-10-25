<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Application\CBApplication;
// below autoload CB\Legacy\LegacyFoundationFunctions;
use CBLib\Core\AutoLoader;

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

// Check that CBLib with CB Lib is installed:

if ( ! is_readable( JPATH_SITE . '/libraries/CBLib/CB/Application/CBApplication.php' ) )
{
	JFactory::getApplication()->enqueueMessage( "Mandatory Community Builder lib_CBLib not installed!", 'error');
	return false;
}

// Loads the first file:

/** @noinspection PhpIncludeInspection */
include_once JPATH_SITE . '/libraries/CBLib/CB/Application/CBApplication.php';

// Initialize CB Application and Auto-load and initialize everything that was in here:

CBApplication::init()
	->getDI()->get( '\CB\Legacy\LegacyFoundationFunctions' );

// Add the plugins library autoloading path:
AutoLoader::registerLibrary( JPATH_SITE . '/components/com_comprofiler/plugin/libraries/' );

/**
 * The classes that were in here have moved to libraries/CBLib/CB/Legacy folder.
 * The functions in here have moved to libraries/CBLib/CB/Legacy/LegacyFoundationFunctions.php
 */
