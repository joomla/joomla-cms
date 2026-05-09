<?php

namespace Joomla\Tests\Unit\Libraries\Cms\Html;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Tests for HTMLHelper class
 *
 * @since  __DEPLOY_VERSION__
 */
class HTMLHelperTest extends UnitTestCase
{
    /**
     * Test buildAttributes with a simple array
     *
     * @return  void
     *
     * @covers  Joomla\CMS\HTML\HTMLHelper::buildAttributes
     */
    public function testBuildAttributesWithSimpleArray()
    {
        $attribs  = ['class' => 'my-class', 'id' => 'my-id'];
        $expected = 'class="my-class" id="my-id"';
        $this->assertEquals($expected, HTMLHelper::buildAttributes($attribs));
    }

    /**
     * Test buildAttributes with HTML special characters
     *
     * @return  void
     *
     * @covers  Joomla\CMS\HTML\HTMLHelper::buildAttributes
     */
    public function testBuildAttributesWithSpecialCharacters()
    {
        $attribs  = ['title' => 'Title with < & > " \' characters'];
        $expected = 'title="Title with &lt; &amp; &gt; &quot; &#039; characters"';
        $this->assertEquals($expected, HTMLHelper::buildAttributes($attribs));
    }

    /**
     * Test buildAttributes with null input
     *
     * @return  void
     *
     * @covers  Joomla\CMS\HTML\HTMLHelper::buildAttributes
     */
    public function testBuildAttributesWithNull()
    {
        $this->assertEquals('', HTMLHelper::buildAttributes(null));
    }

    /**
     * Test buildAttributes with empty array
     *
     * @return  void
     *
     * @covers  Joomla\CMS\HTML\HTMLHelper::buildAttributes
     */
    public function testBuildAttributesWithEmptyArray()
    {
        $this->assertEquals('', HTMLHelper::buildAttributes([]));
    }

    /**
     * Test buildAttributes with string input
     *
     * @return  void
     *
     * @covers  Joomla\CMS\HTML\HTMLHelper::buildAttributes
     */
    public function testBuildAttributesWithString()
    {
        $this->assertEquals('class="my-class"', HTMLHelper::buildAttributes('class="my-class"'));
    }

    /**
     * Test buildAttributes with numeric value
     *
     * @return  void
     *
     * @covers  Joomla\CMS\HTML\HTMLHelper::buildAttributes
     */
    public function testBuildAttributesWithNumericValue()
    {
        $attribs  = ['data-id' => 123];
        $expected = 'data-id="123"';
        $this->assertEquals($expected, HTMLHelper::buildAttributes($attribs));
    }

    /**
     * Test HTMLHelper::link with double quote breaking XSS payload in class attribute
     *
     * @return  void
     *
     * @covers  Joomla\CMS\HTML\HTMLHelper::link
     * @covers  Joomla\CMS\HTML\HTMLHelper::buildAttributes
     */
    public function testLinkWithDoubleQuoteBreakingXssPayload()
    {
        $url  = '#';
        $text = 'Click me';

        // Simulate user input designed to break out of an attribute and inject code.
        $attribs = [
            'class' => 'some-class"><script>alert(1)</script>"',
        ];

        // The expected output should have the double quotes properly escaped,
        // preventing the `<script>` from becoming live HTML tag.
        $expected = '<a href="#" class="some-class&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;&quot;">Click me</a>';
        $this->assertEquals($expected, HTMLHelper::link($url, $text, $attribs));
    }
}
