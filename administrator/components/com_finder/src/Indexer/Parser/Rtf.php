<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Indexer\Parser;

use Joomla\Component\Finder\Administrator\Indexer\Parser;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * RTF Parser class for the Finder indexer package.
 *
 * @since  2.5
 */
class Rtf extends Parser
{
    /**
     * Method to process RTF input and extract the plain text.
     *
     * @param   string  $input  The input to process.
     *
     * @return  string  The plain text input.
     *
     * @since   2.5
     */
    protected function process($input)
    {
        // Remove embedded pictures.
        $input = preg_replace('#{\\\pict[^}]*}#mi', '', $input);

        // Remove control characters.
        $input = str_replace(['{', '}', "\\\n"], [' ', ' ', "\n"], $input);
        $input = preg_replace('#\\\([^;]+?);#m', ' ', $input);
        $input = preg_replace('#\\\[\'a-zA-Z0-9]+#mi', ' ', $input);

        return $input;
    }
}
