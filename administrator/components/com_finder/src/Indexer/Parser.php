<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Indexer;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;

/**
 * Parser base class for the Finder indexer package.
 *
 * @since  2.5
 */
abstract class Parser
{
    /**
     * Parser support instances container.
     *
     * @var    Parser[]
     * @since  4.0.0
     */
    protected static $instances = array();

    /**
     * Method to get a parser, creating it if necessary.
     *
     * @param   string  $format  The type of parser to load.
     *
     * @return  Parser  A Parser instance.
     *
     * @since   2.5
     * @throws  \Exception on invalid parser.
     */
    public static function getInstance($format)
    {
        $format = InputFilter::getInstance()->clean($format, 'cmd');

        // Only create one parser for each format.
        if (isset(self::$instances[$format])) {
            return self::$instances[$format];
        }

        // Setup the adapter for the parser.
        $class = '\\Joomla\\Component\\Finder\\Administrator\\Indexer\\Parser\\' . ucfirst($format);

        // Check if a parser exists for the format.
        if (class_exists($class)) {
            self::$instances[$format] = new $class();

            return self::$instances[$format];
        }

        // Throw invalid format exception.
        throw new \Exception(Text::sprintf('COM_FINDER_INDEXER_INVALID_PARSER', $format));
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
        if (strlen($input) <= 2048) {
            return $this->process($input);
        }

        // Input is longer than 2Kb so parse it in chunks of 2Kb or less.
        $start = 0;
        $end = strlen($input);
        $chunk = 2048;
        $return = null;

        while ($start < $end) {
            // Setup the string.
            $string = substr($input, $start, $chunk);

            // Find the last space character if we aren't at the end.
            $ls = (($start + $chunk) < $end ? strrpos($string, ' ') : false);

            // Truncate to the last space character.
            if ($ls !== false) {
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
