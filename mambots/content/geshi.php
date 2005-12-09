<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onPrepareContent', 'botGeshi' );

/**
* Code Highlighting Mambot
*
* Replaces <pre>...</pre> tags with highlighted text
*/
function botGeshi( $published, &$row, &$params, $page=0 ) {

	// check whether mambot has been unpublished
	if ( !$published ) {
		return true;
	}

	// define the regular expression for the bot
	$regex = "#<pre\s*(.*?)>(.*?)</pre>#s";

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

	jimport('domit.xml_saxy_shared');
	$args = SAXY_Parser_Base::parseAttributes( $matches[1] );
	$text = $matches[2];

	$lang = mosGetParam( $args, 'lang', 'php' );
	$lines = mosGetParam( $args, 'lines', 'false' );


	$html_entities_match = array( "|\<br \/\>|", "#<#", "#>#", "|&#39;|", '#&quot;#', '#&nbsp;#' );
	$html_entities_replace = array( "\n", '&lt;', '&gt;', "'", '"', ' ' );

	$text = preg_replace( $html_entities_match, $html_entities_replace, $text );

	$text = str_replace('&lt;', '<', $text);
	$text = str_replace('&gt;', '>', $text);

/*
	// Replace 2 spaces with "&nbsp; " so non-tabbed code indents without making huge long lines.
	$text = str_replace("  ", "&nbsp; ", $text);
	// now Replace 2 spaces with " &nbsp;" to catch odd #s of spaces.
	$text = str_replace("  ", " &nbsp;", $text);
*/
	// Replace tabs with "&nbsp; &nbsp;" so tabbed code indents sorta right without making huge long lines.
	//$text = str_replace("\t", "&nbsp; &nbsp;", $text);
	$text = str_replace( "\t", '  ', $text );

	$geshi = new GeSHi( $text, $lang, dirname( __FILE__ ) . '/geshi/geshi' );
	if ($lines == 'true') {
		$geshi->enable_line_numbers( GESHI_NORMAL_LINE_NUMBERS );
	}
	$text = $geshi->parse_code();

	return $text;
}
?>