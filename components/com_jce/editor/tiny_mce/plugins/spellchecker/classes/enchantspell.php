<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */

require_once (__DIR__ . '/spellchecker.php');

class enchantspell extends SpellChecker
{
    /**
     * Spellchecks an array of words.
     *
     * @param string $lang  Selected language code (like en_US or de_DE). Shortcodes like "en" and "de" work with enchant >= 1.4.1
     * @param array  $words Array of words to check
     *
     * @return array of misspelled words
     */
    public function &checkWords($lang, $words)
    {
        $r = enchant_broker_init();

        if (enchant_broker_dict_exists($r, $lang)) {
            $d = enchant_broker_request_dict($r, $lang);

            $returnData = array();
            foreach ($words as $key => $value) {
                $correct = enchant_dict_check($d, $value);
                if (!$correct) {
                    $returnData[] = trim($value);
                }
            }

            return $returnData;
            enchant_broker_free_dict($d);
        } else {
            $this->throwError('Language not installed');
        }
        enchant_broker_free($r);
    }

    /**
     * Returns suggestions for a specific word.
     *
     * @param string $lang Selected language code (like en_US or de_DE). Shortcodes like "en" and "de" work with enchant >= 1.4.1
     * @param string $word Specific word to get suggestions for
     *
     * @return array of suggestions for the specified word
     */
    public function &getSuggestions($lang, $word)
    {
        $r = enchant_broker_init();
        $suggs = array();

        if (enchant_broker_dict_exists($r, $lang)) {
            $d = enchant_broker_request_dict($r, $lang);
            $suggs = enchant_dict_suggest($d, $word);

            enchant_broker_free_dict($d);
        } else {
            $this->throwError('Language not installed');
        }
        enchant_broker_free($r);

        return $suggs;
    }
}
