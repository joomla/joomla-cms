<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('JFeedParserMock', __DIR__ . '/stubs/JFeedParserMock.php');
JLoader::register('JFeedParserProcessElementMock', __DIR__ . '/stubs/JFeedParserProcessElementMock.php');
JLoader::register('JFeedParserMockNamespace', __DIR__ . '/stubs/JFeedParserMockNamespace.php');

/**
 * Test class for JFeedParser.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.3
 */
class JFeedParserTest extends TestCase
{
	/**
	 * @var    JFeedParser
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * @var    XMLReader
	 * @since  12.3
	 */
	private $_reader;

	/**
	 * Tests JFeedParser::parse()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testParse()
	{
		// Build the mock so we can verify calls.
		$parser  = $this->getMockBuilder('JFeedParserMock')
					->setMethods(array('initialise', 'processElement'))
					->setConstructorArgs(array($this->_reader))
					->getMock();

		// Setup some expectations for the mock object.
		$parser->expects($this->once())->method('initialise');
		$parser->expects($this->exactly(2))->method('processElement');

		TestReflection::setValue($parser, 'namespaces', array('namespace' => new JFeedParserMockNamespace));

		// Set the XML for the internal reader and move the stream to the <root> element.
		$xml = '<root xmlns="http://bar.foo" xmlns:namespace="http://foo.bar"><tag1>foobar</tag1><namespace:tag2 attr="value" /></root>';
		$this->_reader->XML($xml);

		// Advance the reader to the first <tag1> element.
		do
		{
			$this->_reader->read();
		}
		while ($this->_reader->name != 'tag1');

		$parser->parse();
	}

	/**
	 * Tests JFeedParser::registerNamespace()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testRegisterNamespace()
	{
		// For a new object we nave no namespaces.
		$this->assertAttributeEmpty('namespaces', $this->_instance);

		// Add a new namespace.
		$mock = $this->getMockBuilder('JFeedParserNamespace')->getMock();
		$this->_instance->registerNamespace('foo', $mock);

		$this->assertAttributeEquals(
			array('foo' => $mock),
			'namespaces',
			$this->_instance
		);

		// Add the namespace again for a different prefix.
		$this->_instance->registerNamespace('bar', $mock);

		$this->assertAttributeEquals(
			array('foo' => $mock, 'bar' => $mock),
			'namespaces',
			$this->_instance
		);
	}

	/**
	 * Tests JFeedParser::processElement() with processing a normal element.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testProcessElementWithElement()
	{
		$el = new SimpleXMLElement('<element1></element1>');

		// Process element has a few dependencies that we need to pass: a JFeed object, an element, and namespaces.
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
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testProcessElementWithEntry()
	{
		$el = new SimpleXMLElement('<myentry></myentry>');

		// Process element has a few dependencies that we need to pass: a JFeed object, an element, and namespaces
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
	 * Tests JFeedParser::fetchNamespace()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testFetchNamespace()
	{
		// Set a mock namespace into the namespaces for the parser object.
		$mock = $this->getMockBuilder('JFeedParserNamespace')->getMock();
		$namespaces = array('mock' => $mock);
		TestReflection::setValue($this->_instance, 'namespaces', $namespaces);

		$ns = TestReflection::invoke($this->_instance, 'fetchNamespace', 'mock');
		$this->assertSame($mock, $ns, 'The mock namespace should be what is returned.');

		$ns = TestReflection::invoke($this->_instance, 'fetchNamespace', 'foobar');
		$this->assertFalse($ns, 'Since there is no foobar namespace it should return false.');

		$ns = TestReflection::invoke($this->_instance, 'fetchNamespace', 'namespace');
		$this->assertInstanceOf('JFeedParserMockNamespace', $ns, 'We should get an instance of the mock namespace.');
	}

	/**
	 * Tests JFeedParser::moveToNextElement()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMoveToNextElement()
	{
		// Set the XML for the internal reader and move the stream to the <root> element.
		$this->_reader->XML('<root><node test="first"><child>foobar</child></node><node test="second"></node></root>');
		$this->_reader->next('root');

		// Ensure that the current node is "root".
		$this->assertEquals('root', $this->_reader->name);

		// Move to the next element, which should be <node test="first">.
		TestReflection::invoke($this->_instance, 'moveToNextElement');
		$this->assertEquals('node', $this->_reader->name);
		$this->assertEquals('first', $this->_reader->getAttribute('test'));

		// Move to the next element, which should be <child> with a data value of "foobar".
		TestReflection::invoke($this->_instance, 'moveToNextElement');
		$this->assertEquals('child', $this->_reader->name);
		$this->assertEquals('foobar', $this->_reader->readString());

		// Move to the next element, which should be <node test="second">.
		TestReflection::invoke($this->_instance, 'moveToNextElement');
		$this->assertEquals('node', $this->_reader->name);
		$this->assertEquals('second', $this->_reader->getAttribute('test'));

		// Move to the next element, which should be <node test="second">.
		$return = TestReflection::invoke($this->_instance, 'moveToNextElement');
		$this->assertFalse($return);
	}

	/**
	 * Tests JFeedParser::moveToNextElement() when using the name attribute.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMoveToNextElementByName()
	{
		// Set the XML for the internal reader and move the stream to the <root> element.
		$this->_reader->XML('<root><node test="first"><child>foobar</child></node><node test="second"></node></root>');

		// Move to the next <node> element, which should be <node test="first">.
		TestReflection::invoke($this->_instance, 'moveToNextElement', 'node');
		$this->assertEquals('node', $this->_reader->name);
		$this->assertEquals('first', $this->_reader->getAttribute('test'));

		// Move to the next <node> element, which should be <node test="second">.
		TestReflection::invoke($this->_instance, 'moveToNextElement', 'node');
		$this->assertEquals('node', $this->_reader->name);
		$this->assertEquals('second', $this->_reader->getAttribute('test'));
	}

	/**
	 * Tests JFeedParser::moveToClosingElement()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMoveToClosingElement()
	{
		// Set the XML for the internal reader and move the stream to the <root> element.
		$this->_reader->XML('<root><child>foobar</child></root>');
		$this->_reader->next('root');

		// Ensure that the current node is "root".
		$this->assertEquals('root', $this->_reader->name);

		// Move to the closing element, which should be </root>.
		TestReflection::invoke($this->_instance, 'moveToClosingElement');
		$this->assertEquals(XMLReader::END_ELEMENT, $this->_reader->nodeType);
		$this->assertEquals('root', $this->_reader->name);
	}

	/**
	 * Tests JFeedParser::moveToClosingElement() with internal elements.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMoveToClosingElementWithInternalElements()
	{
		// Set the XML for the internal reader and move the stream to the first <node> element.
		$this->_reader->XML('<root><node test="first"><child>foobar</child></node><node test="second"></node></root>');

		// Advance the reader to the first <node> element.
		do
		{
			$this->_reader->read();
		}
		while ($this->_reader->name != 'node');

		// Ensure that the current node is <node test="first">.
		$this->assertEquals(XMLReader::ELEMENT, $this->_reader->nodeType);
		$this->assertEquals('node', $this->_reader->name);
		$this->assertEquals('first', $this->_reader->getAttribute('test'));

		// Move to the closing element, which should be </node>.
		TestReflection::invoke($this->_instance, 'moveToClosingElement');
		$this->assertEquals(XMLReader::END_ELEMENT, $this->_reader->nodeType);
		$this->assertEquals('node', $this->_reader->name);

		// Advance the reader to the next element.
		do
		{
			$this->_reader->read();
		}
		while ($this->_reader->nodeType != XMLReader::ELEMENT);

		// Ensure that the current node is <node test="first">.
		$this->assertEquals(XMLReader::ELEMENT, $this->_reader->nodeType);
		$this->assertEquals('node', $this->_reader->name);
		$this->assertEquals('second', $this->_reader->getAttribute('test'));

		// Move to the closing element, which should be </node>.
		TestReflection::invoke($this->_instance, 'moveToClosingElement');
		$this->assertEquals(XMLReader::END_ELEMENT, $this->_reader->nodeType);
		$this->assertEquals('node', $this->_reader->name);
	}

	/**
	 * Tests JFeedParser::moveToClosingElement() with self-closing tags.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMoveToClosingElementWithSelfClosingTag()
	{
		// Set the XML for the internal reader and move the stream to the first <node> element.
		$this->_reader->XML('<root><node test="first" /><node test="second"></node></root>');

		// Advance the reader to the first <node> element.
		do
		{
			$this->_reader->read();
		}
		while ($this->_reader->name != 'node');

		// Ensure that the current node is <node test="first">.
		$this->assertEquals(XMLReader::ELEMENT, $this->_reader->nodeType);
		$this->assertEquals('node', $this->_reader->name);
		$this->assertEquals('first', $this->_reader->getAttribute('test'));

		// Move to the closing element, which should be </node>.
		TestReflection::invoke($this->_instance, 'moveToClosingElement');
		$this->assertEquals(true, $this->_reader->isEmptyElement);
		$this->assertEquals('node', $this->_reader->name);

		// Advance the reader to the next element.
		do
		{
			$this->_reader->read();
		}
		while ($this->_reader->nodeType != XMLReader::ELEMENT);

		// Ensure that the current node is <node test="first">.
		$this->assertEquals(XMLReader::ELEMENT, $this->_reader->nodeType);
		$this->assertEquals('node', $this->_reader->name);
		$this->assertEquals('second', $this->_reader->getAttribute('test'));
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::setUp()
	 * @since   12.3
	 */
	protected function setUp()
	{
		parent::setUp();

		// Create the XMLReader object to be used in our parser instance.
		$this->_reader = new XMLReader;

		// Instantiate the mock so we can call concrete methods.
		$this->_instance = new JFeedParserMock($this->_reader);
	}

	/**
	 * Method to tear down whatever was set up before the test.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   12.3
	 */
	protected function tearDown()
	{
		unset($this->_instance);
		unset($this->_reader);

		parent::tearDown();
	}
}
