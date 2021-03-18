<?php

namespace Wamania\Snowball\Stemmer;

use voku\helper\UTF8;

/**
 *
 * @link http://snowball.tartarus.org/algorithms/portuguese/stemmer.html
 * @author wamania
 *
 */
class Portuguese extends Stem
{
    /**
     * All Portuguese vowels
     */
    protected static $vowels = array('a', 'e', 'i', 'o', 'u', 'á', 'é', 'í', 'ó', 'ú', 'â', 'ê', 'ô');

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

        $this->word = UTF8::str_replace(array('ã', 'õ'), array('a~', 'o~'), $this->word);

        $this->rv();
        $this->r1();
        $this->r2();

        $word = $this->word;
        $this->step1();

        if ($word == $this->word) {
            $this->step2();
        }

        if ($word != $this->word) {
            $this->step3();
        } else {
            $this->step4();
        }

        $this->step5();
        $this->finish();

        return $this->word;
    }

    /**
     * Step 1: Standard suffix removal
     */
    private function step1()
    {
        // delete if in R2
        if ( ($position = $this->search(array(
            'amentos', 'imentos', 'adoras', 'adores', 'amento', 'imento', 'adora', 'istas', 'ismos', 'antes', 'ância',
            'ezas', 'eza', 'icos', 'icas', 'ismo', 'ável', 'ível', 'ista', 'oso',
            'osos', 'osas', 'osa', 'ico', 'ica', 'ador', 'aça~o', 'aço~es' , 'ante'))) !== false) {

            if ($this->inR2($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }
            return true;
        }

        // logía   logías
        //      replace with log if in R2
        if ( ($position = $this->search(array('logías', 'logía'))) !== false) {
            if ($this->inR2($position)) {
                $this->word = preg_replace('#(logías|logía)$#u', 'log', $this->word);
            }
            return true;
        }

        // ución   uciones
        //      replace with u if in R2
        if ( ($position = $this->search(array('uciones', 'ución'))) !== false) {
            if ($this->inR2($position)) {
                $this->word = preg_replace('#(uciones|ución)$#u', 'u', $this->word);
            }
            return true;
        }

        // ência    ências
        //      replace with ente if in R2
        if ( ($position = $this->search(array('ências', 'ência'))) !== false) {
            if ($this->inR2($position)) {
                $this->word = preg_replace('#(ências|ência)$#u', 'ente', $this->word);
            }
            return true;
        }

        // amente
        //      delete if in R1
        //      if preceded by iv, delete if in R2 (and if further preceded by at, delete if in R2), otherwise,
        //      if preceded by os, ic or ad, delete if in R2
        if ( ($position = $this->search(array('amente'))) !== false) {

            // delete if in R1
            if ($this->inR1($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }

            // if preceded by iv, delete if in R2 (and if further preceded by at, delete if in R2), otherwise,
            if ( ($position2 = $this->searchIfInR2(array('iv'))) !== false) {
                $this->word = UTF8::substr($this->word, 0, $position2);
                if ( ($position3 = $this->searchIfInR2(array('at'))) !== false) {
                    $this->word = UTF8::substr($this->word, 0, $position3);
                }

                // if preceded by os, ic or ad, delete if in R2
            } elseif ( ($position4 = $this->searchIfInR2(array('os', 'ic', 'ad'))) !== false) {
                $this->word = UTF8::substr($this->word, 0, $position4);
            }
            return true;
        }

        // mente
        //      delete if in R2
        //      if preceded by ante, avel or ível, delete if in R2
        if ( ($position = $this->search(array('mente'))) !== false) {

            // delete if in R2
            if ($this->inR2($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }

            // if preceded by ante, avel or ível, delete if in R2
            if ( ($position2 = $this->searchIfInR2(array('ante', 'avel', 'ível'))) != false) {
                $this->word = UTF8::substr($this->word, 0, $position2);
            }
            return true;
        }

        // idade   idades
        //      delete if in R2
        //      if preceded by abil, ic or iv, delete if in R2
        if ( ($position = $this->search(array('idades', 'idade'))) !== false) {

            // delete if in R2
            if ($this->inR2($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }

            // if preceded by abil, ic or iv, delete if in R2
            if ( ($position2 = $this->searchIfInR2(array('abil', 'ic', 'iv'))) !== false) {
                $this->word = UTF8::substr($this->word, 0, $position2);
            }
            return true;
        }

        // iva   ivo   ivas   ivos
        //      delete if in R2
        //      if preceded by at, delete if in R2
        if ( ($position = $this->search(array('ivas', 'ivos', 'iva', 'ivo'))) !== false) {

            // delete if in R2
            if ($this->inR2($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }

            // if preceded by at, delete if in R2
            if ( ($position2 = $this->searchIfInR2(array('at'))) !== false) {
                $this->word = UTF8::substr($this->word, 0, $position2);
            }
            return true;
        }

        // ira   iras
        //      replace with ir if in RV and preceded by e
        if ( ($position = $this->search(array('iras', 'ira'))) !== false) {

            if ($this->inRv($position)) {
                $before = $position -1;
                $letter = UTF8::substr($this->word, $before, 1);

                if ($letter == 'e') {
                    $this->word = preg_replace('#(iras|ira)$#u', 'ir', $this->word);
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Step 2: Verb suffixes
     * Search for the longest among the following suffixes in RV, and if found, delete.
     */
    private function step2()
    {
        if ( ($position = $this->searchIfInRv(array(
            'aríamos', 'eríamos', 'iríamos', 'ássemos', 'êssemos', 'íssemos',
            'aríeis', 'eríeis', 'iríeis', 'ásseis', 'ésseis', 'ísseis', 'áramos', 'éramos', 'íramos', 'ávamos',
            'aremos', 'eremos', 'iremos',
            'ariam', 'eriam', 'iriam', 'assem', 'essem', 'issem', 'arias', 'erias', 'irias', 'ardes', 'erdes', 'irdes',
            'asses', 'esses', 'isses', 'astes', 'estes', 'istes', 'áreis', 'areis', 'éreis', 'ereis', 'íreis', 'ireis',
            'áveis', 'íamos', 'armos', 'ermos', 'irmos',
            'aria', 'eria', 'iria', 'asse', 'esse', 'isse', 'aste', 'este', 'iste', 'arei', 'erei', 'irei', 'adas', 'idas',
            'aram', 'eram', 'iram', 'avam', 'arem', 'erem', 'irem', 'ando', 'endo', 'indo', 'ara~o', 'era~o', 'ira~o',
            'arás', 'aras', 'erás', 'eras', 'irás', 'avas', 'ares', 'eres', 'ires', 'íeis', 'ados', 'idos', 'ámos', 'amos',
            'emos', 'imos', 'iras',
            'ada', 'ida', 'ará', 'ara', 'erá', 'era', 'irá', 'ava', 'iam', 'ado', 'ido', 'ias', 'ais', 'eis', 'ira',
            'ia', 'ei', 'am', 'em', 'ar', 'er', 'ir', 'as', 'es', 'is', 'eu', 'iu', 'ou',
        ))) !== false) {

            $this->word = UTF8::substr($this->word, 0, $position);
            return true;
        }
        return false;
    }

    /**
     * Step 3: d-suffixes
     *
     */
    private function step3()
    {
        // Delete suffix i if in RV and preceded by c
        if ($this->searchIfInRv(array('i')) !== false) {
            $letter = UTF8::substr($this->word, -2, 1);

            if ($letter == 'c') {
                $this->word = UTF8::substr($this->word, 0, -1);
            }
            return true;
        }
        return false;
    }

    /**
     * Step 4
     */
    private function step4()
    {
        // If the word ends with one of the suffixes "os   a   i   o   á   í   ó" in RV, delete it
        if ( ($position = $this->searchIfInRv(array('os', 'a', 'i', 'o','á', 'í', 'ó'))) !== false) {
            $this->word = UTF8::substr($this->word, 0, $position);
            return true;
        }
        return false;
    }

    /**
     * Step 5
     */
    private function step5()
    {
        // If the word ends with one of "e   é   ê" in RV, delete it, and if preceded by gu (or ci) with the u (or i) in RV, delete the u (or i).
        if ($this->searchIfInRv(array('e', 'é', 'ê')) !== false) {
            $this->word = UTF8::substr($this->word, 0, -1);

            if ( ($position2 = $this->search(array('gu', 'ci'))) !== false) {
                if ($this->inRv(($position2+1))) {
                    $this->word = UTF8::substr($this->word, 0, -1);
                }
            }
            return true;
        } else if ($this->search(array('ç')) !== false) {
            $this->word = preg_replace('#(ç)$#u', 'c', $this->word);
            return true;
        }
        return false;
    }

    /**
     * Finally
     */
    private function finish()
    {
        // turn U and Y back into lower case, and remove the umlaut accent from a, o and u.
        $this->word = UTF8::str_replace(array('a~', 'o~'), array('ã', 'õ'), $this->word);
    }
}
