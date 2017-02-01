<?php

use \enshrined\svgSanitize\data\AllowedAttributes;

/**
 * Class AllowedAttributesTest
 */
class AllowedAttributesTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var AllowedAttributes
     */
    protected $class;

    /**
     * Set up the test class
     */
    public function setUp()
    {
        $this->class = new AllowedAttributes();
    }

    /**
     * Test that the class implements the interface
     */
    public function testItImplementsTheInterface()
    {
        $this->assertInstanceOf('enshrined\svgSanitize\data\AttributeInterface', $this->class);
    }

    /**
     * Test that an array is returned
     */
    public function testThatItReturnsAnArray()
    {
        $result = AllowedAttributes::getAttributes();

        $this->assertInternalType('array', $result);
    }
}