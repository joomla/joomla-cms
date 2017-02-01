<?php

use \enshrined\svgSanitize\data\AllowedTags;

/**
 * Class AllowedTagsTest
 */
class AllowedTagsTest extends PHPUnit_Framework_TestCase
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