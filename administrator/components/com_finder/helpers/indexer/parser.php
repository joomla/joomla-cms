<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Parser base class for the Finder indexer package.
 *
 * @since  2.5
 */
abstract class FinderIndexerParser
{
	/**
	 * Method to get a parser, creating it if necessary.
	 *
	 * @param   string  $format  The type of parser to load.
	 *
	 * @return  FinderIndexerParser  A FinderIndexerParser instance.
	 *
	 * @since   2.5
	 * @throws  Exception on invalid parser.
	 */
	public static function getInstance($format)
	{
		static $instances;

		// Only create one parser for each format.
		if (isset($instances[$format]))
		{
			return $instances[$format];
		}

		// Create an array of instances if necessary.
		if (!is_array($instances))
		{
			$instances = array();
		}

		// Setup the adapter for the parser.
		$format = JFilterInput::getInstance()->clean($format, 'cmd');
		$path = __DIR__ . '/parser/' . $format . '.php';
		$class = 'FinderIndexerParser' . ucfirst($format);

		// Check if a parser exists for the format.
		if (!file_exists($path))
		{
			// Throw invalid format exception.
			throw new Exception(JText::sprintf('COM_FINDER_INDEXER_INVALID_PARSER', $format));
		}

		// Instantiate the parser.
		JLoader::register($class, $path);
		$instances[$format] = new $class;

		return $instances[$format];
	}

	/**
	 * Method to parse input and extract the plain text. Because this method is
	 * called from both inside and outside the indexer, it needs to be able to
	 * batch out its parsing functionality to deal with the inefficiencies of
	 * regular expressions. We will parse recursively in 2KB chunks.
	 *
	 * @param   string  $input  The input to parse.
	 *
	 * @return  string  The plain text input.
	 *
	 * @since   2.5
	 */
	public function parse($input)
	{
		// If the input is less than 2KB we can parse it in one go.
		if (strlen($input) <= 2048)
		{
			return $this->process($input);
		}

		// Input is longer than 2Kb so parse it in chunks of 2Kb or less.
		$start = 0;
		$end = strlen($input);
		$chunk = 2048;
		$return = null;

		while ($start < $end)
		{
			// Setup the string.
			$string = substr($input, $start, $chunk);

			// Find the last space character if we aren't at the end.
			$ls = (($start + $chunk) < $end ? strrpos($string, ' ') : false);

			// Truncate to the last space character.
			if ($ls !== false)
			{
				$string = substr($string, 0, $ls);
			}

			// Adjust the start position for the next iteration.
			$start += ($ls !== false ? ($ls + 1 - $chunk) + $chunk : $chunk);

			// Parse the chunk.
			$return .= $this->process($string);
		}

		return $return;
	}

	/**
	 * Method to process input and extract the plain text.
	 *
	 * @param   string  $input  The input to process.
	 *
	 * @return  string  The plain text input.
	 *
	 * @since   2.5
	 */
	abstract protected function process($input);
}
