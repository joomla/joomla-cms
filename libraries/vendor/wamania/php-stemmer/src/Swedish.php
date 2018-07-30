<?php
namespace Wamania\Snowball;

/**
 *
 * @link http://snowball.tartarus.org/algorithms/swedish/stemmer.html
 * @author wamania
 *
 */
class Swedish extends Stem
{
    /**
     * All swedish vowels
     */
    protected static $vowels = array('a', 'e', 'i', 'o', 'u', 'y', 'ä', 'å', 'ö');

    /**
     * {@inheritdoc}
     */
    public function stem($word)
    {
        // we do ALL in UTF-8
        if (! Utf8::check($word)) {
            throw new \Exception('Word must be in UTF-8');
        }

        $this->word = Utf8::strtolower($word);

        // R2 is not used: R1 is defined in the same way as in the German stemmer
        $this->r1();

        // then R1 is adjusted so that the region before it contains at least 3 letters.
        if ($this->r1Index < 3) {
            $this->r1Index = 3;
            $this->r1 = Utf8::substr($this->word, 3);
        }

        // Do each of steps 1, 2 3 and 4.
        $this->step1();
        $this->step2();
        $this->step3();

        return $this->word;
    }

    /**
     * Define a valid s-ending as one of
     * b   c   d   f   g   h   j   k   l   m   n   o   p   r   t   v   y
     *
     * @param string $ending
     * @return boolean
     */
    private function hasValidSEnding($word)
    {
        $lastLetter = Utf8::substr($word, -1, 1);
        return in_array($lastLetter, array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 't', 'v', 'y'));
    }

    /**
     * Step 1
     * Search for the longest among the following suffixes in R1, and perform the action indicated.
     */
    private function step1()
    {
        // a   arna   erna   heterna   orna   ad   e   ade   ande   arne   are   aste   en   anden   aren   heten
        // ern   ar   er   heter   or   as   arnas   ernas   ornas   es   ades   andes   ens   arens   hetens
        // erns   at   andet   het   ast
        //      delete
        if ( ($position = $this->searchIfInR1(array(
            'heterna', 'hetens', 'ornas', 'andes', 'arnas', 'heter', 'ernas', 'anden', 'heten', 'andet', 'arens',
            'orna', 'arna', 'erna', 'aren', 'ande', 'ades', 'arne', 'erns', 'aste', 'ade', 'ern', 'het',
            'ast', 'are', 'ens', 'or', 'es', 'ad', 'en', 'at', 'ar', 'as', 'er', 'a', 'e'
        ))) !== false) {
            $this->word = Utf8::substr($this->word, 0, $position);
            return true;
        }

        //  s
        //      delete if preceded by a valid s-ending
        if ( ($position = $this->searchIfInR1(array('s'))) !== false) {
            $word = Utf8::substr($this->word, 0, $position);
            if ($this->hasValidSEnding($word)) {
                $this->word = $word;
            }
        }
    }

    /**
     * Step 2
     * Search for one of the following suffixes in R1, and if found delete the last letter.
     */
    private function step2()
    {
        // dd   gd   nn   dt   gt   kt   tt
        if ($this->searchIfInR1(array('dd', 'gd', 'nn', 'dt', 'gt', 'kt', 'tt')) !== false) {
            $this->word = Utf8::substr($this->word, 0, -1);
        }
    }

    /**
     * Step 3:
     * Search for the longest among the following suffixes in R1, and perform the action indicated.
     */
    private function step3()
    {
        // lig   ig   els
        //      delete
        if ( ($position = $this->searchIfInR1(array('lig', 'ig', 'els'))) !== false) {
            $this->word = Utf8::substr($this->word, 0, $position);
            return true;
        }

        // löst
        //      replace with lös
        if ( ($this->searchIfInR1(array('löst'))) !== false) {
            $this->word = Utf8::substr($this->word, 0, -1);
            return true;
        }

        // fullt
        //      replace with full
        if ( ($this->searchIfInR1(array('fullt'))) !== false) {
            $this->word = Utf8::substr($this->word, 0, -1);
            return true;
        }
    }
}
