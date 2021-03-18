<?php

namespace Wamania\Snowball\Stemmer;

use voku\helper\UTF8;

/**
 *
 * @link http://snowball.tartarus.org/algorithms/romanian/stemmer.html
 * @author wamania
 *
 */
class Romanian extends Stem
{
    /**
     * All Romanian vowels
     */
    protected static $vowels = array('a', 'ă', 'â', 'e', 'i', 'î', 'o', 'u');

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

        $this->plainVowels = implode('', self::$vowels);

        //  First, i and u between vowels are put into upper case (so that they are treated as consonants).
        $this->word = preg_replace('#(['.$this->plainVowels.'])u(['.$this->plainVowels.'])#u', '$1U$2', $this->word);
        $this->word = preg_replace('#(['.$this->plainVowels.'])i(['.$this->plainVowels.'])#u', '$1I$2', $this->word);

        $this->rv();
        $this->r1();
        $this->r2();

        $this->step0();

        $word1 = $this->word;
        $word2 = $this->word;

        do {
            $word1 = $this->word;
            $this->step1();
        } while ($this->word != $word1);

        $this->step2();

        // Do step 3 if no suffix was removed either by step 1 or step 2.
        if ($word2 == $this->word) {
            $this->step3();
        }

        $this->step4();
        $this->finish();

