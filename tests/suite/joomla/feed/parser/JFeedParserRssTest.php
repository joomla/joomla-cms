<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/feed/parser.php';
require_once JPATH_PLATFORM . '/joomla/feed/parser/rss.php';

/**
 * Test class for JFeedParserRss.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.1
 */
class JFeedParserRssTest extends JoomlaTestCase
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
		parent::tearDown();
	}

	/**
	 * Tests the JFeedParserRss->__construct method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testConstructor()
	{
		$stream = new XMLReader;
		$stream->open(JPATH_TESTS . '/suite/joomla/feed/stubs/samples/rss/media.xml');

		// Skip ahead to the root node.
		while ($stream->read() && ($stream->nodeType !== XMLReader::ELEMENT));

		$parser = new JFeedParserRss($stream);

		$feed = $parser->parse();
		//print_r($feed);
	}

	/**
	 * Tests JFeedParserRss->detectVersion()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testDetectVersion()
	{
		$this->markTestIncomplete("detectVersion test not implemented");

		$this->object->detectVersion(/* parameters */);
	}

	/**
	 * Tests JFeedParserRss->handleTitle()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleTitle()
	{
		$this->markTestIncomplete("handleTitle test not implemented");

		$this->object->handleTitle(/* parameters */);
	}

	/**
	 * Tests JFeedParserRss->handleLink()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleLink()
	{
		$this->markTestIncomplete("handleLink test not implemented");

		$this->object->handleLink(/* parameters */);
	}

	/**
	 * Tests JFeedParserRss->handleDescription()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleDescription()
	{
		$this->markTestIncomplete("handleDescription test not implemented");

		$this->object->handleDescription(/* parameters */);
	}

	/**
	 * Tests JFeedParserRss->handleLanguage()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleLanguage()
	{
		$this->markTestIncomplete("handleLanguage test not implemented");

		$this->object->handleLanguage(/* parameters */);
	}

	/**
	 * Tests JFeedParserRss->handleCopyright()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleCopyright()
	{
		$this->markTestIncomplete("handleCopyright test not implemented");

		$this->object->handleCopyright(/* parameters */);
	}

	/**
	 * Tests JFeedParserRss->handleLastBuildDate()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleLastBuildDate()
	{
		$this->markTestIncomplete("handleLastBuildDate test not implemented");

		$this->object->handleLastBuildDate(/* parameters */);
	}

	/**
	 * Tests JFeedParserRss->handlePubDate()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandlePubDate()
	{
		$this->markTestIncomplete("handlePubDate test not implemented");

		$this->object->handlePubDate(/* parameters */);
	}

	/**
	 * Tests JFeedParserRss->handleManagingEditor()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleManagingEditor()
	{
		$this->markTestIncomplete("handleManagingEditor test not implemented");

		$this->object->handleManagingEditor(/* parameters */);
	}

	/**
	 * Tests JFeedParserRss->handleWebmaster()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleWebmaster()
	{
		$this->markTestIncomplete("handleWebmaster test not implemented");

		$this->object->handleWebmaster(/* parameters */);
	}

	/**
	 * Tests JFeedParserRss->handleGenerator()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleGenerator()
	{
		$this->markTestIncomplete("handleGenerator test not implemented");

		$this->object->handleGenerator(/* parameters */);
	}

	/**
	 * Tests JFeedParserRss->handleCloud()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleCloud()
	{
		$this->markTestIncomplete("handleCloud test not implemented");

		$this->object->handleCloud(/* parameters */);
	}

	/**
	 * Tests JFeedParserRss->processFeedEntry()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testProcessFeedEntry()
	{
		$this->markTestIncomplete("processFeedEntry test not implemented");

		$this->object->processFeedEntry(/* parameters */);
	}
}
