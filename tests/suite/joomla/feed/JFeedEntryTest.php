<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/feed/feed.php';

/**
 * Test class for JFeedEntry.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.1
 */
class JFeedEntryTest extends JoomlaTestCase
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

		$this->object = new JFeedEntry(/* parameters */);
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
	 * Tests JFeedEntry->__construct()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function test__construct()
	{
		$this->markTestIncomplete("__construct test not implemented");

		$this->object->__construct(/* parameters */);
	}

	/**
	 * Tests JFeedEntry->__get()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function test__get()
	{
		$this->markTestIncomplete("__get test not implemented");

		$this->object->__get(/* parameters */);
	}

	/**
	 * Tests JFeedEntry->__set()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function test__set()
	{
		$this->markTestIncomplete("__set test not implemented");

		$this->object->__set(/* parameters */);
	}

	/**
	 * Tests JFeedEntry->addCategory()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testAddCategory()
	{
		$this->markTestIncomplete("addCategory test not implemented");

		$this->object->addCategory(/* parameters */);
	}

	/**
	 * Tests JFeedEntry->addContributor()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testAddContributor()
	{
		$this->markTestIncomplete("addContributor test not implemented");

		$this->object->addContributor(/* parameters */);
	}

	/**
	 * Tests JFeedEntry->addLink()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testAddLink()
	{
		$this->markTestIncomplete("addLink test not implemented");

		$this->object->addLink(/* parameters */);
	}

	/**
	 * Tests JFeedEntry->removeCategory()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testRemoveCategory()
	{
		$this->markTestIncomplete("removeCategory test not implemented");

		$this->object->removeCategory(/* parameters */);
	}

	/**
	 * Tests JFeedEntry->removeContributor()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testRemoveContributor()
	{
		$this->markTestIncomplete("removeContributor test not implemented");

		$this->object->removeContributor(/* parameters */);
	}

	/**
	 * Tests JFeedEntry->removeLink()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testRemoveLink()
	{
		$this->markTestIncomplete("removeLink test not implemented");

		$this->object->removeLink(/* parameters */);
	}

	/**
	 * Tests JFeedEntry->setAuthor()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testSetAuthor()
	{
		$this->markTestIncomplete("setAuthor test not implemented");

		$this->object->setAuthor(/* parameters */);
	}
}
