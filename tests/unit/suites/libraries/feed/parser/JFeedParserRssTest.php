<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFeedParserRss.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.0
 */
class JFeedParserRssTest extends TestCase
{
	/**
	 * @var  JFeedParserRss
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
		$this->object = new JFeedParserRss($this->reader);
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
	 * Method to seed data for parser initialisation.
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public function seedInitialise()
	{
		return array(
			array('0.91', '<rss version="0.91"><channel><test /></channel></rss>'),
			array('0.92', '<rss version="0.92"><channel><test /></channel></rss>'),
			array('2.0',  '<rss version="2.0"><channel><test /></channel></rss>')
		);
	}

	/**
	 * Method to seed data for person parsing.
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public function seedProcessPerson()
	{
		return array(
			array('webmaster@domain.com (Webmaster)', 'Webmaster', 'webmaster@domain.com'),
			array('webmaster@domain.com (Webmaster)', 'Webmaster', 'webmaster@domain.com'),
			array('webmaster@domain.com (Webmaster)', 'Webmaster', 'webmaster@domain.com'),
			array('webmaster@domain.com (Webmaster)', 'Webmaster', 'webmaster@domain.com')
		);
	}

	/**
	 * Tests JFeedParserRss::handleCategory()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleCategory
	 */
	public function testHandleCategory()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<category>IT/Internet/Web development</category>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleCategory', $feed, $el);

		// Ensure that the clound property is an object.
		$this->assertInternalType('array', $feed->categories);

