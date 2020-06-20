<?php
namespace enshrined\svgSanitize\Tests;

use enshrined\svgSanitize\Sanitizer;
use enshrined\svgSanitize\Tests\Fixtures\TestAllowedAttributes;
use enshrined\svgSanitize\Tests\Fixtures\TestAllowedTags;
use PHPUnit\Framework\TestCase;

/**
 * Class SanitizerTest
 */
class SanitizerTest extends TestCase
{
    /**
     * @var Sanitizer
     */
    protected $class;

    /**
     * Set up the test class
     */
    protected function setUp()
    {
        $this->class = new Sanitizer();
    }

    protected function tearDown()
    {
        unset($this->class);
    }

    /**
     * Make sure the initial tags are loaded
     */
    public function testLoadDefaultTags()
    {
        $tags = $this->class->getAllowedTags();

        $this->assertInternalType('array', $tags);
    }

    /**
     * Make sure the initial attributes are loaded
     */
    public function testLoadDefaultAttributes()
    {
        $attributes = $this->class->getAllowedAttrs();

        $this->assertInternalType('array', $attributes);
    }

    /**
     * Test the custom tag setters and getters
     */
    public function testSetCustomTags()
    {
        $this->class->setAllowedTags(new TestAllowedTags());

        $tags = $this->class->getAllowedTags();

        $this->assertInternalType('array', $tags);

        $this->assertEquals(array_map('strtolower', TestAllowedTags::getTags()), $tags);
    }

    /**
     * Test the custom attribute setters and getters
     */
    public function testSetCustomAttributes()
    {
        $this->class->setAllowedAttrs(new TestAllowedAttributes());

        $attributes = $this->class->getAllowedAttrs();

        $this->assertInternalType('array', $attributes);

        $this->assertEquals( array_map('strtolower', TestAllowedAttributes::getAttributes()), $attributes);
    }

    /**
     * Test that malicious elements and attributes are removed from standard XML
     */
    public function testSanitizeXMLDoc()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/xmlTestOne.xml');
        $expected = file_get_contents($dataDirectory . '/xmlCleanOne.xml');

        $cleanData = $this->class->sanitize($initialData);

