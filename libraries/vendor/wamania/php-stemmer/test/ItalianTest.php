<?php
namespace Wamania\Snowball\Tests;

use Wamania\Snowball\Italian;

class ItalianTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider load
     */
    public function testStem($word, $stem)
    {
        $o = new Italian();

        $snowballStem = $o->stem($word);

        $this->assertEquals($stem, $snowballStem);
    }

    public function load()
    {
        return new CsvFileIterator('test/files/it.txt');
    }
}
