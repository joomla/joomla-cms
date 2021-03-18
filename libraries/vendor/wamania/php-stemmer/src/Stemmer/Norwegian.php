<?php

namespace Wamania\Snowball\Stemmer;

use voku\helper\UTF8;

/**
 *
 * @link http://snowball.tartarus.org/algorithms/norwegian/stemmer.html
 * @author wamania
 *
 */
class Norwegian extends Stem
{
    /**
     * All norwegian vowels
     */
    protected static $vowels = array('a', 'e', 'i', 'o', 'u', 'y', 'æ', 'å', 'ø');

    /**
     * {@inheritdoc}
     */
    public function stem($word)
    {
        // we do ALL in UTF-8
        if (!UTF8::is_utf8($word)) {
            throw new \Exception('Word must be in UTF-8');
        }

        $this->word = UTF8::strtolower($word);

        // R2 is not used: R1 is defined in the same way as in the German stemmer
        $this->r1();

        // then R1 is adjusted so that the region before it contains at least 3 letters.
        if ($this->r1Index < 3) {
            $this->r1Index = 3;
            $this->r1 = UTF8::substr($this->word, 3);
        }

        // Do each of steps 1, 2 3 and 4.
        $this->step1();
        $this->step2();
        $this->step3();

        return $this->word;
    }

    /**
     * Define a valid s-ending as one of
     * b   c   d   f   g   h   j   l   m   n   o   p   r   t   v   y   z,
     * or k not preceded by a vowel
     *
     * @param string $ending
     * @return boolean
     */
    private function hasValidSEnding($word)
    {
        $lastLetter = UTF8::substr($word, -1, 1);
        if (in_array($lastLetter, array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'l', 'm', 'n', 'o', 'p', 'r', 't', 'v', 'y', 'z'))) {
            return true;
        }
        if ($lastLetter == 'k') {
            $beforeLetter = UTF8::substr($word, -2, 1);
            if (!in_array($beforeLetter, self::$vowels)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Step 1
     * Search for the longest among the following suffixes in R1, and perform the action indicated.
     */
    private function step1()
    {
        //  erte   ert
        //      replace with er
        if ( ($position = $this->searchIfInR1(array('erte', 'ert'))) !== false) {
            $this->word = preg_replace('#(erte|ert)$#u', 'er', $this->word);
            return true;
        }

         // a   e   ede   ande   ende   ane   ene   hetene   en   heten   ar   er   heter   as   es   edes   endes   enes   hetenes   ens   hetens   ers   ets   et   het   ast
        //      delete
        if ( ($position = $this->searchIfInR1(array(
            'hetenes', 'hetene', 'hetens', 'heten', 'endes', 'heter', 'ande', 'ende', 'enes', 'edes', 'ede', 'ane',
            'ene', 'het', 'ers', 'ets', 'ast', 'ens', 'en', 'ar', 'er', 'as', 'es', 'et', 'a', 'e'
        ))) !== false) {
            $this->word = UTF8::substr($this->word, 0, $position);
            return true;
        }

        //  s
        //      delete if preceded by a valid s-ending
        if ( ($position = $this->searchIfInR1(array('s'))) !== false) {
            $word = UTF8::substr($this->word, 0, $position);
            if ($this->hasValidSEnding($word)) {
                $this->word = $word;
            }
            return true;
        }
    }

    /**
     * Step 2
     * If the word ends dt or vt in R1, delete the t.
     */
    private function step2()
    {
        if ($this->searchIfInR1(array('dt', 'vt')) !== false) {
            $this->word = UTF8::substr($this->word, 0, -1);
        }
    }

    /**
     * Step 3:
     * Search for the longest among the following suffixes in R1, and if found, delete.
     */
    private function step3()
    {
        // leg   eleg   ig   eig   lig   elig   els   lov   elov   slov   hetslov
        if ( ($position = $this->searchIfInR1(array(
            'hetslov', 'eleg', 'elov', 'slov', 'elig', 'eig', 'lig', 'els', 'lov', 'leg', 'ig'
        ))) !== false) {
            $this->word = UTF8::substr($this->word, 0, $position);
        }
    }
}