		$this->assertEquals(
			array('IT/Internet/Web development' => ''),
			$feed->categories
		);
	}

	/**
	 * Tests JFeedParserRss::handleCloud()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleCloud
	 */
	public function testHandleCloud()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<cloud domain="domain.com" port="80" path="/RPC" registerProcedure="autoNotify" protocol="xml-rpc" />');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleCloud', $feed, $el);

		// Ensure that the clound property is an object.
		$this->assertInternalType('object', $feed->cloud);

		$this->assertEquals(
			'domain.com',
			$feed->cloud->domain
		);
		$this->assertEquals(
			'80',
			$feed->cloud->port
		);
		$this->assertEquals(
			'/RPC',
			$feed->cloud->path
		);
		$this->assertEquals(
			'xml-rpc',
			$feed->cloud->protocol
		);
		$this->assertEquals(
			'autoNotify',
			$feed->cloud->registerProcedure
		);
	}

	/**
	 * Tests JFeedParserRss::handleCopyright()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleCopyright
	 */
	public function testHandleCopyright()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<copyright>All Rights Reserved.</copyright>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleCopyright', $feed, $el);

		$this->assertEquals(
			'All Rights Reserved.',
			$feed->copyright
		);
	}

	/**
	 * Tests JFeedParserRss::handleDescription()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleDescription
	 */
	public function testHandleDescription()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<description>Lorem Ipsum ...</description>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleDescription', $feed, $el);

		$this->assertEquals(
			'Lorem Ipsum ...',
			$feed->description
		);
	}

	/**
	 * Tests JFeedParserRss::handleGenerator()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleGenerator
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
	 * Tests JFeedParserRss::handleImage()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleImage
	 */
	public function testHandleImage()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<image><url>http://www.w3schools.com/images/logo.gif</url><title>W3Schools.com</title><link>http://www.w3schools.com</link></image>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleImage', $feed, $el);

		$this->assertInstanceOf('JFeedLink', $feed->image);

		$this->assertEquals(
			'http://www.w3schools.com/images/logo.gif',
			$feed->image->uri
		);

		$this->assertEquals(
			'W3Schools.com',
			$feed->image->title
		);

		$this->assertEquals(
			'http://www.w3schools.com',
			$feed->image->link
		);
	}

	/**
	 * Tests JFeedParserRss::handleLanguage()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleLanguage
	 */
	public function testHandleLanguage()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<language>en-US</language>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleLanguage', $feed, $el);

		$this->assertEquals(
			'en-US',
			$feed->language
		);
	}

	/**
	 * Tests JFeedParserRss::handleLastBuildDate()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleLastBuildDate
	 */
	public function testHandleLastBuildDate()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<lastBuildDate>Sat, 01 Jan 2011 00:00:00 UTC</lastBuildDate>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleLastBuildDate', $feed, $el);

		$expected = new JDate('2011-01-01');
		$this->assertEquals(
			$expected->toUnix(),
			$feed->updatedDate->toUnix()
		);
	}

	/**
	 * Tests JFeedParserRss::handleLink()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleLink
	 */
	public function testHandleLink()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<link>http://domain.com/path/to/resource</link>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleLink', $feed, $el);

		$this->assertEquals(
			'http://domain.com/path/to/resource',
			$feed->uri
		);
	}

	/**
	 * Tests JFeedParserRss::handleManagingEditor()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleManagingEditor
	 */
	public function testHandleManagingEditor()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<managingEditor>editor@domain.com (The Editor)</managingEditor>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleManagingEditor', $feed, $el);

		$this->assertEquals(
			new JFeedPerson('The Editor', 'editor@domain.com'),
			$feed->author
		);
	}

	/**
	 * Tests JFeedParserRss::handlePubDate()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handlePubDate
	 */
	public function testHandlePubDate()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<pubDate>Sat, 01 Jan 2011 00:00:00 GMT</pubDate>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handlePubDate', $feed, $el);

		$expected = new JDate('2011-01-01');
		$this->assertEquals(
			$expected->toUnix(),
			$feed->publishedDate->toUnix()
		);
	}

	/**
	 * Tests JFeedParserRss::handleSkipDays()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleSkipDays
	 */
	public function testHandleSkipDays()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<skipDays><day>Saturday</day><day>Sunday</day></skipDays>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleSkipDays', $feed, $el);

		// Ensure that the clound property is an object.
		$this->assertInternalType('array', $feed->skipDays);

		$this->assertEquals(
			array('Saturday', 'Sunday'),
			$feed->skipDays
		);
	}

	/**
	 * Tests JFeedParserRss::handleSkipHours()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleSkipHours
	 */
	public function testHandleSkipHours()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<skipHours><hour>0</hour><hour>10</hour></skipHours>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleSkipHours', $feed, $el);

		// Ensure that the clound property is an object.
		$this->assertInternalType('array', $feed->skipHours);

		$this->assertEquals(
			array(0, 10),
			$feed->skipHours
		);
	}

	/**
	 * Tests JFeedParserRss::handleTitle()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleTitle
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
	 * Tests JFeedParserRss::handleTtl()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleTtl
	 */
	public function testHandleTtl()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<ttl>45</ttl>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleTtl', $feed, $el);

		$this->assertEquals(
			45,
			$feed->ttl
		);
	}

	/**
	 * Tests JFeedParserRss::handleWebmaster()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::handleWebmaster
	 */
	public function testHandleWebmaster()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<webmaster>webmaster@domain.com (The Webmaster)</webmaster>');
		$feed = new JFeed;

		TestReflection::invoke($this->object, 'handleWebmaster', $feed, $el);

		$expected = new JFeedPerson('The Webmaster', 'webmaster@domain.com', '', 'webmaster');
		$this->assertTrue(in_array($expected, $feed->contributors));
	}

	/**
	 * Tests JFeedParserRss::initialise()
	 *
	 * @param   string  $expected  The expected rss version string.
	 * @param   string  $xml       The XML string for which to detect the version.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::initialise
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
	 * Tests JFeedParserRss::processFeedEntry()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::processFeedEntry
	 */
	public function testProcessFeedEntry()
	{
		$entry = new JFeedEntry;
		$el = new SimpleXMLElement(
			'<entry>
				<link>http://example.com/id</link>
				<title>title</title>
				<pubDate>August 25, 1991</pubDate>
				<description>description</description>
				<category>category</category>
				<author>admin@domain.com (Webmaster)</author>
				<enclosure url="http://www.w3schools.com/media/3d.wmv" length="78645" type="video/wmv" />
			</entry>'
		);

		TestReflection::invoke($this->object, 'processFeedEntry', $entry, $el);

		$this->assertEquals('http://example.com/id', $entry->uri);
		$this->assertEquals('title', $entry->title);
		$this->assertInstanceOf('JDate', $entry->updatedDate);
		$this->assertInstanceOf('JDate', $entry->publishedDate);
		$this->assertEquals('description', $entry->content);
		$this->assertEquals(array('category' => ''), $entry->categories);
		$this->assertInstanceOf('JFeedPerson', $entry->author);
		$this->assertEquals('Webmaster', $entry->author->name);
		$this->assertEquals('admin@domain.com', $entry->author->email);
		$this->assertEquals(1, count($entry->links));
		$this->assertInstanceOf('JFeedLink', $entry->links[0]);
		$this->assertEquals('http://www.w3schools.com/media/3d.wmv', $entry->links[0]->uri);
		$this->assertEquals('video/wmv', $entry->links[0]->type);
		$this->assertEquals(78645, $entry->links[0]->length);
	}

	/**
	 * Tests JFeedParserRss::processPerson()
	 *
	 * @param   string  $input  The input value being tested
	 * @param   string  $name   The name being tested
	 * @param   string  $email  The e-mail being tested
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeedParserRss::processPerson
	 * @dataProvider  seedProcessPerson
	 */
	public function testProcessPerson($input, $name, $email)
	{
		$person = TestReflection::invoke($this->object, 'processPerson', $input);

		$this->assertInstanceOf('JFeedPerson', $person);

		$this->assertEquals($name, $person->name);
		$this->assertEquals($email, $person->email);
	}
}
