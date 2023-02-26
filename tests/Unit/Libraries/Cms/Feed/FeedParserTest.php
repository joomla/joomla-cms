<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Feed;

use Joomla\CMS\Feed\Feed;
use Joomla\CMS\Feed\FeedEntry;
use Joomla\CMS\Feed\FeedParser;
use Joomla\CMS\Feed\Parser\NamespaceParserInterface;
use Joomla\Tests\Unit\UnitTestCase;
use ReflectionClass;
use SimpleXMLElement;
use XMLReader;

/**
 * Test class for FeedParser.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.1.4
 */
// phpcs:disable PSR1.Classes.ClassDeclaration
class FeedParserTest extends UnitTestCase
{
    /**
     * Tests FeedParser::parse()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testParse()
    {
        $xml       = '<root><tag>something</tag></root>';
        $xmlReader = $this->getXmlReader($xml, 'tag');
        $parser    = new FeedParserStub($xmlReader);

        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
        $this->assertEquals(1, $parser->getInitializeCalledCounter());

        // Cleanup
        $xmlReader->close();
    }

    /**
     * Tests FeedParser::parse() with processing a custom element.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testParseCustomElement()
    {
        $content   = 'test';
        $xml       = "<root><custom>$content</custom></root>";
        $xmlReader = $this->getXmlReader($xml, 'custom');
        $parser    = new FeedParserStub($xmlReader);

        $parser->parse();

        $handleCustomCalledWith = $parser->getHandleCustomCalledWith();
        $this->assertCount(1, $handleCustomCalledWith);
        $this->assertInstanceOf(Feed::class, $handleCustomCalledWith[0]['feed']);
        $this->assertEquals($content, $handleCustomCalledWith[0]['el'][0]);

        // Cleanup
        $xmlReader->close();
    }

    /**
     * Tests FeedParser::parse() with processing a namespace.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testParseNamespaceElement()
    {
        $content = 'test';
        $prefix = 'custom';
        $xml = "<root xmlns:$prefix='http://namespace'><$prefix:content>$content</$prefix:content></root>";
        $xmlReader = $this->getXmlReader($xml, $prefix . ':content');
        $namespaceMock = $this->createMock(NamespaceParserInterface::class);
        $namespaceMock
            ->expects($this->once())
            ->method('processElementForFeed')
            ->with(
                $this->isInstanceOf(Feed::class),
                $this->callback(
                    function ($value) use ($content) {
                        return $value instanceof SimpleXMLElement && (string) $value[0] === $content;
                    }
                )
            );

        $parser = new FeedParserStub($xmlReader);
        $parser->registerNamespace($prefix, $namespaceMock);
        $parser->parse();

        // Cleanup
        $xmlReader->close();
    }

    /**
     * Tests FeedParser::parse() for unregistered namespace.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testParseDetectsUnregisteredNamespace()
    {
        $content = 'test';
        $prefix = 'unregistered';
        $xml = "<root xmlns:$prefix='http://namespace'><$prefix:content>$content</$prefix:content></root>";
        $xmlReader = $this->getXmlReader($xml, "$prefix:content");

        $parser = new FeedParserStub($xmlReader);
        $parser->parse();

        $this->assertNotEmpty($parser->getNamespaces());
        $this->assertArrayHasKey($prefix, $parser->getNamespaces());
        $this->assertInstanceOf(FeedParserStubUnregistered::class, $parser->getNamespaces()[$prefix]);

        // Cleanup
        $xmlReader->close();
    }

    /**
     * Tests FeedParser::parse() with processing an entry element.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testParseElementWithEntry()
    {
        $xml       = "<root><entry></entry></root>";
        $xmlReader = $this->getXmlReader($xml, 'entry');
        $parser    = new FeedParserStub($xmlReader);

        $feed = $parser->parse();

        $this->assertInstanceOf(Feed::class, $feed);
        $this->assertEquals(1, $feed->count());
        $processFeedEntryCalledWith = $parser->getProcessFeedEntryCalledWith();
        $this->assertCount(1, $processFeedEntryCalledWith);
        $this->assertInstanceOf(FeedEntry::class, $processFeedEntryCalledWith[0]['entry']);
        $this->assertEquals('', $processFeedEntryCalledWith[0]['el'][0]);

        // Cleanup
        $xmlReader->close();
    }

    /**
     * Tests FeedParser::parse() with processing a namespaced entry element.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testParseElementWithNamespaceEntry()
    {
        $content = 'test';
        $prefix = 'custom';
        $xml = "<root xmlns:$prefix='http://namespace'><$prefix:entry></$prefix:entry></root>";
        $xmlReader = $this->getXmlReader($xml, $prefix . ':entry');
        $namespaceMock = $this->createMock(NamespaceParserInterface::class);
        $namespaceMock
            ->expects($this->once())
            ->method('processElementForFeedEntry')
            ->with(
                $this->isInstanceOf(FeedEntry::class),
                $this->callback(
                    function ($value) use ($content) {
                        return $value instanceof SimpleXMLElement && (string) $value[0] === '';
                    }
                )
            );

        $parser = new FeedParserStub($xmlReader);
        $parser->registerNamespace($prefix, $namespaceMock);
        $parser->parse();
    }

    /**
     * Tests FeedParser::registerNamespace()
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function testRegisterNamespace()
    {
        $prefix        = 'my-namespace';
        $namespaceMock = $this->createMock(NamespaceParserInterface::class);

        $parser         = new FeedParserStub(new \XMLReader());
        $returnedParser = $parser->registerNamespace($prefix, $namespaceMock);

        $this->assertInstanceOf(FeedParserStub::class, $returnedParser);
        $this->assertEquals([$prefix => $namespaceMock], $parser->getNamespaces());
    }

    /**
     * Tests FeedParser::moveToNextElement()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testMoveToNextElement()
    {
        $xml = '<root><node test="first"><child>foobar</child></node><node test="second"></node></root>';
        $xmlReader = $this->getXmlReader($xml, 'root');

        $parser = new FeedParserStub($xmlReader);

        // Use reflection to test protected method (it's easier than testing this using the public interface)
        $reflectionClass = new ReflectionClass($parser);
        $method = $reflectionClass->getMethod('moveToNextElement');
        $method->setAccessible(true);

        // Move to next element
        $method->invoke($parser);

        // Move to the next element, which should be <node test="first">.
        $this->assertEquals('node', $xmlReader->name);
        $this->assertEquals('first', $xmlReader->getAttribute('test'));

        // Move to next element
        $method->invoke($parser);

        // Move to the next element, which should be <child> with a data value of "foobar".
        $this->assertEquals('child', $xmlReader->name);
        $this->assertEquals('foobar', $xmlReader->readString());

        // Move to next element
        $method->invoke($parser);

        // Move to the next element, which should be <node test="second">.
        $this->assertEquals('node', $xmlReader->name);
        $this->assertEquals('second', $xmlReader->getAttribute('test'));

        // Move to next element and assert that it returns false
        $this->assertFalse($method->invoke($parser));
    }

    /**
     * Tests FeedParser::moveToNextElement() when using the name attribute.
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testMoveToNextElementByName()
    {
        $xml = '<root><node test="first"><child>foobar</child></node><node test="second"></node></root>';
        $xmlReader = $this->getXmlReader($xml, 'root');

        $parser = new FeedParserStub($xmlReader);

        // Use reflection to test protected method (it's easier than testing this using the public interface)
        $reflectionClass = new ReflectionClass($parser);
        $method = $reflectionClass->getMethod('moveToNextElement');
        $method->setAccessible(true);

        // Move to next element
        $method->invoke($parser, 'node');

        // Move to the next <node> element, which should be <node test="first">.
        $this->assertEquals('node', $xmlReader->name);
        $this->assertEquals('first', $xmlReader->getAttribute('test'));

        // Move to the next <node> element, which should be <node test="second">.
        $method->invoke($parser, 'node');
        $this->assertEquals('node', $xmlReader->name);
        $this->assertEquals('second', $xmlReader->getAttribute('test'));
    }

    /**
     * Tests FeedParser::moveToClosingElement()
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testMoveToClosingElement()
    {
        $xml = '<root><child>foobar</child></root>';
        $xmlReader = $this->getXmlReader($xml, 'root');

        $parser = new FeedParserStub($xmlReader);

        // Use reflection to test protected method (it's easier than testing this using the public interface)
        $reflectionClass = new ReflectionClass($parser);
        $method = $reflectionClass->getMethod('moveToClosingElement');
        $method->setAccessible(true);

        // Move to next element
        $method->invoke($parser);

        // Move to the closing element, which should be </root>.
        $this->assertEquals(XMLReader::END_ELEMENT, $xmlReader->nodeType);
        $this->assertEquals('root', $xmlReader->name);
    }

    /**
     * Tests FeedParser::moveToClosingElement() with internal elements.
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testMoveToClosingElementWithInternalElements()
    {
        // Set the XML for the internal reader and move the stream to the first <node> element.
        $xml = '<root><node test="first"><child>foobar</child></node><node test="second"></node></root>';
        $xmlReader = $this->getXmlReader($xml, 'node');

        $parser = new FeedParserStub($xmlReader);

        // Use reflection to test protected method (it's easier than testing this using the public interface)
        $reflectionClass = new ReflectionClass($parser);
        $method = $reflectionClass->getMethod('moveToClosingElement');
        $method->setAccessible(true);

        // Move to next element
        $method->invoke($parser);

        // Ensure that the current node is closing element
        $this->assertEquals(XMLReader::END_ELEMENT, $xmlReader->nodeType);
        $this->assertEquals('node', $xmlReader->name);
    }

    /**
     * Tests FeedParser::moveToClosingElement() with self-closing tags.
     *
     * @return  void
     *
     * @since   3.1.4
     * @throws \ReflectionException
     */
    public function testMoveToClosingElementWithSelfClosingTag()
    {
        // Set the XML for the internal reader and move the stream to the first <node> element.
        $xml = '<root><node test="first" /><node test="second"></node></root>';
        $xmlReader = $this->getXmlReader($xml, 'node');

        $parser = new FeedParserStub($xmlReader);

        // Use reflection to test protected method (it's easier than testing this using the public interface)
        $reflectionClass = new ReflectionClass($parser);
        $method = $reflectionClass->getMethod('moveToClosingElement');
        $method->setAccessible(true);

        // Move to closing element
        $method->invoke($parser);

        // Move to the closing element, which should be </node>.
        $this->assertEquals(true, $xmlReader->isEmptyElement);
        $this->assertEquals('node', $xmlReader->name);
    }

