<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/feed/parser.php';

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

		// Create the abstract mock so we can call concrete methods.
		$this->object = $this->getMockForAbstractClass('JFeedParser', array($this->reader));
		$this->object->expects($this->any())
			->method('detectVersion')
			->will($this->returnValue('1.0'));
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

		parent::tearDown();
	}

	/**
	 * Tests the JFeedParser->__construct method.
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testConstructor()
	{
		$this->markTestIncomplete("__construct test not implemented");
	}

	/**
	 * Tests JFeedParser->parse()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testParse()
	{
		$this->markTestIncomplete("parse test not implemented");

		$this->object->parse();
	}

	/**
	 * Tests JFeedParser->registerNamespace()
	 *
	 * @return void
	 *
	 * @since 12.1
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
	 * Tests JFeedParser->registerNamespace() with an expected failure.  Cannot register a string.
	 *
	 * @return void
	 *
	 * @expectedException  PHPUnit_Framework_Error
	 * @since 12.1
	 */
	public function testRegisterNamespaceWithString()
	{
		$this->object->registerNamespace('foo', 'bar');
	}

	/**
	 * Tests JFeedParser->registerNamespace() with an expected failure.  Cannot register a handler
	 * that isn't an instance of JFeedParserNamespace.
	 *
	 * @return void
	 *
	 * @expectedException  PHPUnit_Framework_Error
	 * @since 12.1
	 */
	public function testRegisterNamespaceWithObject()
	{
		$this->object->registerNamespace('foo', new stdClass);
	}

	/**
	 * Tests JFeedParser->processElement()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testProcessElement()
	{
		$this->markTestIncomplete("processElement test not implemented");

		$this->object->processElement(/* parameters */);
	}

	/**
	 * Tests JFeedParser->expandToSimpleXml()
	 *
	 * @return void
	 *
	 * @since 12.1
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
	 * Tests JFeedParser->fetchNamespace()
	 *
	 * @return void
	 *
	 * @since 12.1
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
	}

	/**
	 * Tests JFeedParser->moveToNextElement()
	 *
	 * @return void
	 *
	 * @since 12.1
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
	}

	/**
	 * Tests JFeedParser->moveToNextElement() when using the name attribute.
	 *
	 * @return void
	 *
	 * @since 12.1
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
	 * Tests JFeedParser->moveToClosingElement()
	 *
	 * @return void
	 *
	 * @since 12.1
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
	 * Tests JFeedParser->moveToClosingElement() with internal elements.
	 *
	 * @return void
	 *
	 * @since 12.1
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
	 * Tests JFeedParser->moveToClosingElement() with self-closing tags.
	 *
	 * @return void
	 *
	 * @since 12.1
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
