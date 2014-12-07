<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::register('FinderIndexerParser', dirname(__DIR__) . '/parser.php');

/**
 * HTML Parser class for the Finder indexer package.
 *
 * @since  2.5
 */
class FinderIndexerParserHtml extends FinderIndexerParser
{
	/**
	 * Method to process HTML input and extract the plain text.
	 *
	 * @param   string  $input  The input to process.
	 *
	 * @return  string  The plain text input.
	 *
	 * @since   2.5
	 */
	protected function process($input)
	{
		// Strip invalid UTF-8 characters.
		$input = iconv("utf-8", "utf-8//IGNORE", $input);

		// Strip all script tags.
		$input = preg_replace('#<script[^>]*>.*?</script>#si', ' ', $input);

		// Strip the tags from the input
		$input = strip_tags(str_replace('>', '> ', $input));

		// Deal with spacing issues in the input
		$input = str_replace(array('&nbsp;', '&#160;'), ' ', $input);
		$input = trim(preg_replace('#\s+#u', ' ', $input));

		// Remove last parts of HTML code which may be caused by a cut of the string
		if (strpos($input, '>') !== false)
		{
			$input = substr($input, strpos($input, '>') + 1);
		}

		if (strpos($input, '<') !== false)
		{
			$input = substr($input, 0, strpos($input, '<'));
		}

		// Decode entities and remove unneeded white spaces
		$input = html_entity_decode($input, ENT_QUOTES, 'UTF-8');
		$input = trim(preg_replace('#\s+#u', ' ', $input));

		return $input;
	}
}