    /**
     * Helper function which gets an instance of xml reader
     *
     * @param   mixed   $xml     XML
     * @param   mixed   $moveTo  Moveto
     *
     * @return XMLReader
     *
     * @since 4.0.0
     */
    protected function getXmlReader($xml, $moveTo): XMLReader
    {
        // It's hard to mock the xml reader stream, so we use the real object here (but set xml directly)
        $xmlReader = new XMLReader();

        // Set the XML for the internal reader and move the stream to the element.
        $xmlReader->XML($xml);

        do {
            $xmlReader->read();
        } while ($xmlReader->name != $moveTo && $xmlReader->nodeType != XMLReader::END_ELEMENT);

        return $xmlReader;
    }
}

/**
 * Class FeedParserStub
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       4.0.0
 */
class FeedParserStub extends FeedParser
{
    /**
     * Test helper flag to check that the initialize method was called
     *
     * @var  integer
     *
     * @since   4.0.0
     */
    protected $initializeCalledCounter = 0;

    /**
     * Test helper flag to check that the process feed entry handler was called
     *
     * @var array
     *
     * @since   4.0.0
     */
    protected $processFeedEntryCalledWith = [];

    /**
     * Test helper flag to check that the custom element handler was called
     *
     * @var array
     *
     * @since   4.0.0
     */
    protected $handleCustomCalledWith = [];

