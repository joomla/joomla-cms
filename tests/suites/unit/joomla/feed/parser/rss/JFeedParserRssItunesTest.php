<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFeedParserRssItunes.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.3
 */
class JFeedParserRssItunesTest extends TestCase
{
	/**
	 * @var    JFeedParserRssItunes
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * Tests JFeedParserRssItunes::processElementForFeed()
	 *
	 * @return  void
	 *
	 * @covers  JFeedParserRssItunes::processElementForFeed
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
	 * Tests JFeedParserRssItunes::processElementForFeedEntry()
	 *
	 * @return  void
	 *
	 * @covers  JFeedParserRssItunes::processElementForFeedEntry
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

		$this->_instance = new JFeedParserRssItunes;
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
