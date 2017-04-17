<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


// Auto-load and initialize everything that was in here:
/** @see CB\Legacy\LegacyComprofilerFunctions */

\CBLib\Application\Application::DI()->get( 'CB\Legacy\LegacyComprofilerFunctions' );

/**
 * The classes that were in here have moved to libraries/CBLib/CB/Legacy folder.
 * The functions in here have moved to libraries/CBLib/CB/Legacy/LegacyComprofilerFunctions.php
 */
