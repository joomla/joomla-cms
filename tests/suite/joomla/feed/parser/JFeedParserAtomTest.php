<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/feed/parser.php';
require_once JPATH_PLATFORM . '/joomla/feed/parser/atom.php';

/**
 * Test class for JFeedParserAtom.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.1
 */
class JFeedParserAtomTest extends JoomlaTestCase
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
	 * Tests the JFeedParserAtom::__construct method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testConstructor()
	{
		$stream = new XMLReader;
		$stream->open(JPATH_TESTS . '/suite/joomla/feed/stubs/samples/atom/1.0.xml');

		// Skip ahead to the root node.
		while ($stream->read() && ($stream->nodeType !== XMLReader::ELEMENT));

		$parser = new JFeedParserAtom($stream);

		$feed = $parser->parse();
		//print_r($feed);
	}

	/**
	 * Tests JFeedParserAtom->detectVersion()
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
	 * Tests JFeedParserAtom->handleId()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleId()
	{
		$this->markTestIncomplete("handleId test not implemented");

		$this->object->handleId(/* parameters */);
	}

	/**
	 * Tests JFeedParserAtom->handleTitle()
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
	 * Tests JFeedParserAtom->handleGenerator()
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
	 * Tests JFeedParserAtom->handleSubtitle()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleSubtitle()
	{
		$this->markTestIncomplete("handleSubtitle test not implemented");

		$this->object->handleSubtitle(/* parameters */);
	}

	/**
	 * Tests JFeedParserAtom->handleRights()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleRights()
	{
		$this->markTestIncomplete("handleRights test not implemented");

		$this->object->handleRights(/* parameters */);
	}

	/**
	 * Tests JFeedParserAtom->handleUpdated()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleUpdated()
	{
		$this->markTestIncomplete("handleUpdated test not implemented");

		$this->object->handleUpdated(/* parameters */);
	}

	/**
	 * Tests JFeedParserAtom->handleLink()
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
	 * Tests JFeedParserAtom->handleAuthor()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleAuthor()
	{
		$this->markTestIncomplete("handleAuthor test not implemented");

		$this->object->handleAuthor(/* parameters */);
	}

	/**
	 * Tests JFeedParserAtom->handleContributor()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testHandleContributor()
	{
		$this->markTestIncomplete("handleContributor test not implemented");

		$this->object->handleContributor(/* parameters */);
	}

	/**
	 * Tests JFeedParserAtom->processFeedEntry()
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
