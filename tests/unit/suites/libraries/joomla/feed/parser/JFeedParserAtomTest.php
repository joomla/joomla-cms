<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFeedParserAtom.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.3
 */
class JFeedParserAtomTest extends TestCase
{
	/**
	 * @var    JFeedParserAtom
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * @var    XMLReader
	 * @since  12.3
	 */
	private $_reader;

	/**
	 * Method to seed data for detecting feed version.
	 *
	 * @return  array
	 *
	 * @since   12.3
	 */
	public function seedInitialise()
	{
		return array(
			array('0.3', '<feed version="0.3" xmlns="http://purl.org/atom/ns#"><test /></feed>'),
			array('1.0', '<feed xmlns="http://www.w3.org/2005/Atom"><test /></feed>')
		);
	}

	/**
	 * Tests JFeedParserAtom::handleAuthor()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHandleAuthor()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<author><name>John Doe</name><email>john@doe.name</email><uri>http://doe.name</uri></author>');
		$feed = new JFeed;

		TestReflection::invoke($this->_instance, 'handleAuthor', $feed, $el);

		$expected = new JFeedPerson('John Doe', 'john@doe.name', 'http://doe.name');
		$this->assertEquals(
			$expected,
			$feed->author
		);
	}

	/**
	 * Tests JFeedParserAtom::handleContributor()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHandleContributor()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<contributor><name>Jane Doe</name><email>jane@example.com</email></contributor>');
		$feed = new JFeed;

		TestReflection::invoke($this->_instance, 'handleContributor', $feed, $el);

		$expected = new JFeedPerson('Jane Doe', 'jane@example.com');
		$this->assertTrue(in_array($expected, $feed->contributors));
	}

	/**
	 * Tests JFeedParserAtom::handleGenerator()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHandleGenerator()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<generator>Joomla</generator>');
		$feed = new JFeed;

		TestReflection::invoke($this->_instance, 'handleGenerator', $feed, $el);

		$this->assertEquals(
			'Joomla',
			$feed->generator
		);
	}

	/**
	 * Tests JFeedParserAtom::handleId()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHandleId()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<id>http://domain.com/path/to/resource</id>');
		$feed = new JFeed;

		TestReflection::invoke($this->_instance, 'handleId', $feed, $el);

		$this->assertEquals(
			'http://domain.com/path/to/resource',
			$feed->uri
		);
	}

	/**
	 * Tests JFeedParserAtom::handleLink()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHandleLink()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<link href="http://domain.com/" />');
		$feed = new JFeed;

		TestReflection::invoke($this->_instance, 'handleLink', $feed, $el);

		$expected = new JFeedLink('http://domain.com/');
		$this->assertEquals(
			$expected,
			$feed->link
		);
	}

	/**
	 * Tests JFeedParserAtom::handleRights()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHandleRights()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<rights>All Rights Reserved.</rights>');
		$feed = new JFeed;

		TestReflection::invoke($this->_instance, 'handleRights', $feed, $el);

		$this->assertEquals(
			'All Rights Reserved.',
			$feed->copyright
		);
	}

	/**
	 * Tests JFeedParserAtom::handleSubtitle()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHandleSubtitle()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<subtitle>Lorem Ipsum ...</subtitle>');
		$feed = new JFeed;

		TestReflection::invoke($this->_instance, 'handleSubtitle', $feed, $el);

		$this->assertEquals(
			'Lorem Ipsum ...',
			$feed->description
		);
	}

	/**
	 * Tests JFeedParserAtom::handleTitle()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHandleTitle()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<title>My Title</title>');
		$feed = new JFeed;

		TestReflection::invoke($this->_instance, 'handleTitle', $feed, $el);

		$this->assertEquals(
			'My Title',
			$feed->title
		);
	}

	/**
	 * Tests JFeedParserAtom::handleUpdated()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testHandleUpdated()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<updated>2011-01-01T00:00:00Z</updated>');
		$feed = new JFeed;

		TestReflection::invoke($this->_instance, 'handleUpdated', $feed, $el);

		$expected = new JDate('2011-01-01');
		$this->assertEquals(
			$expected->toUnix(),
			$feed->updatedDate->toUnix()
		);
	}

	/**
	 * Tests JFeedParserAtom::initialise()
	 *
	 * @param   string  $expected  The expected version number.
	 * @param   string  $xml       The <feed> tag from which to detect the version.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedInitialise
	 * @since         12.3
	 */
	public function testInitialise($expected, $xml)
	{
		// Set the XML for the internal reader.
		$this->_reader->Xml($xml);

		// Advance the reader to the first element.
		do
		{
			$this->_reader->read();
		}
		while ($this->_reader->nodeType != XMLReader::ELEMENT);

		TestReflection::invoke($this->_instance, 'initialise');

		$this->assertAttributeEquals(
			$expected,
			'version',
			$this->_instance,
			'The version string detected should match the expected value.'
		);

		// Verify that after detecting the version we are ready to start parsing.
		$this->assertEquals(
			'test',
			$this->_reader->name
		);
		$this->assertEquals(
			XMLReader::ELEMENT,
			$this->_reader->nodeType
		);
	}

	/**
	 * Tests JFeedParserAtom::processFeedEntry()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testProcessFeedEntry()
	{
		$xml = '<entry><id>http://example.com/id</id><title>title</title><updated>August 25, 1991</updated><summary>summary</summary></entry>';
		$entry = new JFeedEntry;
		$el = new SimpleXMLElement($xml);

		TestReflection::invoke($this->_instance, 'processFeedEntry', $entry, $el);

		$this->assertEquals('http://example.com/id', $entry->uri);
		$this->assertEquals('title', $entry->title);
		$this->assertInstanceOf('JDate', $entry->updatedDate);
		$this->assertEquals('summary', $entry->content);
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

		// Instantiate the parser object.
		$this->_instance = new JFeedParserAtom($this->_reader);
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

		parent::teardown();
	}
}
