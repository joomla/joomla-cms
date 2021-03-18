<?php

namespace Wamania\Snowball\Stemmer;

use voku\helper\UTF8;

/**
 *
 * @link   http://snowball.tartarus.org/algorithms/catalan/stemmer.html
 * @author Orestes Sanchez Benavente <orestes@estotienearreglo.es>
 *
 *
 * Some fine tuning was necessary in this implementation of the original catalan stemmer algorithm in Snowball:
 *
 *    1. Some suffix sets have overlapping items, so here all items are sorted by decreasing size, to
 *       prevent that a shorter suffix will skip a larger one.
 *
 *    2. Some alternatives (`or` operator in Snowball) in the original algorithm have
 *       been rearranged to make sure they are applied in the right order.
 *
 *  Based on the reference Snowball implementation by Israel Olalla of iSOCO
 */
class Catalan extends Stem
{

    /**
     * All catalan vowels
     */
    protected static $vowels = ['a', 'e', 'i', 'o', 'u', 'á', 'é', 'í', 'ó', 'ú', 'à', 'è', 'ï', 'ò', 'ü'];

    protected static $standard_suffix_1a = [
        'allengües', 'ativitats', 'bilitats', 'ionistes', 'ialistes', 'ialismes', 'ativitat', 'atòries', 'isament',
        'bilitat', 'ivitats', 'ionisme', 'ionista', 'ialista', 'ialisme', 'íssimes', 'formes', 'ivisme', 'aments',
        'nça', 'ificar', 'idores', 'ancies', 'atòria', 'ivitat', 'encies', 'ències', 'atives', 'íssima', 'íssims',
        'ictes', 'eries', 'itats', 'itzar', 'ament', 'ments', 'sfera', 'ícies', 'àries', 'cions', 'ístic', 'issos',
        'íssem', 'íssiu', 'issem', 'isseu', 'ísseu', 'dores', 'adura', 'ívola', 'ables', 'adors', 'idors', 'adora',
        'doras', 'dures', 'ancia', 'toris', 'encia', 'ència', 'ïtats', 'atius', 'ativa', 'ibles', 'asses', 'assos',
        'íssim', 'ìssem', 'ìsseu', 'ìssin', 'ismes', 'istes', 'inies', 'íinia', 'ínies', 'trius', 'atge', 'icte',
        'ells', 'ella', 'essa', 'eres', 'ines', 'able', 'itat', 'ives', 'ment', 'amen', 'iste', 'aire', 'eria',
        'eses', 'esos', 'ícia', 'icis', 'ícis', 'ària', 'alla', 'nces', 'enca', 'issa', 'dora', 'dors', 'bles',
        'ívol', 'egar', 'ejar', 'itar', 'ació', 'ants', 'tori', 'ions', 'isam', 'ores', 'aris', 'ïtat', 'atiu',
        'ible', 'assa', 'ents', 'imes', 'isme', 'ista', 'inia', 'ites', 'triu', 'oses', 'osos', 'ient', 'otes',
        'ell', 'esc', 'ets', 'eta', 'ers', 'ina', 'iva', 'ius', 'fer', 'als', 'era', 'ana', 'esa', 'ici', 'íci',
        'ció', 'dor', 'all', 'enc', 'osa', 'ble', 'dís', 'dur', 'ant', 'ats', 'ota', 'ors', 'ora', 'ari', 'uts',
        'uds', 'ent', 'ims', 'ima', 'ita', 'ar', 'és', 'ès', 'et', 'ls', 'ió', 'ot', 'al', 'or', 'il', 'ís', 'ós',
        'ud', 'ots', 'ó'
    ];

    protected static $attached_pronoun = [
        'selas', 'selos', '\'hi', '\'ho', '\'ls', '-les', '-nos', '\'ns', 'sela', 'selo', '\'s', '\'l', '-ls', '-la',
        '-li', 'vos', 'nos', '-us', '\'n', '-ns', '\'m', '-me', '-te', '\'t', 'los', 'las', 'les', 'ens', 'se', 'us',
        '-n', '-m', 'li', 'lo', 'me', 'le', 'la', 'ho', 'hi'
    ];

