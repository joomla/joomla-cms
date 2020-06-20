<?php
namespace enshrined\svgSanitize\Tests;

use enshrined\svgSanitize\data\AllowedAttributes;
use PHPUnit\Framework\TestCase;

/**
 * Class AllowedAttributesTest
 */
class AllowedAttributesTest extends TestCase
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