        return $this->word;
    }

    /**
     * Step 0: Removal of plurals (and other simplifications)
     * Search for the longest among the following suffixes, and, if it is in R1, perform the action indicated.
     * @return boolean
     */
    private function step0()
    {
        // ul   ului
        //      delete
        if ( ($position = $this->search(array('ul', 'ului'))) !== false) {
            if ($this->inR1($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }
            return true;
        }

        // aua
        //      replace with a
        if ( ($position = $this->search(array('aua'))) !== false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(aua)$#u', 'a', $this->word);
            }
            return true;
        }

        // ea   ele   elor
        //      replace with e
        if ( ($position = $this->search(array('ea', 'ele', 'elor'))) !== false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(ea|ele|elor)$#u', 'e', $this->word);
            }
            return true;
        }

        // ii   iua   iei   iile   iilor   ilor
        //      replace with i
        if ( ($position = $this->search(array('ii', 'iua', 'iei', 'iile', 'iilor', 'ilor'))) !== false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(ii|iua|iei|iile|iilor|ilor)$#u', 'i', $this->word);
            }
            return true;
        }

        // ile
        //      replace with i if not preceded by ab
        if ( ($position = $this->search(array('ile'))) !== false) {
            if ($this->inR1($position)) {
                $before = UTF8::substr($this->word, ($position-2), 2);

                if ($before != 'ab') {
                    $this->word = preg_replace('#(ile)$#u', 'i', $this->word);
                }
            }
            return true;
        }

        // atei
        //      replace with at
        if ( ($position = $this->search(array('atei'))) != false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(atei)$#u', 'at', $this->word);
            }
            return true;
        }

        // aţie   aţia
        //      replace with aţi
        if ( ($position = $this->search(array('aţie', 'aţia'))) !== false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(aţie|aţia)$#u', 'aţi', $this->word);
            }
            return true;
        }

        return false;
    }

    /**
     * Step 1: Reduction of combining suffixes
     * Search for the longest among the following suffixes, and, if it is in R1, preform the replacement action indicated.
     * Then repeat this step until no replacement occurs.
     * @return boolean
     */
    private function step1()
    {
        // abilitate   abilitati   abilităi   abilităţi
        //      replace with abil
        if ( ($position = $this->search(array('abilitate', 'abilitati', 'abilităi', 'abilităţi'))) !== false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(abilitate|abilitati|abilităi|abilităţi)$#u', 'abil', $this->word);
            }
            return true;
        }

        // ibilitate
        //      replace with ibil
        if ( ($position = $this->search(array('ibilitate'))) !== false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(ibilitate)$#u', 'ibil', $this->word);
            }
            return true;
        }

        // ivitate   ivitati   ivităi   ivităţi
        //      replace with iv
        if ( ($position = $this->search(array('ivitate', 'ivitati', 'ivităi', 'ivităţi'))) !== false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(ivitate|ivitati|ivităi|ivităţi)$#u', 'iv', $this->word);
            }
            return true;
        }

        // icitate   icitati   icităi   icităţi   icator   icatori   iciv   iciva   icive   icivi   icivă   ical   icala   icale   icali   icală
        //      replace with ic
        if ( ($position = $this->search(array(
            'icitate', 'icitati', 'icităi', 'icităţi', 'icatori', 'icator', 'iciva',
            'icive', 'icivi', 'icivă', 'icala', 'icale', 'icali', 'icală', 'iciv', 'ical'))) !== false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(icitate|icitati|icităi|icităţi|cator|icatori|iciva|icive|icivi|icivă|icala|icale|icali|icală|ical|iciv)$#u', 'ic', $this->word);
            }
            return true;
        }

        // ativ   ativa   ative   ativi   ativă   aţiune   atoare   ator   atori   ătoare   ător   ători
        //      replace with at
        if ( ($position = $this->search(array('ativa', 'ative', 'ativi', 'ativă', 'ativ', 'aţiune', 'atoare', 'atori', 'ătoare', 'ători', 'ător', 'ator'))) !== false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(ativa|ative|ativi|ativă|ativ|aţiune|atoare|atori|ătoare|ători|ător|ator)$#u', 'at', $this->word);
            }
            return true;
        }

        // itiv   itiva   itive   itivi   itivă   iţiune   itoare   itor   itori
        //      replace with it
        if ( ($position = $this->search(array('itiva', 'itive', 'itivi', 'itivă', 'itiv', 'iţiune', 'itoare', 'itori', 'itor'))) !== false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(itiva|itive|itivi|itivă|itiv|iţiune|itoare|itori|itor)$#u', 'it', $this->word);
            }
            return true;
        }

        return false;
    }

    /**
     * Step 2: Removal of 'standard' suffixes
     * Search for the longest among the following suffixes, and, if it is in R2, perform the action indicated.
     * @return boolean
     */
    private function step2()
    {
        // atori   itate   itati, ităţi, abila   abile   abili   abilă, ibila   ibile   ibili   ibilă
        // anta, ante, anti, antă, ator, ibil, oasa   oasă   oase, ităi, abil
        // osi   oşi   ant   ici   ică iva   ive   ivi   ivă ata   ată   ati   ate, ata   ată   ati   ate uta   ută   uti   ute, ita   ită   iti   ite  ica   ice
        // at, os, iv, ut, it, ic
        //      delete
        if ( ($position = $this->search(array(
            'atori', 'itate', 'itati', 'ităţi', 'abila', 'abile', 'abili', 'abilă', 'ibila', 'ibile', 'ibili', 'ibilă',
            'anta', 'ante', 'anti', 'antă', 'ator', 'ibil', 'oasa', 'oasă', 'oase', 'ităi', 'abil',
            'osi', 'oşi', 'ant', 'ici', 'ică', 'iva', 'ive', 'ivi', 'ivă', 'ata', 'ată', 'ati', 'ate', 'ata', 'ată',
            'ati', 'ate', 'uta', 'ută', 'uti', 'ute', 'ita', 'ită', 'iti', 'ite', 'ica', 'ice',
            'at', 'os', 'iv', 'ut', 'it', 'ic'
        ))) !== false) {
            if ($this->inR2($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }
            return true;
        }

        // iune   iuni
        //      delete if preceded by ţ, and replace the ţ by t.
        if ( ($position = $this->search(array('iune', 'iuni'))) !== false) {
            if ($this->inR2($position)) {
                $before = $position - 1;
                $letter = UTF8::substr($this->word, $before, 1);
                if ($letter == 'ţ') {
                    $this->word = UTF8::substr($this->word, 0, $position);
                    $this->word = preg_replace('#(ţ)$#u', 't', $this->word);
                }
            }
            return true;
        }

        // ism   isme   ist   ista   iste   isti   istă   işti
        //      replace with ist
        if ( ($position = $this->search(array('isme', 'ism', 'ista', 'iste', 'isti', 'istă', 'işti', 'ist'))) !== false) {
            if ($this->inR2($position)) {
                $this->word = preg_replace('#(isme|ism|ista|iste|isti|istă|işti|ist)$#u', 'ist', $this->word);
            }
            return true;
        }

        return false;
    }

    /**
     * Step 3: Removal of verb suffixes
     * Do step 3 if no suffix was removed either by step 1 or step 2.
     * @return boolean
     */
    private function step3()
    {
        // are   ere   ire   âre   ind   ând   indu   ându   eze   ească   ez   ezi   ează   esc   eşti
        // eşte   ăsc   ăşti   ăşte   am   ai   au   eam   eai   ea   eaţi   eau   iam   iai   ia   iaţi
        // iau   ui   aşi   arăm   arăţi   ară   uşi   urăm   urăţi   ură   işi   irăm   irăţi   iră   âi
        // âşi   ârăm   ârăţi   âră   asem   aseşi   ase   aserăm   aserăţi   aseră   isem   iseşi   ise
        // iserăm   iserăţi   iseră   âsem   âseşi   âse   âserăm   âserăţi   âseră   usem   useşi   use   userăm   userăţi   useră
        //      delete if preceded in RV by a consonant or u
        if ( ($position = $this->searchIfInRv(array(
            'userăţi', 'iserăţi', 'âserăţi', 'aserăţi',
            'userăm', 'iserăm', 'âserăm', 'aserăm',
            'iseră', 'âseşi', 'useră', 'âseră', 'useşi', 'iseşi', 'aseră', 'aseşi', 'ârăţi', 'irăţi', 'urăţi', 'arăţi', 'ească',
            'usem', 'âsem', 'isem', 'asem', 'ârăm', 'urăm', 'irăm', 'arăm', 'iaţi', 'eaţi', 'ăşte', 'ăşti', 'eşte', 'eşti', 'ează', 'ându', 'indu',
            'âse', 'use', 'ise', 'ase', 'âră', 'iră', 'işi', 'ură', 'uşi', 'ară', 'aşi', 'âşi', 'iau', 'iai', 'iam', 'eau', 'eai', 'eam', 'ăsc',
            'are', 'ere', 'ire', 'âre', 'ind', 'ând', 'eze', 'ezi', 'esc',
            'âi', 'ui', 'ia', 'ea', 'au', 'ai', 'am', 'ez'
        ))) !== false) {
            if ($this->inRv($position)) {
                $before = $position - 1;
                if ($this->inRv($before)) {
                    $letter = UTF8::substr($this->word, $before, 1);

                    if ( (!in_array($letter, self::$vowels)) || ($letter == 'u') ) {
                        $this->word = UTF8::substr($this->word, 0, $position);
                    }
                }
            }
            return true;
        }



        // ăm   aţi   em   eţi   im   iţi   âm   âţi   seşi   serăm   serăţi   seră   sei   se   sesem   seseşi   sese   seserăm   seserăţi   seseră
        //      delete
        if ( ($position = $this->searchIfInRv(array(
            'seserăm', 'seserăţi', 'seseră', 'seseşi', 'sesem', 'serăţi', 'serăm', 'seşi', 'sese', 'seră',
            'aţi', 'eţi', 'iţi', 'âţi', 'sei', 'se', 'ăm', 'âm', 'em', 'im'
        ))) !== false) {
            if ($this->inRv($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }
            return true;
        }
    }

    /**
     * Step 4: Removal of final vowel
     */
    private function step4()
    {
        // Search for the longest among the suffixes "a   e   i   ie   ă " and, if it is in RV, delete it.
        if ( ($position = $this->search(array('a', 'ie', 'e', 'i', 'ă'))) !== false) {
            if ($this->inRv($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }
        }

        return true;
    }

    /**
     * Finally
     * Turn I, U back into i, u
     */
    private function finish()
    {
        // Turn I, U back into i, u
        $this->word = UTF8::str_replace(array('I', 'U'), array('i', 'u'), $this->word);
    }
}
