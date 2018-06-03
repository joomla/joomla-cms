<?php
namespace Wamania\Snowball\Tests;

use Wamania\Snowball\Portuguese;

class PortugueseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider load
     */
    public function testStem($word, $stem)
    {
        $o = new Portuguese();

        $snowballStem = $o->stem($word);

        $this->assertEquals($stem, $snowballStem);
    }

    public function load()
    {
        return new CsvFileIterator('test/files/pt.txt');
    }
}
