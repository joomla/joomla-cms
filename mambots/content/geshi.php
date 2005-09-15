<?php
/**
* @version $Id: geshi.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onPrepareContent', 'botGeshi' );

/**
* Code Highlighting Mambot
*
* Replaces <pre>...</pre> tags with highlighted text
*/
function botGeshi( $published, &$row, &$params, $page=0 ) {
	require_once( $GLOBALS['mosConfig_absolute_path'] . '/includes/domit/xml_saxy_shared.php' );

	// define the regular expression for the bot
	$regex = "#<pre\s*(.*?)>(.*?)</pre>#s";

	if (!$published) {
		$row->text = preg_replace( $regex, '', $row->text );
		return;
	}

	$GLOBALS['_MAMBOT_GESHI_PARAMS'] =& $params;

	// perform the replacement
	$row->text = preg_replace_callback( $regex, 'botGeshi_replacer', $row->text );

	return true;
}
/**
* Replaces the matched tags an image
* @param array An array of matches (see preg_match_all)
* @return string
*/
function botGeshi_replacer( &$matches ) {
	$params =& $GLOBALS['_MAMBOT_GESHI_PARAMS'];
	include_once( dirname( __FILE__ ) . '/geshi/geshi.php' );

	$args = SAXY_Parser_Base::parseAttributes( $matches[1] );
	$text = $matches[2];

	$lang = mosGetParam( $args, 'lang', 'php' );
	$lines = mosGetParam( $args, 'lines', 'false' );


	$html_entities_match = array( "|\<br \/\>|", "#<#", "#>#", "|&#39;|", '#&quot;#', '#&nbsp;#' );
	$html_entities_replace = array( "\n", '&lt;', '&gt;', "'", '"', ' ' );

	$text = preg_replace( $html_entities_match, $html_entities_replace, $text );

	$text = str_replace('&lt;', '<', $text);
	$text = str_replace('&gt;', '>', $text);
	$text = str_replace('&amp;', '&', $text);
	$text = str_replace( "\t", '  ', $text );

	$geshi = new GeSHi( $text, $lang, dirname( __FILE__ ) . '/geshi/geshi' );
	if ($lines == 'true') {
		$geshi->enable_line_numbers( GESHI_NORMAL_LINE_NUMBERS );
	}
	$text = $geshi->parse_code();

	return $text;
}
?>