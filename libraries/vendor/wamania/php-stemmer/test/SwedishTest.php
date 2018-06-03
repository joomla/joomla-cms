<?php
namespace Wamania\Snowball\Tests;

use Wamania\Snowball\Swedish;

class SwedishTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider load
     */
    public function testStem($word, $stem)
    {
        $o = new Swedish();

        $snowballStem = $o->stem($word);

        $this->assertEquals($stem, $snowballStem);
    }

    public function load()
    {
        return new CsvFileIterator('test/files/sw.txt');
    }
}
