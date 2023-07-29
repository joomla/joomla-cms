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
use Joomla\String\StringHelper;

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
        // We first split on whitespace
        $terms = parent::tokenise($input);

        // Iterate through the terms and test if they contain Chinese.
        for ($i = 0, $n = count($terms); $i < $n; $i++) {
            $charMatches = [];
            preg_match_all('#\p{Han}#mui', $terms[$i], $charMatches);

            // No chinese characters found in this term, aborting early.
            if (!count($charMatches[0])) {
                continue;
            }

            // Our term contains chinese words, so we replace those with nothing.
            $tSplit = StringHelper::str_ireplace($charMatches[0], '', $terms[$i], false);

            if (!empty($tSplit)) {
                // A subset of the term is non-chinese and we keep it
                $terms[$i] = $tSplit;
            } else {
                // The term is empty now and we remove it.
                unset($terms[$i]);
            }

            // We now add all found chinese characters as terms. We do this also for duplicates to support the weighing algorithm.
            foreach ($charMatches[0] as $term) {
                $terms[] = $term;
            }
        }

        return $terms;
    }
}
