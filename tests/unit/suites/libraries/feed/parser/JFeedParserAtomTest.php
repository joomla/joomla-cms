<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFeedParserAtom.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.0
 */
class JFeedParserAtomTest extends TestCase
{
	/**
	 * @var  JFeedParserAtom
	 */
	protected $object;

	/**
	 * @var  XMLReader
	 */
	protected $reader;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::setUp()
	 * @since   3.0
	 */
	protected function setUp()
	{
		parent::setUp();

		// Create the XMLReader object to be used in our parser.
		$this->reader = new XMLReader;

		// Create the parser object.
		$this->object = new JFeedParserAtom($this->reader);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.0
	 */
	protected function tearDown()
	{
		$this->object = null;
		$this->reader = null;

		parent::tearDown();
	}

	/**
	 * Method to seed data for detecting feed version.
	 *
	 * @return  array
	 *
	 * @since   3.0
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
	 * @since   3.0
	 *
	 * @covers  JFeedParserAtom::handleAuthor
	 */
	public function testHandleAuthor()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<author><name>John Doe</name><email>john@doe.name</email><uri>http://doe.name</uri></author>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleAuthor', $feed, $el);

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
	 * @since   3.0
	 *
	 * @covers  JFeedParserAtom::handleContributor
	 */
	public function testHandleContributor()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<contributor><name>Jane Doe</name><email>jane@example.com</email></contributor>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleContributor', $feed, $el);

		$expected = new JFeedPerson('Jane Doe', 'jane@example.com');
		$this->assertTrue(in_array($expected, $feed->contributors));
	}

	/**
	 * Tests JFeedParserAtom::handleGenerator()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserAtom::handleGenerator
	 */
	public function testHandleGenerator()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<generator>Joomla</generator>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleGenerator', $feed, $el);

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
	 * @since   3.0
	 *
	 * @covers  JFeedParserAtom::handleId
	 */
	public function testHandleId()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<id>http://domain.com/path/to/resource</id>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleId', $feed, $el);

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
	 * @since   3.0
	 *
	 * @covers  JFeedParserAtom::handleLink
	 */
	public function testHandleLink()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<link href="http://domain.com/" />');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleLink', $feed, $el);

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
	 * @since   3.0
	 *
	 * @covers  JFeedParserAtom::handleRights
	 */
	public function testHandleRights()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<rights>All Rights Reserved.</rights>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleRights', $feed, $el);

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
	 * @since   3.0
	 *
	 * @covers  JFeedParserAtom::handleSubtitle
	 */
	public function testHandleSubtitle()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<subtitle>Lorem Ipsum ...</subtitle>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleSubtitle', $feed, $el);

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
	 * @since   3.0
	 *
	 * @covers  JFeedParserAtom::handleTitle
	 */
	public function testHandleTitle()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<title>My Title</title>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleTitle', $feed, $el);

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
	 * @since   3.0
	 *
	 * @covers  JFeedParserAtom::handleUpdated
	 */
	public function testHandleUpdated()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<updated>2011-01-01T00:00:00Z</updated>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleUpdated', $feed, $el);

		$expected = new JDate('2011-01-01');
		$this->assertEquals(
			$expected->toUnix(),
			$feed->updatedDate->toUnix()
		);
	}

	/**
	 * Tests JFeedParserAtom::initialise()
	 *
	 * @param   string  $expected  The expected test value
	 * @param   string  $xml       The XML string being tested
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserAtom::initialise
	 * @dataProvider  seedInitialise
	 */
	public function testInitialise($expected, $xml)
	{
		// Set the XML for the internal reader.
		$this->reader->XML($xml);

		// Advance the reader to the first element.
		while ($this->reader->read() && ($this->reader->nodeType != XMLReader::ELEMENT));

		TestReflection::invoke($this->object, 'initialise');

		$this->assertAttributeEquals(
			$expected,
			'version',
			$this->object,
			'The version string detected should match the expected value.'
		);

		// Verify that after detecting the version we are ready to start parsing.
		$this->assertEquals(
			'test',
			$this->reader->name
		);
		$this->assertEquals(
			XMLReader::ELEMENT,
			$this->reader->nodeType
		);
	}

	/**
	 * Tests JFeedParserAtom::processFeedEntry()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserAtom::processFeedEntry
	 */
	public function testProcessFeedEntry()
	{
		$entry = new JFeedEntry;
		$el = new SimpleXMLElement('<entry><id>http://example.com/id</id><title>title</title><updated>August 25, 1991</updated><summary>summary</summary></entry>');

		TestReflection::invoke($this->object, 'processFeedEntry', $entry, $el);

		$this->assertEquals('http://example.com/id', $entry->uri);
		$this->assertEquals('title', $entry->title);
		$this->assertInstanceOf('JDate', $entry->updatedDate);
		$this->assertEquals('summary', $entry->content);
	}
}
