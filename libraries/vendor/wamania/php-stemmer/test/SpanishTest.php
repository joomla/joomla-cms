<?php
namespace Wamania\Snowball\Tests;

use Wamania\Snowball\Spanish;

class SpanishTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider load
     */
    public function testStem($word, $stem)
    {
        $o = new Spanish();

        $snowballStem = $o->stem($word);

        $this->assertEquals($stem, $snowballStem);
    }

    public function load()
    {
        return new CsvFileIterator('test/files/es.txt');
    }
}
