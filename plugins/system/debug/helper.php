<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for the Joomla! Debug plugin.
 *
 * @since  3.7
 */
abstract class JDebugHelper
{
	/**
	 * Pretty print JSON with colors.
	 *
	 * @param   string  $json  The json raw string.
	 *
	 * @return  string  The json string pretty printed.
	 *
	 * @since   3.5
	 */
	public static function prettyPrintJSON($json = '')
	{
		// In PHP 5.4.0 or later we have pretty print option.
		if (version_compare(PHP_VERSION, '5.4', '>='))
		{
			$json = json_encode($json, JSON_PRETTY_PRINT);
		}

        // Add some colors
        return preg_replace(
            array('#"([^"]+)":#', '#"(|[^"]+)"(\n|\r\n|,)#', '#null,#'),
            array(
                '<span class="black">"</span><span class="green">$1</span><span class="black">"</span>:',
                '<span class="grey">"$1"</span>$2',
                '<span class="blue">null</span>,'
            ),
            $json
        );
	}

	/**
	 * Replaces the Joomla! root with "JROOT" to improve readability.
	 * Formats a link with a special value xdebug.file_link_format
	 * from the php.ini file.
	 *
	 * @param   string  $file  The full path to the file.
	 * @param   string  $line  The line number.
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	public static function formatLink($file, $line = '')
	{
		$linkFormat = ini_get('xdebug.file_link_format');

		$link = str_replace(JPATH_ROOT, 'JROOT', $file);
		$link .= ($line) ? ':' . $line : '';

		if ($linkFormat)
		{
			$href = str_replace(array('%f', '%l'), array($file, $line), $linkFormat);
			$html = '<a href="' . $href . '" rel="noopener noreferrer">' . $link . '</a>';
		}
		else
		{
			$html = $link;
		}

		return $html;
	}

	/**
	 * Simple highlight for SQL queries.
	 *
	 * @param   string  $query  The query to highlight.
	 *
	 * @return  string
	 *
	 * @since   2.5
	 */
	public static function highlightQuery($query)
	{
		$newlineKeywords = '#\b(FROM|LEFT|INNER|OUTER|WHERE|SET|VALUES|ORDER|GROUP|HAVING|LIMIT|ON|AND|CASE)\b#i';

		$query = htmlspecialchars($query, ENT_QUOTES);

		$query = preg_replace($newlineKeywords, '<br />&#160;&#160;\\0', $query);

		$regex = array(

			// Tables are identified by the prefix.
			'/(=)/' => '<b class="dbg-operator">$1</b>',

			// All uppercase words have a special meaning.
			'/(?<!\w|>)([A-Z_]{2,})(?!\w)/x' => '<span class="dbg-command">$1</span>',

			// Tables are identified by the prefix.
			'/(' . JFactory::getDbo()->getPrefix() . '[a-z_0-9]+)/' => '<span class="dbg-table">$1</span>'

		);

		$query = preg_replace(array_keys($regex), array_values($regex), $query);

		$query = str_replace('*', '<b style="color: red;">*</b>', $query);

		return $query;
	}
}
