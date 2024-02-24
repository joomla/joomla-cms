<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Indexer\Language;

use Joomla\Component\Finder\Administrator\Indexer\Language;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Chinese (simplified) language support class for the Finder indexer package.
 *
 * @since  4.0.0
 */
class Zh extends Language
{
    /**
     * Language locale of the class
     *
     * @var    string
     * @since  4.0.0
     */
    public $language = 'zh';

    /**
     * Spacer between terms
     *
     * @var    string
     * @since  4.0.0
     */
    public $spacer = '';

    /**
     * Method to construct the language object.
     *
     * @since   4.0.0
     */
    public function __construct($locale = null)
    {
        // Override parent constructor since we don't need to load an external stemmer
    }

    /**
     * Method to tokenise a text string.
     *
     * @param   string  $input  The input to tokenise.
     *
     * @return  array  An array of term strings.
     *
     * @since   4.0.0
     */
    public function tokenise($input)
    {
        // We first add whitespace around each Chinese character, so that our later code can easily split on this.
        $input = preg_replace('#\p{Han}#mui', ' $0 ', $input);

        // Now we split up the input into individual terms
        $terms = parent::tokenise($input);

        return $terms;
    }
}
