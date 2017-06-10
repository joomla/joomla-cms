<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('FinderIndexerParser', dirname(__DIR__) . '/parser.php');

/**
 * Text Parser class for the Finder indexer package.
 *
 * @since  2.5
 */
class FinderIndexerParserTxt extends FinderIndexerParser
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
	protected function process($input)
	{
		return $input;
	}
}
