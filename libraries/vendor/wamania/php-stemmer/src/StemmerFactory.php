<?php

namespace Wamania\Snowball;

use voku\helper\UTF8;
use Wamania\Snowball\Stemmer\Catalan;
use Wamania\Snowball\Stemmer\Danish;
use Wamania\Snowball\Stemmer\Dutch;
use Wamania\Snowball\Stemmer\English;
use Wamania\Snowball\Stemmer\French;
use Wamania\Snowball\Stemmer\German;
use Wamania\Snowball\Stemmer\Italian;
use Wamania\Snowball\Stemmer\Norwegian;
use Wamania\Snowball\Stemmer\Portuguese;
use Wamania\Snowball\Stemmer\Romanian;
use Wamania\Snowball\Stemmer\Russian;
use Wamania\Snowball\Stemmer\Spanish;
use Wamania\Snowball\Stemmer\Stemmer;
use Wamania\Snowball\Stemmer\Swedish;

class StemmerFactory
{
    const LANGS = [
        Catalan::class    => ['ca', 'cat', 'catalan'],
        Danish::class     => ['da', 'dan', 'danish'],
        Dutch::class      => ['nl', 'dut', 'nld', 'dutch'],
        English::class    => ['en', 'eng', 'english'],
        French::class     => ['fr', 'fre', 'fra', 'french'],
        German::class     => ['de', 'deu', 'ger', 'german'],
        Italian::class    => ['it', 'ita', 'italian'],
        Norwegian::class  => ['no', 'nor', 'norwegian'],
        Portuguese::class => ['pt', 'por', 'portuguese'],
        Romanian::class   => ['ro', 'rum', 'ron', 'romanian'],
        Russian::class    => ['ru', 'rus', 'russian'],
        Spanish::class    => ['es', 'spa', 'spanish'],
        Swedish::class    => ['sv', 'swe', 'swedish']
    ];

    /**
     * @throws NotFoundException
     */
    public static function create(string $code): Stemmer
    {
        $code = UTF8::strtolower($code);

        foreach (self::LANGS as $classname => $isoCodes) {
            if (in_array($code, $isoCodes)) {
                return new $classname;
            }
        }

        throw new NotFoundException(sprintf('Stemmer not found for %', $code));
    }
}
