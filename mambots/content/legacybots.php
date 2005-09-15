<?php
/**
* @version $Id: legacybots.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onPrepareContent', 'botLegacyBots' );

/**
* Process any legacy bots in the /mambots directory
*
* THIS FILE CAN BE **SAFELY REMOVED** IF YOU HAVE NO LEGACY MAMBOTS
* @param object A content object
* @param int A bit-wise mask of options
* @param int The page number
*/
function botLegacyBots( $published, &$row, &$params, $page=0 ) {
	global $mosConfig_absolute_path;

	// process any legacy bots
	$bots = mosReadDirectory( "$mosConfig_absolute_path/mambots", "\.php$" );
	sort( $bots );
	foreach ($bots as $bot) {
		require "mambots/$bot";
	}
}
?>
