<?php
namespace Wamania\Snowball\Tests;

use Wamania\Snowball\English;

class EnglishTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider load
     */
    public function testStem($word, $stem)
    {
        $o = new English();

        $snowballStem = $o->stem($word);

        $this->assertEquals($stem, $snowballStem);
    }

    public function load()
    {
        return new CsvFileIterator('test/files/en.txt');
    }
}
