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

/**
 * Text Parser class for the Finder indexer package.
 *
 * @since  2.5
 */
class Txt extends Parser
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
