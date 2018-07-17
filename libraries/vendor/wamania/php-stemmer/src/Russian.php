<?php
namespace Wamania\Snowball;

/**
 *
 * @link http://snowball.tartarus.org/algorithms/russian/stemmer.html
 * @author wamania
 *
 */
class Russian extends Stem
{
    /**
     * All russian vowels
     */
    protected static $vowels = array('а', 'е', 'и', 'о', 'у', 'ы', 'э', 'ю', 'я');

    protected static $perfectiveGerund = array(
        array('вшись', 'вши', 'в'),
        array('ывшись', 'ившись', 'ывши', 'ивши', 'ив', 'ыв')
    );

    protected static $adjective = array(
        'ыми', 'ими', 'ему', 'ому', 'его', 'ого', 'ее', 'ие', 'ые', 'ое', 'ей', 'ий',
        'ый', 'ой', 'ем', 'им', 'ым','ом','их', 'ых', 'ую', 'юю', 'ая', 'яя', 'ою', 'ею'
    );

    protected static $participle = array(
        array('ем', 'нн', 'вш', 'ющ', 'щ'),
        array('ивш', 'ывш', 'ующ')
    );

    protected static $reflexive = array('ся', 'сь');

    protected static $verb = array(
        array('ешь', 'нно', 'ете', 'йте', 'ла', 'на', 'ли', 'й', 'л', 'ем', 'н', 'ло', 'но', 'ет', 'ют', 'ны', 'ть'),
        array(
            'уйте', 'ило', 'ыло', 'ено','ила', 'ыла', 'ена', 'ейте', 'ены', 'ить', 'ыть', 'ишь', 'ите', 'или', 'ыли',
            'ует', 'уют', 'ей', 'уй', 'ил', 'ыл', 'им', 'ым', 'ен', 'ят', 'ит', 'ыт', 'ую', 'ю'
        )
    );

    protected static $noun = array(
        'иями', 'ями', 'ами', 'ией', 'иям', 'ием', 'иях', 'ев', 'ов', 'ие', 'ье', 'еи', 'ии', 'ей', 'ой', 'ий', 'ям',
        'ем', 'ам', 'ом', 'ах', 'ях', 'ию', 'ью', 'ия', 'ья', 'я', 'а', 'е', 'ы', 'ь', 'и', 'о', 'у', 'й', 'ю'
    );

    protected static $superlative = array('ейше', 'ейш');

    protected static $derivational = array('ость', 'ост');

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
        $this->r2();
        $this->rv();

        // Do each of steps 1, 2 3 and 4.
        $this->step1();
        $this->step2();
        $this->step3();
        $this->step4();

        return $this->word;
    }

    /**
     * Step 1: Search for a PERFECTIVE GERUND ending. If one is found remove it, and that is then the end of step 1.
     * Otherwise try and remove a REFLEXIVE ending, and then search in turn for (1) an ADJECTIVAL, (2) a VERB or (3) a NOUN ending.
     * As soon as one of the endings (1) to (3) is found remove it, and terminate step 1.
     */
    public function step1()
    {
        // Search for a PERFECTIVE GERUND ending.
        // group 1
        if ( ($position = $this->searchIfInRv(self::$perfectiveGerund[0])) !== false) {
            if ( ($this->inRv($position)) && ($this->checkGroup1($position)) ) {
                $this->word = Utf8::substr($this->word, 0, $position);
                return true;
            }
        }

        // group 2
        if ( ($position = $this->searchIfInRv(self::$perfectiveGerund[1])) !== false) {
            if ($this->inRv($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);
                return true;
            }
        }

        // Otherwise try and remove a REFLEXIVE ending
        if ( ($position = $this->searchIfInRv(self::$reflexive)) !== false) {
            if ($this->inRv($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);
            }
        }

        // then search in turn for (1) an ADJECTIVAL, (2) a VERB or (3) a NOUN ending.
        // As soon as one of the endings (1) to (3) is found remove it, and terminate step 1.
        if ( ($position = $this->searchIfInRv(self::$adjective)) !== false) {
            if ($this->inRv($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);

                if ( ($position2 = $this->search(self::$participle[0])) !== false) {
                    if ( ($this->inRv($position2)) && ($this->checkGroup1($position2)) ) {
                        $this->word = Utf8::substr($this->word, 0, $position2);
                        return true;
                    }
                }

                if ( ($position2 = $this->search(self::$participle[1])) !== false) {
                    if ($this->inRv($position2)) {
                        $this->word = Utf8::substr($this->word, 0, $position2);
                        return true;
                    }
                }

                return true;
            }
        }

        if ( ($position = $this->searchIfInRv(self::$verb[0])) !== false) {
            if ( ($this->inRv($position)) && ($this->checkGroup1($position)) ) {
                $this->word = Utf8::substr($this->word, 0, $position);
                return true;
            }
        }

        if ( ($position = $this->searchIfInRv(self::$verb[1])) !== false) {
            if ($this->inRv($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);
                return true;
            }
        }

        if ( ($position = $this->searchIfInRv(self::$noun)) !== false) {
            if ($this->inRv($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);
                return true;
            }
        }

        return false;
    }

    /**
     * Step 2: If the word ends with и (i), remove it.
     */
    public function step2()
    {
        if ( ($position = $this->searchIfInRv(array('и'))) !== false) {
            if ($this->inRv($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);
                return true;
            }
        }
        return false;
    }

    /**
     * Step 3: Search for a DERIVATIONAL ending in R2 (i.e. the entire ending must lie in R2),
     * and if one is found, remove it.
     */
    public function step3()
    {
        if ( ($position = $this->searchIfInRv(self::$derivational)) !== false) {
            if ($this->inR2($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);
                return true;
            }
        }
    }

    /**
     *  Step 4: (1) Undouble н (n), or, (2) if the word ends with a SUPERLATIVE ending, remove it
     *  and undouble н (n), or (3) if the word ends ь (') (soft sign) remove it.
     */
    public function step4()
    {
        // (2) if the word ends with a SUPERLATIVE ending, remove it
        if ( ($position = $this->searchIfInRv(self::$superlative)) !== false) {
            $this->word = Utf8::substr($this->word, 0, $position);
        }

        // (1) Undouble н (n)
        if ( ($position = $this->searchIfInRv(array('нн'))) !== false) {
            $this->word = Utf8::substr($this->word, 0, ($position+1));
            return true;
        }

        // (3) if the word ends ь (') (soft sign) remove it
        if ( ($position = $this->searchIfInRv(array('ь'))) !== false) {
            $this->word = Utf8::substr($this->word, 0, $position);
            return true;
        }
    }

    /**
     *  In any word, RV is the region after the first vowel, or the end of the word if it contains no vowel.
     */
    protected function rv()
    {
        $length = Utf8::strlen($this->word);

        $this->rv = '';
        $this->rvIndex = $length;

        for ($i=0; $i<$length; $i++) {
            $letter = Utf8::substr($this->word, $i, 1);
            if (in_array($letter, self::$vowels)) {
                $this->rv = Utf8::substr($this->word, ($i+1));
                $this->rvIndex = $i + 1;
                return true;
            }
        }

        return false;
    }

    /**
     * group 1 endings must follow а (a) or я (ia)
     *
     * @param integer $position
     * @return boolean
     */
    private function checkGroup1($position)
    {
        if (! $this->inRv(($position-1))) {
            return false;
        }

        $letter = Utf8::substr($this->word, ($position - 1), 1);

        if ($letter == 'а' || $letter == 'я') {
            return true;
        }
        return false;
    }
}
