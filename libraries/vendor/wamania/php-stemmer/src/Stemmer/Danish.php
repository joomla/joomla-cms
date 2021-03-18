<?php

namespace Wamania\Snowball\Stemmer;

use voku\helper\UTF8;

/**
 *
 * @link http://snowball.tartarus.org/algorithms/danish/stemmer.html
 * @author wamania
 *
 */
class Danish extends Stem
{
    /**
     * All danish vowels
     */
    protected static $vowels = array('a', 'e', 'i', 'o', 'u', 'y', 'æ', 'å', 'ø');

    /**
     * {@inheritdoc}
     */
    public function stem($word): string
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
        $this->step4();

        return $this->word;
    }

    /**
     * Define a valid s-ending as one of
     * a   b   c   d   f   g   h   j   k   l   m   n   o   p   r   t   v   y   z   å
     *
     * @param string $ending
     * @return boolean
     */
    private function hasValidSEnding($word)
    {
        $lastLetter = UTF8::substr($word, -1, 1);
        return in_array($lastLetter, array('a', 'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 't', 'v', 'y', 'z', 'å'));
    }

    /**
     * Step 1
     * Search for the longest among the following suffixes in R1, and perform the action indicated.
     */
    private function step1()
    {
        // hed   ethed   ered   e   erede   ende   erende   ene   erne   ere   en   heden   eren   er   heder   erer
        // heds   es   endes   erendes   enes   ernes   eres   ens   hedens   erens   ers   ets   erets   et   eret
        //      delete
        if ( ($position = $this->searchIfInR1(array(
            'erendes', 'erende', 'hedens', 'erede', 'ethed', 'heden', 'endes', 'erets', 'heder', 'ernes',
            'erens', 'ered', 'ende', 'erne', 'eres', 'eren', 'eret', 'erer', 'enes', 'heds',
            'ens', 'ene', 'ere', 'ers', 'ets', 'hed', 'es', 'et', 'er', 'en', 'e'
        ))) !== false) {
            $this->word = UTF8::substr($this->word, 0, $position);
            return true;
        }

        // s
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
     * Search for one of the following suffixes in R1, and if found delete the last letter.
     *      gd   dt   gt   kt
     */
    private function step2()
    {
        if ($this->searchIfInR1(array('gd', 'dt', 'gt', 'kt')) !== false) {
            $this->word = UTF8::substr($this->word, 0, -1);
        }
    }

    /**
     * Step 3:
     */
    private function step3()
    {
        // If the word ends igst, remove the final st.
        if ($this->search(array('igst')) !== false) {
            $this->word = UTF8::substr($this->word, 0, -2);
        }

        // Search for the longest among the following suffixes in R1, and perform the action indicated.
        //  ig   lig   elig   els
        //      delete, and then repeat step 2
        if ( ($position = $this->searchIfInR1(array('elig', 'lig', 'ig', 'els'))) !== false) {
            $this->word = UTF8::substr($this->word, 0, $position);
            $this->step2();
            return true;
        }

        //  løst
        //      replace with løs
        if ($this->searchIfInR1(array('løst')) !== false) {
            $this->word = UTF8::substr($this->word, 0, -1);
        }
    }

    /**
     * Step 4: undouble
     * If the word ends with double consonant in R1, remove one of the consonants.
     */
    private function step4()
    {
        $length = UTF8::strlen($this->word);
        if (!$this->inR1(($length-1))) {
            return false;
        }

        $lastLetter = UTF8::substr($this->word, -1, 1);
        if (in_array($lastLetter, self::$vowels)) {
            return false;
        }
        $beforeLastLetter = UTF8::substr($this->word, -2, 1);

        if ($lastLetter == $beforeLastLetter) {
            $this->word = UTF8::substr($this->word, 0, -1);
        }
        return true;
    }
}