        $this->assertXmlStringEqualsXmlString($expected, $cleanData);
    }

    /**
     * Test that malicious elements and attributes are removed from an SVG
     */
    public function testSanitizeSVGDoc()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/svgTestOne.svg');
        $expected = file_get_contents($dataDirectory . '/svgCleanOne.svg');

        $cleanData = $this->class->sanitize($initialData);

        $this->assertXmlStringEqualsXmlString($expected, $cleanData);
    }

    /**
     * Test that a badly formatted XML document returns false
     */
    public function testBadXMLReturnsFalse()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/badXmlTestOne.svg');

        $cleanData = $this->class->sanitize($initialData);

        $this->assertEquals(false, $cleanData);
    }

    /**
     * Make sure that hrefs get sanitized correctly
     */
    public function testSanitizeHrefs()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/hrefTestOne.svg');
        $expected = file_get_contents($dataDirectory . '/hrefCleanOne.svg');

        $cleanData = $this->class->sanitize($initialData);

        $this->assertXmlStringEqualsXmlString($expected, $cleanData);
    }

    /**
     * Make sure that hrefs get sanitized correctly when the xlink namespace is omitted.
     */
    public function testSanitizeHrefsNoXlinkNamespace()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/hrefTestTwo.svg');
        $expected = file_get_contents($dataDirectory . '/hrefCleanTwo.svg');

        $cleanData = $this->class->sanitize($initialData);

        $this->assertXmlStringEqualsXmlString($expected, $cleanData);
    }

    /**
     * Make sure that external references get sanitized correctly
     */
    public function testSanitizeExternal()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/externalTest.svg');
        $expected = file_get_contents($dataDirectory . '/externalClean.svg');

        $this->class->removeRemoteReferences(true);
        $cleanData = $this->class->sanitize($initialData);
        $this->class->removeRemoteReferences(false);

        $this->assertXmlStringEqualsXmlString($expected, $cleanData);
    }

    /**
     * Test that minification of an SVG works
     */
    public function testSanitizeAndMinifiySVGDoc()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/svgTestOne.svg');
        $expected = file_get_contents($dataDirectory . '/svgCleanOneMinified.svg');

        $this->class->minify(true);
        $cleanData = $this->class->sanitize($initialData);
        $this->class->minify(false);

        $this->assertXmlStringEqualsXmlString($expected, $cleanData);
    }

    /**
     * Test that ARIA and Data Attributes are allowed
     */
    public function testThatAriaAndDataAttributesAreAllowed()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/ariaDataTest.svg');
        $expected = file_get_contents($dataDirectory . '/ariaDataClean.svg');

        $this->class->minify(false);
        $cleanData = $this->class->sanitize($initialData);
        $this->class->minify(false);

        $this->assertXmlStringEqualsXmlString($expected, $cleanData);
    }

    /**
     * Test that ARIA and Data Attributes are allowed
     */
    public function testThatExternalUseElementsAreStripped()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/useTest.svg');
        $expected = file_get_contents($dataDirectory . '/useClean.svg');

        $this->class->minify(false);
        $cleanData = $this->class->sanitize($initialData);
        $this->class->minify(false);

        $this->assertXmlStringEqualsXmlString($expected, $cleanData);
    }

    /**
     * Test setXMLOptions and minifying works as expected
     */
    public function testMinifiedOptions()
    {
        $this->class->minify(true);
        $this->class->removeXMLTag(true);
        $this->class->setXMLOptions(0);

        $input = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><title>chevron-double-down</title><path d="M4 11.73l.68-.73L12 17.82 19.32 11l.68.73-7.66 7.13a.5.5 0 0 1-.68 0z"/><path d="M4 5.73L4.68 5 12 11.82 19.32 5l.68.73-7.66 7.13a.5.5 0 0 1-.68 0z"/></svg>';
        $output = $this->class->sanitize($input);
        $this->assertEquals($input, $output);
    }

    /**
     * @test
     */
    public function useRecursionsAreDetected()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/xlinkLaughsTest.svg');
        $expected = file_get_contents($dataDirectory . '/xlinkLaughsClean.svg');

        $this->class->minify(false);
        $cleanData = $this->class->sanitize($initialData);

        $this->assertXmlStringEqualsXmlString($expected, $cleanData);
    }

    /**
     * @test
     */
    public function infiniteUseLoopsAreDetected()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/xlinkLoopTest.svg');
        $expected = file_get_contents($dataDirectory . '/xlinkLoopClean.svg');

        $this->class->minify(false);
        $cleanData = $this->class->sanitize($initialData);

        $this->assertXmlStringEqualsXmlString($expected, $cleanData);
    }

    /**
     * Make sure that DOS attacks using the <use> element are detected.
     */
    public function testUseDOSattacksAreNullified()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/useDosTest.svg');
        $expected = file_get_contents($dataDirectory . '/useDosClean.svg');

        $this->class->minify(false);
        $cleanData = $this->class->sanitize($initialData);

        $this->assertXmlStringEqualsXmlString($expected, $cleanData);
    }

    /**
     * Make sure that DOS attacks using the <use> element are detected,
     * especially when the SVG is extremely large.
     */
    public function testLargeUseDOSattacksAreNullified()
    {
        $dataDirectory = __DIR__ . '/data';
        $initialData = file_get_contents($dataDirectory . '/useDosTestTwo.svg');
        $expected = file_get_contents($dataDirectory . '/useDosCleanTwo.svg');

        $this->class->minify(false);
        $cleanData = $this->class->sanitize($initialData);

        $this->assertXmlStringEqualsXmlString($expected, $cleanData);
    }
}