    protected static $verb_suffixes = [
        'aríamos', 'eríamos', 'iríamos', 'eresseu', 'iéramos', 'iésemos', 'adores', 'aríais', 'aremos', 'eríais',
        'eremos', 'iríais', 'iremos', 'ierais', 'ieseis', 'asteis', 'isteis', 'ábamos', 'áramos', 'ásemos', 'isquen',
        'esquin', 'esquis', 'esques', 'esquen', 'ïsquen', 'ïsques', 'adora', 'adors', 'arían', 'arías', 'arian',
        'arien', 'aries', 'aréis', 'erían', 'erías', 'eréis', 'erass', 'irían', 'irías', 'iréis', 'asseu', 'esseu',
        'àsseu', 'àssem', 'àssim', 'àssiu', 'essen', 'esses', 'assen', 'asses', 'assim', 'assiu', 'éssen', 'ésseu',
        'éssim', 'éssiu', 'éssem', 'aríem', 'aríeu', 'eixer', 'eixes', 'ieran', 'iesen', 'ieron', 'iendo', 'essin',
        'essis', 'assin', 'assis', 'essim', 'èssim', 'èssiu', 'ieras', 'ieses', 'abais', 'arais', 'aseis', 'íamos',
        'irien', 'iries', 'irìem', 'irìeu', 'iguem', 'igueu', 'esqui', 'eixin', 'eixis', 'eixen', 'iríem', 'iríeu',
        'atges', 'issen', 'isses', 'issin', 'issis', 'issiu', 'issim', 'ïssin', 'íssiu', 'íssim', 'ïssis', 'ïguem',
        'ïgueu', 'ïssen', 'ïsses', 'itzeu', 'itzis', 'ador', 'ents', 'udes', 'eren', 'arán', 'arás', 'aria', 'aràs',
        'aría', 'arés', 'erán', 'erás', 'ería', 'erau', 'irán', 'irás', 'iría', 'írem', 'íreu', 'aves', 'avem', 'ávem',
        'àvem', 'àveu', 'áveu', 'aven', 'ares', 'àrem', 'àreu', 'àren', 'areu', 'aren', 'tzar', 'ides', 'ïdes', 'ades',
        'iera', 'iese', 'aste', 'iste', 'aban', 'aran', 'asen', 'aron', 'abas', 'adas', 'idas', 'aras', 'ases', 'íais',
        'ados', 'idos', 'amos', 'imos', 'ques', 'iran', 'irem', 'iren', 'ires', 'ireu', 'iria', 'iràs', 'eixi', 'eixo',
        'isin', 'isis', 'esca', 'isca', 'ïsca', 'ïren', 'ïres', 'ïxen', 'ïxes', 'ixen', 'ixes', 'inin', 'inis', 'ineu',
        'itza', 'itzi', 'itzo', 'itzà', 'arem', 'ent', 'arà', 'ará', 'ara', 'aré', 'erá', 'eré', 'irá', 'iré', 'íeu',
        'ies', 'íem', 'ìeu', 'ien', 'uda', 'ava', 'ats', 'ant', 'ïen', 'ams', 'ïes', 'dre', 'eix', 'ïda', 'aba', 'ada',
        'ida', 'its', 'ids', 'ase', 'ían', 'ado', 'ido', 'ieu', 'ess', 'ass', 'ías', 'áis', 'ira', 'irà', 'irè', 'sis',
        'sin', 'int', 'isc', 'ïsc', 'ïra', 'ïxo', 'ixo', 'ixa', 'ini', 'itz', 'iïn', 're', 'ie', 'er', 'ia', 'at', 'ut',
        'au', 'ïm', 'ïu', 'és', 'en', 'es', 'em', 'am', 'ïa', 'it', 'ït', 'ía', 'ad', 'ed', 'id', 'an', 'ió', 'ar',
        'ir', 'as', 'ii', 'io', 'ià', 'ís', 'ïx', 'ix', 'in', 'às', 'iï', 'iïs', 'í'
    ];

