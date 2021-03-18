<?php

namespace Wamania\Snowball\Stemmer;

use voku\helper\UTF8;

/**
 *
 * @link http://snowball.tartarus.org/algorithms/italian/stemmer.html
 * @author wamania
 *
 */
class Italian extends Stem
{
    /**
     * All Italian vowels
     */
    protected static $vowels = array('a', 'e', 'i', 'o', 'u', 'à', 'è', 'ì', 'ò', 'ù');

    /**
     * {@inheritdoc}
     */
    public function stem($word)
    {
        // we do ALL in UTF-8
        if (!UTF8::is_utf8($word)) {
            throw new \Exception('Word must be in UTF-8');
        }

        $this->plainVowels = implode('', self::$vowels);

        $this->word = UTF8::strtolower($word);

        // First, replace all acute accents by grave accents.
        $this->word = UTF8::str_replace(array('á', 'é', 'í', 'ó', 'ú'), array('à', 'è', 'ì', 'ò', 'ù'), $this->word);

        //And, as in French, put u after q, and u, i between vowels into upper case. (See note on vowel marking.) The vowels are then
        $this->word = preg_replace('#([q])u#u', '$1U', $this->word);
        $this->word = preg_replace('#(['.$this->plainVowels.'])u(['.$this->plainVowels.'])#u', '$1U$2', $this->word);
        $this->word = preg_replace('#(['.$this->plainVowels.'])i(['.$this->plainVowels.'])#u', '$1I$2', $this->word);

        $this->rv();
        $this->r1();
        $this->r2();

        $this->step0();

        $word = $this->word;
        $this->step1();

        //Do step 2 if no ending was removed by step 1.
        if ($word == $this->word) {
            $this->step2();
        }

        $this->step3a();
        $this->step3b();
        $this->finish();

        return $this->word;
    }

    /**
     * Step 0: Attached pronoun
     */
    private function step0()
    {
        // Search for the longest among the following suffixes
        if ( ($position = $this->search(array(
            'gliela', 'gliele', 'glieli', 'glielo', 'gliene',
            'sene', 'mela', 'mele', 'meli', 'melo', 'mene', 'tela', 'tele', 'teli', 'telo', 'tene', 'cela',
            'cele', 'celi', 'celo', 'cene', 'vela', 'vele', 'veli', 'velo', 'vene',
            'gli', 'la', 'le', 'li', 'lo', 'mi', 'ne', 'si', 'ti', 'vi', 'ci'))) !== false) {

            $suffixe = UTF8::substr($this->word, $position);

            // following one of (in RV)
             // a
            $a = array('ando', 'endo');
            $a = array_map(function($item) use ($suffixe) {
                return $item . $suffixe;
            }, $a);
            // In case of (a) the suffix is deleted
            if ($this->searchIfInRv($a) !== false) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }

            //b
            $b = array('ar', 'er', 'ir');
            $b = array_map(function($item) use ($suffixe) {
                return $item . $suffixe;
            }, $b);
            // in case (b) it is replace by e
            if ($this->searchIfInRv($b) !== false) {
                $this->word = preg_replace('#('.$suffixe.')$#u', 'e', $this->word);
            }

            return true;
        }

