<?php
namespace Wamania\Snowball;

/**
 *
 * @link http://snowball.tartarus.org/algorithms/spanish/stemmer.html
 * @author wamania
 *
 */
class Spanish extends Stem
{
    /**
     * All spanish vowels
     */
    protected static $vowels = array('a', 'e', 'i', 'o', 'u', 'á', 'é', 'í', 'ó', 'ú', 'ü');

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

        $this->rv();
        $this->r1();
        $this->r2();

        $this->step0();

        $word = $this->word;
        $this->step1();

        // Do step 2a if no ending was removed by step 1.
        if ($this->word == $word) {
            $this->step2a();

            // Do Step 2b if step 2a was done, but failed to remove a suffix.
            if ($this->word == $word) {
                $this->step2b();
            }
        }

        $this->step3();
        $this->finish();

        return $this->word;
    }

    /**
     * Step 0: Attached pronoun
     *
     * Search for the longest among the following suffixes
     *      me   se   sela   selo   selas   selos   la   le   lo   las   les   los   nos
     *
     * and delete it, if comes after one of
     *      (a) iéndo   ándo   ár   ér   ír
     *      (b) ando   iendo   ar   er   ir
     *      (c) yendo following u
     *
     *  in RV. In the case of (c), yendo must lie in RV, but the preceding u can be outside it.
     *  In the case of (a), deletion is followed by removing the acute accent (for example, haciéndola -> haciendo).
     */
    private function step0()
    {
        if ( ($position = $this->searchIfInRv(array('selas', 'selos', 'las', 'los', 'les', 'nos', 'selo', 'sela', 'me', 'se', 'la', 'le', 'lo' ))) != false) {
            $suffixe = Utf8::substr($this->word, $position);

            // a
            $a = array('iéndo', 'ándo', 'ár', 'ér', 'ír');
            $a = array_map(function($item) use ($suffixe) {
                return $item . $suffixe;
            }, $a);

            if ( ($position2 = $this->searchIfInRv($a)) !== false) {
                $suffixe2 = Utf8::substr($this->word, $position2);
                $suffixe2 = Utf8::deaccent($suffixe2, -1);
                $this->word = Utf8::substr($this->word, 0, $position2);
                $this->word .= $suffixe2;
                $this->word = Utf8::substr($this->word, 0, $position);
                return true;
            }

            // b
            $b = array('iendo', 'ando', 'ar', 'er', 'ir');
            $b = array_map(function($item) use ($suffixe) {
                return $item . $suffixe;
            }, $b);

            if ( ($position2 = $this->searchIfInRv($b)) !== false) {
                $this->word = Utf8::substr($this->word, 0, $position);
                return true;
            }

            // c
            if ( ($position2 = $this->searchIfInRv(array('yendo' . $suffixe))) != false) {
                $before = Utf8::substr($this->word, ($position2-1), 1);
                if ( (isset($before)) && ($before == 'u') ) {
                    $this->word = Utf8::substr($this->word, 0, $position);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Step 1
     */
    private function step1()
    {
        // anza   anzas   ico   ica   icos   icas   ismo   ismos   able   ables   ible   ibles   ista
        // istas   oso   osa   osos   osas   amiento   amientos   imiento   imientos
        //      delete if in R2
        if ( ($position = $this->search(array(
            'imientos', 'imiento', 'amientos', 'amiento', 'osas', 'osos', 'osa', 'oso', 'istas', 'ista', 'ibles',
            'ible', 'ables', 'able', 'ismos', 'ismo', 'icas', 'icos', 'ica', 'ico', 'anzas', 'anza'))) != false) {

            if ($this->inR2($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);
            }
            return true;
        }

        // adora   ador   ación   adoras   adores   aciones   ante   antes   ancia   ancias
        //      delete if in R2
        //      if preceded by ic, delete if in R2
        if ( ($position = $this->search(array(
            'adoras', 'adora', 'aciones', 'ación', 'adores', 'ador', 'antes', 'ante', 'ancias', 'ancia'))) != false) {

            if ($this->inR2($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);
            }

            if ( ($position2 = $this->searchIfInR2(array('ic')))) {
                $this->word = Utf8::substr($this->word, 0, $position2);
            }
            return true;
        }

        // logía   logías
        //      replace with log if in R2
        if ( ($position = $this->search(array('logías', 'logía'))) != false) {
            if ($this->inR2($position)) {
                $this->word = preg_replace('#(logías|logía)$#u', 'log', $this->word);
            }
            return true;
        }

        // ución   uciones
        //      replace with u if in R2
        if ( ($position = $this->search(array('uciones', 'ución'))) != false) {
            if ($this->inR2($position)) {
                $this->word = preg_replace('#(uciones|ución)$#u', 'u', $this->word);
            }
            return true;
        }

        // encia   encias
        //      replace with ente if in R2
        if ( ($position = $this->search(array('encias', 'encia'))) != false) {
            if ($this->inR2($position)) {
                $this->word = preg_replace('#(encias|encia)$#u', 'ente', $this->word);
            }
            return true;
        }

        // amente
        //      delete if in R1
        //      if preceded by iv, delete if in R2 (and if further preceded by at, delete if in R2), otherwise,
        //      if preceded by os, ic or ad, delete if in R2
        if ( ($position = $this->search(array('amente'))) != false) {

            // delete if in R1
            if ($this->inR1($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);
            }

            // if preceded by iv, delete if in R2 (and if further preceded by at, delete if in R2), otherwise,
            if ( ($position2 = $this->searchIfInR2(array('iv'))) !== false) {
                $this->word = Utf8::substr($this->word, 0, $position2);
                if ( ($position3 = $this->searchIfInR2(array('at'))) !== false) {
                    $this->word = Utf8::substr($this->word, 0, $position3);
                }

            // if preceded by os, ic or ad, delete if in R2
            } elseif ( ($position4 = $this->searchIfInR2(array('os', 'ic', 'ad'))) != false) {
                $this->word = Utf8::substr($this->word, 0, $position4);
            }
            return true;
        }

        // mente
        //      delete if in R2
        //      if preceded by ante, able or ible, delete if in R2
        if ( ($position = $this->search(array('mente'))) != false) {

            // delete if in R2
            if ($this->inR2($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);
            }

            // if preceded by ante, able or ible, delete if in R2
            if ( ($position2 = $this->searchIfInR2(array('ante', 'able', 'ible'))) != false) {
                $this->word = Utf8::substr($this->word, 0, $position2);
            }
            return true;
        }

        // idad   idades
        //      delete if in R2
        //      if preceded by abil, ic or iv, delete if in R2
        if ( ($position = $this->search(array('idades', 'idad'))) != false) {

            // delete if in R2
            if ($this->inR2($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);
            }

            // if preceded by abil, ic or iv, delete if in R2
            if ( ($position2 = $this->searchIfInR2(array('abil', 'ic', 'iv'))) != false) {
                $this->word = Utf8::substr($this->word, 0, $position2);
            }
            return true;
        }

        // iva   ivo   ivas   ivos
        //      delete if in R2
        //      if preceded by at, delete if in R2
        if ( ($position = $this->search(array('ivas', 'ivos', 'iva', 'ivo'))) != false) {

            // delete if in R2
            if ($this->inR2($position)) {
                $this->word = Utf8::substr($this->word, 0, $position);
            }

            // if preceded by at, delete if in R2
            if ( ($position2 = $this->searchIfInR2(array('at'))) != false) {
                $this->word = Utf8::substr($this->word, 0, $position2);
            }
            return true;
        }

        return false;
    }

    /**
     * Step 2a: Verb suffixes beginning y
     */
    private function step2a()
    {
        // if found, delete if preceded by u
        // (Note that the preceding u need not be in RV.)
        if ( ($position = $this->searchIfInRv(array(
            'yamos', 'yendo', 'yeron', 'yan', 'yen', 'yais', 'yas', 'yes', 'yo', 'yó', 'ya', 'ye'))) != false) {

            $before = Utf8::substr($this->word, ($position-1), 1);
            if ( (isset($before)) && ($before == 'u') ) {
                $this->word = Utf8::substr($this->word, 0, $position);
                return true;
            }
        }

        return false;
    }

    /**
     * Step 2b: Other verb suffixes
     *      Search for the longest among the following suffixes in RV, and perform the action indicated.
     */
    private function step2b()
    {
        //      delete
        if ( ($position = $this->searchIfInRv(array(
            'iésemos', 'iéramos', 'ábamos', 'iríamos', 'eríamos', 'aríamos', 'áramos', 'ásemos', 'eríais',
            'aremos', 'eremos', 'iremos', 'asteis', 'ieseis', 'ierais', 'isteis', 'aríais',
            'irían', 'aréis', 'erían', 'erías', 'eréis', 'iréis', 'irías', 'ieran', 'iesen', 'ieron', 'iendo', 'ieras',
            'iríais', 'arían', 'arías',
            'amos', 'imos', 'ados', 'idos', 'irán', 'irás', 'erán', 'erás', 'ería', 'iría', 'íais', 'arán', 'arás', 'aría',
            'iera', 'iese', 'aste', 'iste', 'aban', 'aran', 'asen', 'aron', 'ando', 'abas', 'adas', 'idas', 'ases', 'aras',
            'aré', 'erá', 'eré', 'áis', 'ías', 'irá', 'iré', 'aba', 'ían', 'ada', 'ara', 'ase', 'ida', 'ado', 'ido', 'ará',
            'ad', 'ed', 'id', 'ís', 'ió', 'ar', 'er', 'ir', 'as', 'ía', 'an'
        ))) != false) {
            $this->word = Utf8::substr($this->word, 0, $position);
            return true;
        }

        // en   es   éis   emos
        //      delete, and if preceded by gu delete the u (the gu need not be in RV)
        if ( ($position = $this->searchIfInRv(array('éis', 'emos', 'en', 'es'))) != false) {
            $this->word = Utf8::substr($this->word, 0, $position);

            if ( ($position2 = $this->search(array('gu'))) != false) {
                $this->word = Utf8::substr($this->word, 0, ($position2+1));
            }


            return true;
        }
    }

    /**
     * Step 3: residual suffix
     * Search for the longest among the following suffixes in RV, and perform the action indicated.
     */
    private function step3()
    {
        // os   a   o   á   í   ó
        //      delete if in RV
        if ( ($position = $this->searchIfInRv(array('os', 'a', 'o', 'á', 'í', 'ó'))) != false) {
            $this->word = Utf8::substr($this->word, 0, $position);
            return true;
        }

        // e   é
        //      delete if in RV, and if preceded by gu with the u in RV delete the u
        if ( ($position = $this->searchIfInRv(array('e', 'é'))) != false) {
            $this->word = Utf8::substr($this->word, 0, $position);

            if ( ($position2 = $this->searchIfInRv(array('u'))) != false) {
                $before = Utf8::substr($this->word, ($position2-1), 1);
                if ( (isset($before)) && ($before == 'g') ) {
                    $this->word = Utf8::substr($this->word, 0, $position2);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * And finally:
     * Remove acute accents
     */
    private function finish()
    {
        $this->word = Utf8::str_replace(array('á', 'í', 'ó', 'é', 'ú'), array('a', 'i', 'o', 'e', 'u'), $this->word);
    }
}
