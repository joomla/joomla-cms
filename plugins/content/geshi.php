<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$mainframe->registerEvent('onPrepareContent', 'plgContentGeshi');

/**
* Code Highlighting Plugin
*
* Replaces <pre>...</pre> tags with highlighted text
 */
function plgContentGeshi(&$row, &$params, $page=0)
{
	// simple performance check to determine whether bot should process further
	if (JString::strpos($row->text, 'pre>') === false) {
		return true;
	}

	// Get Plugin info
 	$plugin = &JPluginHelper::getPlugin('content', 'geshi');

	// define the regular expression for the bot
	$regex = "#<pre xml:\s*(.*?)>(.*?)</pre>#s";

	$GLOBALS['_MAMBOT_GESHI_PARAMS'] = &$params;

	// perform the replacement
	$row->text = preg_replace_callback($regex, 'plgContentGeshi_replacer', $row->text);

	return true;
}
/**
* Replaces the matched tags an image
* @param array An array of matches (see preg_match_all)
* @return string
 */
function plgContentGeshi_replacer(&$matches)
{
	$params = &$GLOBALS['_MAMBOT_GESHI_PARAMS'];

	require_once (dirname(__FILE__).'/geshi/geshi.php');

	jimport('joomla.utilities.utility');

	$args = JUtility::parseAttributes($matches[1]);
	$text = $matches[2];

	$lang	= JArrayHelper::getValue($args, 'lang', 'php');
	$lines	= JArrayHelper::getValue($args, 'lines', 'false');


	$html_entities_match = array("|\<br \/\>|", "#<#", "#>#", "|&#39;|", '#&quot;#', '#&nbsp;#');
	$html_entities_replace = array("\n", '&lt;', '&gt;', "'", '"', ' ');

	$text = preg_replace($html_entities_match, $html_entities_replace, $text);

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
	$text = str_replace("\t", '  ', $text);

	$geshi = new GeSHi($text, $lang);
	if ($lines == 'true') {
		$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
	}
	$text = $geshi->parse_code();

	return $text;
}