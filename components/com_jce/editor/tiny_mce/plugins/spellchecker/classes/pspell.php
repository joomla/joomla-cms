<?php

require_once (__DIR__ . '/spellchecker.php');

/**
 * @author Moxiecode
 * @copyright Copyright (c) 2004-2007, Moxiecode Systems AB, All rights reserved
 */
class pspell extends SpellChecker
{
    /**
     * Spellchecks an array of words.
     *
     * @param {String} $lang  Language code like sv or en
     * @param {Array}  $words Array of words to spellcheck
     *
     * @return {Array} Array of misspelled words
     */
    public function &checkWords($lang, $words)
    {
        $plink = $this->_getPLink($lang);

        $outWords = array();
        foreach ($words as $word) {
            if (!pspell_check($plink, trim($word))) {
                $outWords[] = utf8_encode($word);
            }
        }

        return $outWords;
    }

    /**
     * Returns suggestions of for a specific word.
     *
     * @param {String} $lang Language code like sv or en
     * @param {String} $word Specific word to get suggestions for
     *
     * @return {Array} Array of suggestions for the specified word
     */
    public function &getSuggestions($lang, $word)
    {
        $words = pspell_suggest($this->_getPLink($lang), $word);

        for ($i = 0; $i < count($words); ++$i) {
            $words[$i] = utf8_encode($words[$i]);
        }

        return $words;
    }

    /**
     * Opens a link for pspell.
     */
    public function &_getPLink($lang)
    {
        // Check for native PSpell support
        if (!function_exists('pspell_new')) {
            $this->throwError('PSpell support not found in PHP installation.');
        }

        $pspell_config = pspell_config_create(
            $lang,
            $this->_config['PSpell.spelling'],
            $this->_config['PSpell.jargon'],
            $this->_config['PSpell.encoding']
        );

        pspell_config_personal($pspell_config, $this->_config['PSpell.dictionary']);
        $plink = pspell_new_config($pspell_config);

        if (!$plink) {
            $this->throwError('No PSpell link found opened.');
        }

        return $plink;
    }
    /**
     * Add a word to the PSPell personal dictionary
     * From http://slack5.com/blog/2008/12/tinymce-add-to-dictionary/.
     *
     * @param object $lang
     * @param object $word
     *
     * @return
     */
    public function &addToDictionary($lang, $word)
    {
        $plink = $this->_getPLink($lang);
        pspell_add_to_personal($plink, $word);
        pspell_save_wordlist($plink);

        return true;
    }
}
