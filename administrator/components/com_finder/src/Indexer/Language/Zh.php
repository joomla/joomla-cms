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
        $terms = parent::tokenise($input);

        // Iterate through the terms and test if they contain Chinese.
        for ($i = 0, $n = count($terms); $i < $n; $i++) {
            $charMatches = array();
            $charCount = preg_match_all('#[\p{Han}]#mui', $terms[$i], $charMatches);

            // Split apart any groups of Chinese characters.
            for ($j = 0; $j < $charCount; $j++) {
                $tSplit = StringHelper::str_ireplace($charMatches[0][$j], '', $terms[$i], false);

                if (!empty($tSplit)) {
                    $terms[$i] = $tSplit;
                } else {
                    unset($terms[$i]);
                }

                $terms[] = $charMatches[0][$j];
            }
        }

        return $terms;
    }
}
