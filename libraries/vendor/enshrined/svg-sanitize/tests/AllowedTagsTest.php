<?php
namespace enshrined\svgSanitize\Tests;

use enshrined\svgSanitize\data\AllowedTags;
use PHPUnit\Framework\TestCase;

/**
 * Class AllowedTagsTest
 */
class AllowedTagsTest extends TestCase
{

    /**
     * @var AllowedTags
     */
    protected $class;

    /**
     * Set up the test class
     */
    public function setUp()
    {
        $this->class = new AllowedTags();
    }

    /**
     * Test that the class implements the interface
     */
    public function testItImplementsTheInterface()
    {
        $this->assertInstanceOf('enshrined\svgSanitize\data\TagInterface', $this->class);
    }

    /**
     * Test that an array is returned
     */
    public function testThatItReturnsAnArray()
    {
        $result = AllowedTags::getTags();

        $this->assertInternalType('array', $result);
    }
}