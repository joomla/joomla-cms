<?php

namespace Wamania\Snowball;

class StemmerManager
{
    /** @var array */
    private $stemmers;

    public function __construct()
    {
        $this->stemmers = [];
    }

    /**
     * @throws NotFoundException
     */
    public function stem(string $word, string $isoCode): string
    {
        if (!isset($this->stemmers[$isoCode])) {
            $this->stemmers[$isoCode] = StemmerFactory::create($isoCode);
        }

        return $this->stemmers[$isoCode]->stem($word);
    }
}
