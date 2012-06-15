<?php
/**
 * @package		Joomla.Plugin
 * @subpackage	Content.geshi
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

class plgContentGeshi extends JPlugin
{
	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Simple performance check to determine whether bot should process further.
		if (JString::strpos($article->text, 'pre>') === false) {
			return true;
		}

		// Define the regular expression for the bot.
		$regex = "#<pre xml:\s*(.*?)>(.*?)</pre>#s";

		// Perform the replacement.
		$article->text = preg_replace_callback($regex, array(&$this, '_replace'), $article->text);

		return true;
	}

	/**
	 * Replaces the matched tags.
	 *
	 * @param	array	An array of matches (see preg_match_all)
	 * @return	string
	 */
	protected function _replace(&$matches)
	{
		jimport('joomla.utilities.utility');

		require_once dirname(__FILE__).'/geshi/geshi.php';

		$args = JUtility::parseAttributes($matches[1]);
		$text = $matches[2];

		$lang = JArrayHelper::getValue($args, 'lang', 'php');
		$lines = JArrayHelper::getValue($args, 'lines', 'false');

		$html_entities_match = array("|\<br \/\>|", "#<#", "#>#", "|&#39;|", '#&quot;#', '#&nbsp;#');
		$html_entities_replace = array("\n", '&lt;', '&gt;', "'", '"', ' ');

		$text = preg_replace($html_entities_match, $html_entities_replace, $text);

		$text = str_replace('&lt;', '<', $text);
		$text = str_replace('&gt;', '>', $text);

		$text = str_replace("\t", '  ', $text);

		$geshi = new GeSHi($text, $lang);
		if ($lines == 'true') {
			$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
		}
		$text = $geshi->parse_code();

		return $text;
	}
}
