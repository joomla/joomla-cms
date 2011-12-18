<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JFeedParserMock.php';
require_once __DIR__ . '/stubs/JFeedParserProcessElementMock.php';
require_once __DIR__ . '/stubs/JFeedParserMockNamespace.php';

/**
 * Test class for JFeedParser.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.1
 */
class JFeedParserTest extends JoomlaTestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::setUp()
	 * @since   12.1
	 */
	protected function setUp()
	{
		parent::setUp();

		// Create the XMLReader object to be used in our parser instance.
		$this->reader = new XMLReader;

		// Instantiate the mock so we can call concrete methods.
		$this->object = new JFeedParserMock($this->reader);
	}

	/**
	 * Tear down any fixtures.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   12.1
	 */
	protected function tearDown()
	{
		$this->object = null;
		$this->reader = null;

		parent::tearDown();
	}

	/**
	 * Tests JFeedParser::parse()
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedParser::parse
	 */
	public function testParse()
	{
		// Create the mock so we can verify calls.
		$parser = $this->getMock(
			'JFeedParserMock',
			array('initialise', 'processElement'),
			array($this->reader)
		);

		// Setup some expectations for the mock object.
		$parser->expects($this->once())->method('initialise');
		$parser->expects($this->exactly(2))->method('processElement');

		ReflectionHelper::setValue($parser, 'namespaces', array('namespace' => new JFeedParserMockNamespace));

		// Set the XML for the internal reader and move the stream to the <root> element.
		$xml = '<root xmlns="http://bar.foo" xmlns:namespace="http://foo.bar"><tag1>foobar</tag1><namespace:tag2 attr="value" /></root>';
		$this->reader->XML($xml);

		// Advance the reader to the first <tag1> element.
		while ($this->reader->read() && ($this->reader->name != 'tag1'));

		$parser->parse();
	}

	/**
	 * Tests JFeedParser::registerNamespace()
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedParser::registerNamespace
	 */
	public function testRegisterNamespace()
	{
		// For a new object we nave no namespaces.
		$this->assertAttributeEmpty('namespaces', $this->object);

		// Add a new namespace.
		$mock = $this->getMock('JFeedParserNamespace');
		$this->object->registerNamespace('foo', $mock);

		$this->assertAttributeEquals(
			array('foo' => $mock),
			'namespaces',
			$this->object
		);

		// Add the namespace again for a different prefix.
		$this->object->registerNamespace('bar', $mock);

		$this->assertAttributeEquals(
			array('foo' => $mock, 'bar' => $mock),
			'namespaces',
			$this->object
		);
	}

	/**
	 * Tests JFeedParser::registerNamespace() with an expected failure.  Cannot register a string.
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers             JFeedParser::registerNamespace
	 * @expectedException  PHPUnit_Framework_Error
	 */
	public function testRegisterNamespaceWithString()
	{
		$this->object->registerNamespace('foo', 'bar');
	}

	/**
	 * Tests JFeedParser::registerNamespace() with an expected failure.  Cannot register a handler
	 * that isn't an instance of JFeedParserNamespace.
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers             JFeedParser::registerNamespace
	 * @expectedException  PHPUnit_Framework_Error
	 */
	public function testRegisterNamespaceWithObject()
	{
		$this->object->registerNamespace('foo', new stdClass);
	}

	/**
	 * Tests JFeedParser::processElement() with processing a normal element.
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedParser::processElement
	 */
	public function testProcessElementWithElement()
	{
		$el = new JXmlElement('<element1></element1>');

		// process element has a few dependencies that we need to pass:
		// a JFeed object, an element, and namespaces
		$feed = $this->getMockBuilder('JFeed')
					 ->disableOriginalConstructor()
					 ->getMock();

		$mock = $this->getMockBuilder('JFeedParserProcessElementMock')
					 ->disableOriginalConstructor()
					 ->setMethods(array('processFeedEntry', 'handleElement1'))
					 ->getMock();

		$mock->expects($this->once())
			 ->method('handleElement1')
			 ->with($feed, $el);

		$namespace = $this->getMockBuilder('JFeedParserNamespace')
						  ->getMock();

		$namespace->expects($this->once())
				  ->method('processElementForFeed')
				  ->with($feed, $el);

		$mock->processElement($feed, $el, array($namespace));
	}

	/**
	 * Tests JFeedParser::processElement() with processing an entry element.
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedParser::processElement
	 */
	public function testProcessElementWithEntry()
	{
		$el = new JXmlElement('<myentry></myentry>');

		// process element has a few dependencies that we need to pass:
		// a JFeed object, an element, and namespaces
		$feed = $this->getMockBuilder('JFeed')
					 ->disableOriginalConstructor()
					 ->getMock();

		$feed->expects($this->once())
			 ->method('addEntry')
			 ->with($this->isInstanceOf('JFeedEntry'));

		$mock = $this->getMockBuilder('JFeedParserProcessElementMock')
					 ->disableOriginalConstructor()
					 ->setMethods(array('processFeedEntry'))
					 ->getMock();

		$mock->expects($this->once())
			 ->method('processFeedEntry')
			 ->with($this->isInstanceOf('JFeedEntry'), $el);

		$namespace = $this->getMockBuilder('JFeedParserNamespace')
						  ->getMock();

		$namespace->expects($this->once())
				  ->method('processElementForFeedEntry')
				  ->with($this->isInstanceOf('JFeedEntry'), $el);

		$mock->processElement($feed, $el, array($namespace));
	}

	/**
	 * Tests JFeedParser::expandToSimpleXml()
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedParser::expandToSimpleXml
	 */
	public function testExpandToSimpleXml()
	{
		// Set the XML for the internal reader and move the stream to the first <node> element.
		$this->reader->XML('<node foo="bar"><child>foobar</child></node>');
		$this->reader->next('node');

		// Execute the 'expandToSimpleXml' method.
		$el = ReflectionHelper::invoke($this->object, 'expandToSimpleXml');

		$this->assertInstanceOf(
			'JXmlElement',
			$el,
			'The expanded return value should be a JXmlElement instance.'
		);

		$this->assertEquals(
			'node',
			$el->getName(),
			'The element should be named "node".'
		);

		$this->assertEquals(
			'bar',
			(string) $el['foo'],
			'The element should have an attribute "foo" with a value "bar".'
		);

		$this->assertInstanceOf(
			'JXmlElement',
			$el->child[0],
			'The expanded return value should have a child element which is a JXmlElement instance.'
		);

		$this->assertEquals(
			'foobar',
			(string) $el->child[0],
			'The child element should have a value of "foobar".'
		);
	}

	/**
	 * Tests JFeedParser::fetchNamespace()
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedParser::fetchNamespace
	 */
	public function testFetchNamespace()
	{
		// Set a mock namespace into the namespaces for the parser object.
		$mock = $this->getMock('JFeedParserNamespace');
		$namespaces = array('mock' => $mock);
		ReflectionHelper::setValue($this->object, 'namespaces', $namespaces);

		$ns = ReflectionHelper::invoke($this->object, 'fetchNamespace', 'mock');
		$this->assertSame($mock, $ns, 'The mock namespace should be what is returned.');

		$ns = ReflectionHelper::invoke($this->object, 'fetchNamespace', 'foobar');
		$this->assertFalse($ns, 'Since there is no foobar namespace it should return false.');

		$ns = ReflectionHelper::invoke($this->object, 'fetchNamespace', 'namespace');
		$this->assertInstanceOf('JFeedParserMockNamespace', $ns, 'We should get an instance of the mock namespace.');
	}

	/**
	 * Tests JFeedParser::moveToNextElement()
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedParser::moveToNextElement
	 */
	public function testMoveToNextElement()
	{
		// Set the XML for the internal reader and move the stream to the <root> element.
		$this->reader->XML('<root><node test="first"><child>foobar</child></node><node test="second"></node></root>');
		$this->reader->next('root');

		// Ensure that the current node is "root".
		$this->assertEquals('root', $this->reader->name);

		// Move to the next element, which should be <node test="first">.
		ReflectionHelper::invoke($this->object, 'moveToNextElement');
		$this->assertEquals('node', $this->reader->name);
		$this->assertEquals('first', $this->reader->getAttribute('test'));

		// Move to the next element, which should be <child> with a data value of "foobar".
		ReflectionHelper::invoke($this->object, 'moveToNextElement');
		$this->assertEquals('child', $this->reader->name);
		$this->assertEquals('foobar', $this->reader->readString());

		// Move to the next element, which should be <node test="second">.
		ReflectionHelper::invoke($this->object, 'moveToNextElement');
		$this->assertEquals('node', $this->reader->name);
		$this->assertEquals('second', $this->reader->getAttribute('test'));

		// Move to the next element, which should be <node test="second">.
		$return = ReflectionHelper::invoke($this->object, 'moveToNextElement');
		$this->assertFalse($return);
	}

	/**
	 * Tests JFeedParser::moveToNextElement() when using the name attribute.
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedParser::moveToNextElement
	 */
	public function testMoveToNextElementByName()
	{
		// Set the XML for the internal reader and move the stream to the <root> element.
		$this->reader->XML('<root><node test="first"><child>foobar</child></node><node test="second"></node></root>');

		// Move to the next <node> element, which should be <node test="first">.
		ReflectionHelper::invoke($this->object, 'moveToNextElement', 'node');
		$this->assertEquals('node', $this->reader->name);
		$this->assertEquals('first', $this->reader->getAttribute('test'));

		// Move to the next <node> element, which should be <node test="second">.
		ReflectionHelper::invoke($this->object, 'moveToNextElement', 'node');
		$this->assertEquals('node', $this->reader->name);
		$this->assertEquals('second', $this->reader->getAttribute('test'));
	}

	/**
	 * Tests JFeedParser::moveToClosingElement()
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedParser::moveToClosingElement
	 */
	public function testMoveToClosingElement()
	{
		// Set the XML for the internal reader and move the stream to the <root> element.
		$this->reader->XML('<root><child>foobar</child></root>');
		$this->reader->next('root');

		// Ensure that the current node is "root".
		$this->assertEquals('root', $this->reader->name);

		// Move to the closing element, which should be </root>.
		ReflectionHelper::invoke($this->object, 'moveToClosingElement');
		$this->assertEquals(XMLReader::END_ELEMENT, $this->reader->nodeType);
		$this->assertEquals('root', $this->reader->name);
	}

	/**
	 * Tests JFeedParser::moveToClosingElement() with internal elements.
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedParser::moveToClosingElement
	 */
	public function testMoveToClosingElementWithInternalElements()
	{
		// Set the XML for the internal reader and move the stream to the first <node> element.
		$this->reader->XML('<root><node test="first"><child>foobar</child></node><node test="second"></node></root>');

		// Advance the reader to the first <node> element.
		while ($this->reader->read() && ($this->reader->name != 'node'));

		// Ensure that the current node is <node test="first">.
		$this->assertEquals(XMLReader::ELEMENT, $this->reader->nodeType);
		$this->assertEquals('node', $this->reader->name);
		$this->assertEquals('first', $this->reader->getAttribute('test'));

		// Move to the closing element, which should be </node>.
		ReflectionHelper::invoke($this->object, 'moveToClosingElement');
		$this->assertEquals(XMLReader::END_ELEMENT, $this->reader->nodeType);
		$this->assertEquals('node', $this->reader->name);

		// Advance the reader to the next element.
		while ($this->reader->read() && ($this->reader->nodeType != XMLReader::ELEMENT));

		// Ensure that the current node is <node test="first">.
		$this->assertEquals(XMLReader::ELEMENT, $this->reader->nodeType);
		$this->assertEquals('node', $this->reader->name);
		$this->assertEquals('second', $this->reader->getAttribute('test'));

		// Move to the closing element, which should be </node>.
		ReflectionHelper::invoke($this->object, 'moveToClosingElement');
		$this->assertEquals(XMLReader::END_ELEMENT, $this->reader->nodeType);
		$this->assertEquals('node', $this->reader->name);
	}

	/**
	 * Tests JFeedParser::moveToClosingElement() with self-closing tags.
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedParser::moveToClosingElement
	 */
	public function testMoveToClosingElementWithSelfClosingTag()
	{
		// Set the XML for the internal reader and move the stream to the first <node> element.
		$this->reader->XML('<root><node test="first" /><node test="second"></node></root>');

		// Advance the reader to the first <node> element.
		while ($this->reader->read() && ($this->reader->name != 'node'));

		// Ensure that the current node is <node test="first">.
		$this->assertEquals(XMLReader::ELEMENT, $this->reader->nodeType);
		$this->assertEquals('node', $this->reader->name);
		$this->assertEquals('first', $this->reader->getAttribute('test'));

		// Move to the closing element, which should be </node>.
		ReflectionHelper::invoke($this->object, 'moveToClosingElement');
		$this->assertEquals(true, $this->reader->isEmptyElement);
		$this->assertEquals('node', $this->reader->name);

		// Advance the reader to the next element.
		while ($this->reader->read() && ($this->reader->nodeType != XMLReader::ELEMENT));

		// Ensure that the current node is <node test="first">.
		$this->assertEquals(XMLReader::ELEMENT, $this->reader->nodeType);
		$this->assertEquals('node', $this->reader->name);
		$this->assertEquals('second', $this->reader->getAttribute('test'));
	}
}