    /**
     * Getter for the initialize called counter
     *
     * @return  integer
     *
     * @since   4.0.0
     */
    public function getInitializeCalledCounter(): int
    {
        return $this->initializeCalledCounter;
    }

    /**
     * Getter for the handle custom called with flag
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getHandleCustomCalledWith(): array
    {
        return $this->handleCustomCalledWith;
    }

    /**
     * Getter for the namespaces
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Getter for the handle custom called with flag
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getProcessFeedEntryCalledWith(): array
    {
        return $this->processFeedEntryCalledWith;
    }

    /**
     * Process a feed entry
     *
     * @param   mixed   FeedEntry         $entry  Entry
     * @param   mixed   SimpleXMLElement  $el     El
     *
     * @return void
     *
     * @since   4.0.0
     */
    public function processFeedEntry(FeedEntry $entry, SimpleXMLElement $el)
    {
        $this->processFeedEntryCalledWith[] = [
            'entry' => $entry,
            'el'   => $el,
        ];
    }

    /**
     * Method to initialise the feed for parsing.  If child parsers need to detect versions or other
     * such things this is where you'll want to implement that logic.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    protected function initialise()
    {
        $this->initializeCalledCounter++;
    }

    /**
     * Custom element handler
     *
     * @param   mixed   Feed              $feed  Feed
     * @param   mixed   SimpleXMLElement  $el    El
     *
     * @return void
     *
     * @since   4.0.0
     */
    protected function handleCustom(Feed $feed, SimpleXMLElement $el)
    {
        $this->handleCustomCalledWith[] = [
            'feed' => $feed,
            'el'   => $el,
        ];
    }
}

/**
 * Class FeedParserStubUnregistered
 *
 * Helper Class to test an unregistered namespace
 *
 * @since   4.0.0
 */
class FeedParserStubUnregistered implements NamespaceParserInterface
{
    /**
     * Method to handle an element for the feed given that a certain namespace is present.
     *
     * @param   mixed   Feed               $feed  The Feed object being built from the parsed feed.
     * @param   mixed   SimpleXMLElement   $el    The current XML element object to handle.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function processElementForFeed(Feed $feed, SimpleXMLElement $el)
    {
    }

    /**
     * Method to handle the feed entry element for the feed given that a certain namespace is present.
     *
     * @param   mixed   FeedEntry         $entry  The FeedEntry object being built from the parsed feed entry.
     * @param   mixed   SimpleXMLElement  $el     The current XML element object to handle.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function processElementForFeedEntry(FeedEntry $entry, SimpleXMLElement $el)
    {
    }
}
