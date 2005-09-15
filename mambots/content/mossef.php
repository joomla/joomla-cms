<?php
/**
* @version $Id: mossef.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onPrepareContent', 'botMosSef' );

/**
* Converting internal relative links to SEF URLs
*
* <b>Usage:</b>
* <code><a href="...relative link..."></code>
*/
function botMosSef( $published, &$row, &$params, $page=0 ) {
	global $mosConfig_absolute_path, $mosConfig_live_site;

	// define the regular expression for the bot
	$regex = "#href=\"(.*?)\"#is";

	// perform the replacement
	$row->text = preg_replace_callback( $regex, 'botMosSef_replacer', $row->text );

	return true;
}
/**
* Replaces the matched tags
* @param array An array of matches (see preg_match_all)
* @return string
*/
function botMosSef_replacer( &$matches ) {
	if ( substr($matches[1],0,1)=="#" ) {
		// anchor
		$temp = split("index.php", $_SERVER['REQUEST_URI']);
		return 'href="'.sefRelToAbs("index.php".@$temp[1]).$matches[1].'"';
	} else {
		return 'href="'.sefRelToAbs($matches[1]).'"';
	}
}
?>