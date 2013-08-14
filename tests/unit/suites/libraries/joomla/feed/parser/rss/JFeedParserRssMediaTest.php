<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFeedParserRssMedia.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.3
 */
class JFeedParserRssMediaTest extends TestCase
{
	/**
	 * @var    JFeedParserRssMedia
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * Tests JFeedParserRssMedia::processElementForFeed()
	 *
	 * @return  void
	 *
	 * @covers  JFeedParserRssMedia::processElementForFeed
	 * @since   12.3
	 */
	public function testProcessElementForFeed()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<id>http://domain.com/path/to/resource</id>');
		$feed = new JFeed;

		$this->_instance->processElementForFeed($feed, $el);
	}

	/**
	 * Tests JFeedParserRssMedia::processElementForFeedEntry()
	 *
	 * @return  void
	 *
	 * @covers  JFeedParserRssMedia::processElementForFeedEntry
	 * @since   12.3
	 */
	public function testProcessElementForFeedEntry()
	{
		// Setup the inputs.
		$el   = new SimpleXMLElement('<id>http://domain.com/path/to/resource</id>');
		$feedEntry = new JFeedEntry;

		$this->_instance->processElementForFeedEntry($feedEntry, $el);
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

		$this->_instance = new JFeedParserRssMedia;
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

		parent::teardown();
	}
}