        return false;
    }

    /**
     * Step 1: Standard suffix removal
     */
    private function step1()
    {
        // amente
        //      delete if in R1
        //      if preceded by iv, delete if in R2 (and if further preceded by at, delete if in R2), otherwise,
        //      if preceded by os, ic or abil, delete if in R2
        if ( ($position = $this->search(array('amente'))) !== false) {
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
            } elseif ( ($position4 = $this->searchIfInR2(array('os', 'ic', 'abil'))) != false) {
                $this->word = UTF8::substr($this->word, 0, $position4);
            }
            return true;
        }

        // delete if in R2
        if ( ($position = $this->search(array(
            'ibili', 'atrice', 'abili', 'abile', 'ibile', 'atrici', 'mente',
            'anza', 'anze', 'iche', 'ichi', 'ismo', 'ismi', 'ista', 'iste', 'isti', 'istà', 'istè', 'istì', 'ante', 'anti',
            'ico', 'ici', 'ica', 'ice', 'oso', 'osi', 'osa', 'ose'
        ))) !== false) {

            if ($this->inR2($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }
            return true;
        }

        // azione   azioni   atore   atori
        //      delete if in R2
        //      if preceded by ic, delete if in R2
        if ( ($position = $this->search(array('azione', 'azioni', 'atore', 'atori'))) !== false) {
            if ($this->inR2($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);

                if ( ($position2 = $this->search(array('ic'))) !== false) {
                    if ($this->inR2($position2)) {
                        $this->word = UTF8::substr($this->word, 0, $position2);
                    }
                }
            }
            return true;
        }

        // logia   logie
        //      replace with log if in R2
        if ( ($position = $this->search(array('logia', 'logie'))) !== false) {
            if ($this->inR2($position)) {
                $this->word = preg_replace('#(logia|logie)$#u', 'log', $this->word);
            }
            return true;
        }

        // uzione   uzioni   usione   usioni
        //      replace with u if in R2
        if ( ($position = $this->search(array('uzione', 'uzioni', 'usione', 'usioni'))) !== false) {
            if ($this->inR2($position)) {
                $this->word = preg_replace('#(uzione|uzioni|usione|usioni)$#u', 'u', $this->word);
            }
            return true;
        }

        // enza   enze
        //      replace with ente if in R2
        if ( ($position = $this->search(array('enza', 'enze'))) !== false) {
            if ($this->inR2($position)) {
                $this->word = preg_replace('#(enza|enze)$#u', 'ente', $this->word);
            }
            return true;
        }

        // amento   amenti   imento   imenti
        //      delete if in RV
        if ( ($position = $this->search(array('amento', 'amenti', 'imento', 'imenti'))) !== false) {
            if ($this->inRv($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }
            return true;
        }

        // ità
        //      delete if in R2
        //      if preceded by abil, ic or iv, delete if in R2
        if ( ($position = $this->search(array('ità'))) !== false) {
            if ($this->inR2($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }

            if ( ($position2 = $this->searchIfInR2(array('abil', 'ic', 'iv'))) != false) {
                $this->word = UTF8::substr($this->word, 0, $position2);
            }
            return true;
        }

        // ivo   ivi   iva   ive
        //      delete if in R2
        //      if preceded by at, delete if in R2 (and if further preceded by ic, delete if in R2)
        if ( ($position = $this->search(array('ivo', 'ivi', 'iva', 'ive'))) !== false) {
            if ($this->inR2($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }

            if ( ($position2 = $this->searchIfInR2(array('at'))) !== false) {
                $this->word = UTF8::substr($this->word, 0, $position2);
                if ( ($position3 = $this->searchIfInR2(array('ic'))) !== false) {
                    $this->word = UTF8::substr($this->word, 0, $position3);
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
            'assimo', 'assero', 'eranno', 'erebbero', 'erebbe', 'eremmo', 'ereste', 'eresti', 'essero', 'iranno', 'irebbero', 'irebbe', 'iremmo',
            'iscano', 'ireste', 'iresti', 'iscono', 'issero',
            'avamo', 'arono', 'avano', 'avate', 'eremo', 'erete', 'erono', 'evamo', 'evano', 'evate', 'ivamo', 'ivano', 'ivate', 'iremo', 'irete', 'irono',
            'ammo', 'ando', 'asse', 'assi', 'emmo', 'enda', 'ende', 'endi', 'endo', 'erai', 'erei', 'Yamo', 'iamo', 'immo', 'irà', 'irai', 'irei',
            'isca', 'isce', 'isci', 'isco',
            'ano', 'are', 'ata', 'ate', 'ati', 'ato', 'ava', 'avi', 'avo', 'erà', 'ere', 'erò', 'ete', 'eva',
            'evi', 'evo', 'ire', 'ita', 'ite', 'iti', 'ito', 'iva', 'ivi', 'ivo', 'ono', 'uta', 'ute', 'uti', 'uto', 'irò', 'ar', 'ir'))) !== false) {

            $this->word = UTF8::substr($this->word, 0, $position);
        }
    }

    /**
     * Step 3a
     * Delete a final a, e, i, o, à, è, ì or ò if it is in RV, and a preceding i if it is in RV
     */
    private function step3a()
    {
        if ($this->searchIfInRv(array('a', 'e', 'i', 'o', 'à', 'è', 'ì', 'ò')) !== false) {
            $this->word = UTF8::substr($this->word, 0, -1);

            if ($this->searchIfInRv(array('i')) !== false) {
                $this->word = UTF8::substr($this->word, 0, -1);
            }
            return true;
        }
        return false;
    }

    /**
     * Step 3b
     * Replace final ch (or gh) with c (or g) if in RV (crocch -> crocc)
     */
    private function step3b()
    {
        if ($this->searchIfInRv(array('ch')) !== false) {
            $this->word = preg_replace('#(ch)$#u', 'c', $this->word);

        } elseif ($this->searchIfInRv(array('gh')) !== false) {
            $this->word = preg_replace('#(gh)$#u', 'g', $this->word);
        }
    }

    /**
     * Finally
     * turn I and U back into lower case
     */
    private function finish()
    {
        $this->word = UTF8::str_replace(array('I', 'U'), array('i', 'u'), $this->word);
    }
}
