<?php
namespace Wamania\Snowball\Tests;

use Wamania\Snowball\Romanian;

class RomanianTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider load
     */
    public function testStem($word, $stem)
    {
        $o = new Romanian();

        $snowballStem = $o->stem($word);

        $this->assertEquals($stem, $snowballStem);
    }

    public function load()
    {
        return new CsvFileIterator('test/files/ro.txt');
    }
}
