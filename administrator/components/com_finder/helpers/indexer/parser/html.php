<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * HTML Parser class for the Finder indexer package.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
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

		// Deal with spacing issues in the input.
		$input = str_replace('>', '> ', $input);
		$input = str_replace(array('&nbsp;', '&#160;'), ' ', $input);
		$input = trim(preg_replace('#\s+#u', ' ', $input));

		// Strip the tags from the input and decode entities.
		$input = strip_tags($input);
		$input = html_entity_decode($input, ENT_QUOTES, 'UTF-8');
		$input = trim(preg_replace('#\s+#u', ' ', $input));

		return $input;
	}
}