    protected static $residual_suffixes = [
        'itz', 'it', 'os', 'eu', 'iu', 'is', 'ir', 'ïn', 'ïs', 'a', 'o', 'á', 'à', 'í', 'ó', 'e', 'é', 'i', 's', 'ì',
        'ï'
    ];

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

        // Catalan stemmer does not use Rv
        $this->r1();
        $this->r2();

        // Step 0: Attached pronoun
        $this->step0();

        $word = $this->word;
        // Step 1a: Standard suffix
        $this->step1a();

        // Step 1b: Verb suffix
        // Do step 1b if no ending was removed by step 1a.
        if ($this->word == $word) {
            $this->step1b();
        }

        $this->step2();
        $this->finish();

        return $this->word;
    }

    /**
     * Step 0: Attached pronoun
     *
     * Search for the longest among the following suffixes
     * and delete it in R1.
     */

    private function step0()
    {
        if (($position = $this->search(static::$attached_pronoun)) !== false) {
            if ($this->inR1($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
                return true;
            }
        }
        return false;
    }

    /**
     * Step 1a: Standard suffix
     */
    private function step1a()
    {
        // Run step 1a.2 before 1a.1, since they overlap on `cions` (1a.1) and `acions` (1a.2)
        //
        // Step 1a.2.
        // acions ada ades
        //      delete if in R2
        if (($position = $this->search(['acions', 'ada', 'ades'])) !== false) {
            if ($this->inR2($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }
            return true;
        }

        // Step 1a.1.
        // ar atge formes icte ictes ell ells ella és ès esc essa et ets eta eres eries ers ina ines able ls ió itat
        // itats itzar iva ives ivisme ius fer ment amen ament aments ments ot sfera al als era ana iste aire eria esa
        // eses esos or ícia ícies icis ici íci ícis ària àries alla ció cions n{c}a nces ó dor all il ístic enc enca
        // ís issa issos íssem íssiu issem isseu ísseu ós osa dora dores dors adura ble bles ívol ívola dís egar ejar
        // ificar itar ables adors idores idors adora ació doras dur dures alleng{u"}es ant ants ancia ancies atòria
        // atòries tori toris ats ions ota isam ors ora ores isament bilitat bilitats ivitat ivitats ari aris ionisme
        // ionista ionistes ialista ialistes ialisme ialismes ud uts uds encia encies ència ències ïtat ïtats atiu
        // atius atives ativa ativitat ativitats ible ibles assa asses assos ent ents íssim íssima íssims íssimes
        // ìssem ìsseu ìssin ims ima imes isme ista ismes istes inia inies íinia ínies ita ites triu trius oses osos
        // ient otes ots
        // 
        //      delete if in R1
        if (($position = $this->search(self::$standard_suffix_1a)) !== false) {
            if ($this->inR1($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }
            return true;
        }

        // Step 1a.3.
        // logía logíes logia logies logi logis lógica lógics lógiques
        //      replace with log if in R2
        if (($position = $this->search(
                ['logía', 'logíes', 'logia', 'logies', 'logis', 'lógica', 'lógics', 'lógiques', 'logi']
            )) !== false) {
            if ($this->inR2($position)) {
                $this->word = preg_replace(
                    '#(logía|logíes|logia|logies|logis|lógica|lógics|lógiques|logi)$#u', 'log', $this->word
                );
            }
            return true;
        }

        // Step 1a.4.
        // ic ica ics iques
        //      replace with ic if in R2
        if (($position = $this->search(['ics', 'ica', 'iques', 'ic'])) !== false) {
            if ($this->inR2($position)) {
                $this->word = preg_replace('#(ics|ica|iques|ic)$#u', 'ic', $this->word);
            }
            return true;
        }

        // Step 1a.5.
        // quíssims quíssimes quíssima quíssim
        //      replace with c if in R1
        if (($position = $this->search(['quíssima', 'quíssims', 'quíssimes', 'quíssim'])) !== false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(quíssima|quíssims|quíssimes|quíssim)$#u', 'c', $this->word);
            }
            return true;
        }

        return false;
    }

    /**
     * Step 1b: Verb suffixes
     *      Search for the longest among the following suffixes in r1 and r2, and
     *      perform the action indicated.
     */
    private function step1b()
    {
        // Step 1b.1
        //
        // aríamos eríamos iríamos eresseu iéramos iésemos adores aríais aremos eríais
        // eremos iríais iremos ierais ieseis asteis isteis ábamos áramos ásemos isquen
        // esquin esquis esques esquen ïsquen ïsques adora adors arían arías arian
        // arien aries aréis erían erías eréis erass irían irías iréis asseu esseu
        // àsseu àssem àssim àssiu essen esses assen asses assim assiu éssen ésseu
        // éssim éssiu éssem aríem aríeu eixer eixes ieran iesen ieron iendo essin
        // essis assin assis essim èssim èssiu ieras ieses abais arais aseis íamos
        // irien iries irìem irìeu iguem igueu esqui eixin eixis eixen iríem iríeu
        // atges issen isses issin issis issiu issim ïssin íssiu íssim ïssis ïguem
        // ïgueu ïssen ïsses itzeu itzis ador ents udes eren arán arás aria aràs
        // aría arés erán erás ería erau irán irás iría írem íreu aves avem ávem
        // àvem àveu áveu aven ares àrem àreu àren areu aren tzar ides ïdes ades
        // iera iese aste iste aban aran asen aron abas adas idas aras ases íais
        // ados idos amos imos ques iran irem iren ires ireu iria iràs eixi eixo
        // isin isis esca isca ïsca ïren ïres ïxen ïxes ixen ixes inin inis ineu
        // itza itzi itzo itzà arem ent arà ará ara aré erá eré irá iré íeu
        // ies íem ìeu ien uda ava ats ant ïen ams ïes dre eix ïda aba ada
        // ida its ids ase ían ado ido ieu ess ass ías áis ira irà irè sis
        // sin int isc ïsc ïra ïxo ixo ixa ini itz iïn re ie er ia at ut
        // au ïm ïu és en es em am ïa it ït ía ad ed id an ió ar
        // ir as ii io ià ís ïx ix in às iï iïs í
        //      delete if in R1
        if (($position = $this->search(static::$verb_suffixes)) !== false) {
            if ($this->inR1($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }
            return true;
        }

        // Step 1b.2
        // ando
        //      delete if in R2
        if (($position = $this->search(['ando'])) !== false) {
            if ($this->inR2($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }
            return true;
        }
        return false;
    }

    /**
     * Step 2: residual suffix
     * Search for the longest among the following suffixes in R1, and perform
     * the action indicated.
     */
    private function step2()
    {
        // Step 2.1
        // residual suffix
        //      delete if in R1
        if (($position = $this->search(static::$residual_suffixes)) !== false) {
            if ($this->inR1($position)) {
                $this->word = UTF8::substr($this->word, 0, $position);
            }
            return true;
        }

        // Step 2.2
        // iqu
        //      replace with ic if in R1
        if (($position = $this->search(['iqu'])) !== false) {
            if ($this->inR1($position)) {
                $this->word = preg_replace('#(iqu)$#u', 'ic', $this->word);
            }
            return true;
        }

        return false;
    }

    /**
     * And finally:
     * Remove accents and l aggeminades
     */
    private function finish()
    {
        $this->word = UTF8::str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'à', 'è', 'ì', 'ò', 'ï', 'ü', '·'],
            ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'i', 'u', '.'],
            $this->word
        );
    }

}
