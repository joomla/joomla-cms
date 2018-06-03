<?php
namespace Wamania\Snowball\Tests;

use Wamania\Snowball\Norwegian;

class NorwegianTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider load
     */
    public function testStem($word, $stem)
    {
        $o = new Norwegian();

        $snowballStem = $o->stem($word);

        $this->assertEquals($stem, $snowballStem);
    }

    public function load()
    {
        return new CsvFileIterator('test/files/no.txt');
    }
}
