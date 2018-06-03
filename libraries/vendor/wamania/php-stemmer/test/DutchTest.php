<?php
namespace Wamania\Snowball\Tests;

use Wamania\Snowball\Dutch;

class DutchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider load
     */
    public function testStem($word, $stem)
    {
        $o = new Dutch();

        $snowballStem = $o->stem($word);

        $this->assertEquals($stem, $snowballStem);
    }

    public function load()
    {
        return new CsvFileIterator('test/files/nl.txt');
    }
}
