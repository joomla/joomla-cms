<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::register('SearchIndexerParser', dirname(__DIR__) . '/parser.php');

/**
 * Text Parser class for the Search indexer package.
 *
 * @since  2.5
 */
class SearchIndexerParserTxt extends SearchIndexerParser
{
	/**
	 * Method to process Text input and extract the plain text.
	 *
	 * @param   string  $input  The input to process.
	 *
	 * @return  string  The plain text input.
	 *
	 * @since   2.5
	 */
	public function parse($input)
	{
		return $input;
	}
}
