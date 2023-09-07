<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_finder
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Finder\Administrator\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Tests\Unit\UnitTestCase;
use ReflectionClass;

/**
 * Test class for \Joomla\Component\Finder\Administrator\Indexer\Result
 *
 * @since  4.1.3
 */
class ResultTest extends UnitTestCase
{
    /**
     * @return void
     *
     * @throws \ReflectionException
     *
     * @covers Result::unserialize
     * @covers Result::serialize
     * @covers Result::__serialize
     * @covers Result::__unserialize
     *
     * @since   4.1.3
     */
    public function testSerialize(): void
    {
        /** @var Result $obj */
        $obj = $this->createNoConstructorMock();
        $obj->setElement('this', 'that');
        $obj->title = 'MyTitle';

        // Test the `serialize` method provided by the object - Pre PHP 8.1 deprecated style
        $this->assertIsString(
            $obj->serialize($obj)
        );

        // Test PHP `serialize` the object - PHP 8.1+ style (uses magic methods)
        $this->assertEquals(
            'that',
            unserialize(serialize($obj))->getElement('this')
        );

        $obj->title = 'MyTitle2';
        $this->assertEquals(
            'MyTitle2',
            unserialize(serialize($obj))->title
        );
    }

    /**
     * Useful method to mock the Result class so that the constructor doesn't call
     * ComponentHelper which requires a db connection and full Joomla stack running.
     *
     * @param $class string
     *
     * @return object<Result>
     *
     * @throws \ReflectionException
     *
     * @since  4.1.3
     */
    private function createNoConstructorMock($class = Result::class): object
    {
        return (new ReflectionClass($class))->newInstanceWithoutConstructor();
    }

    /**
 * @return void
 *
 * @throws \ReflectionException
 *
 * @covers Result::setElement
 * @covers Result::getElement
 *
 * @since   4.1.3
 */
public function testSetAndGetElement(): void
{
    // Create a new Result object
    $obj = $this->createNoConstructorMock();
    
    // Set an element
    $obj->setElement('key1', 'value1');
    
    // Get the element and assert its value
    $this->assertEquals('value1', $obj->getElement('key1'));
    
    // Set another element
    $obj->setElement('key2', 'value2');
    
    // Get the second element and assert its value
    $this->assertEquals('value2', $obj->getElement('key2'));
    
    // Attempt to get a non-existent element should return null
    $this->assertNull($obj->getElement('non_existent_key'));
}
}
