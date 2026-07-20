<?php

namespace Joomla\Tests\Unit\Libraries\Cms\Html\Helpers;

use Joomla\CMS\HTML\Helpers\Attribute;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Tests for Attribute helper class
 *
 * @since  __DEPLOY_VERSION__
 */
class AttributeTest extends UnitTestCase
{
    /**
     * Test buildAttributes with a simple array
     *
     * @return  void
     *
     * @covers  Joomla\CMS\HTML\Helpers\Attribute::buildAttributes
     */
    public function testBuildAttributesWithSimpleArray(): void
    {
        $attribs  = ['class' => 'my-class', 'id' => 'my-id'];
        $expected = 'class="my-class" id="my-id"';
        $this->assertEquals($expected, Attribute::buildAttributes($attribs));
    }

    /**
     * Test buildAttributes with HTML special characters
     *
     * @return  void
     *
     * @covers  Joomla\CMS\HTML\Helpers\Attribute::buildAttributes
     */
    public function testBuildAttributesWithSpecialCharacters(): void
    {
        $attribs  = ['title' => 'Title with < & > " \' characters'];
        $expected = 'title="Title with &lt; &amp; &gt; &quot; &#039; characters"';
        $this->assertEquals($expected, Attribute::buildAttributes($attribs));
    }

    /**
     * Test buildAttributes with empty array
     *
     * @return  void
     *
     * @covers  Joomla\CMS\HTML\Helpers\Attribute::buildAttributes
     */
    public function testBuildAttributesWithEmptyArray(): void
    {
        $this->assertEquals('', Attribute::buildAttributes([]));
    }

    /**
     * Test buildAttributes with invalid attribute names
     *
     * @return  void
     *
     * @covers  Joomla\CMS\HTML\Helpers\Attribute::buildAttributes
     */
    public function testBuildAttributesWithInvalidAttributeNames(): void
    {
        $attribs = [
            'valid'         => 'value1',
            '1invalid'      => 'value2',  // Starts with a number
            'invalid-name!' => 'value3',  // Contains invalid character '!'
            'data-valid'    => 'value4',
            ''              => 'value5',  // Empty attribute name
            '_isvalid'      => 'value6',  // Starts with underscore, not recommended but valid
            '-invalid'      => 'value7',  // Starts with hyphen
            'valid_name'    => 'value8',
        ];

        $expected = 'valid="value1" data-valid="value4" _isvalid="value6" valid_name="value8"';
        $this->assertEquals($expected, Attribute::buildAttributes($attribs));
    }

    /**
     * Test buildAttributes with double quote breaking XSS payload in class attribute
     *
     * @return  void
     *
     * @covers  Joomla\CMS\HTML\Helpers\Attribute::buildAttributes
     */
    public function testBuildAttributesWithXssPayload(): void
    {
        // Simulate user input designed to break out of an attribute and inject code.
        $attribs = [
            'class' => 'some-class"><script>alert(1)</script>"',
        ];

        // The expected output should have the double quotes properly escaped,
        // preventing the `<script>` from becoming live HTML tag.
        $expected = 'class="some-class&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;&quot;"';
        $this->assertEquals($expected, Attribute::buildAttributes($attribs));
    }
}
