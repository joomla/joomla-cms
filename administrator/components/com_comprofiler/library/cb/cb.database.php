<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Database\DatabaseDriverInterface;

// no direct access
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * The classes comprofilerDBTable and CBdatabase that were in here have moved to libraries/CBLib/CB/Legacy folder.
 */

/**
 * Here for immense backwards compatibility only
 *
 * @global DatabaseDriverInterface $_CB_Database
 * @deprecated 2.0: Use Application::Database() instead
 */
global $_CB_database;

$_CB_database	=	Application::Database();
