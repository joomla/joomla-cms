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
 * Test class for JFeed.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.1
 */
class JFeedTest extends JoomlaTestCase
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

		$this->object = new JFeed(/* parameters */);
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
	 * Tests the JFeed->__construct method.
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
		public function testConstruct()
	{
		$this->markTestIncomplete("__construct test not implemented");

		$this->object->__construct(/* parameters */);
	}

	/**
	 * Tests JFeed->__get()
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
	 * Tests JFeed->__set()
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
	 * Tests JFeed->addCategory()
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
	 * Tests JFeed->addContributor()
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
	 * Tests JFeed->addEntry()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testAddEntry()
	{
		$this->markTestIncomplete("addEntry test not implemented");

		$this->object->addEntry(/* parameters */);
	}

	/**
	 * Tests JFeed->offsetExists()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testOffsetExists()
	{
		$this->markTestIncomplete("offsetExists test not implemented");

		$this->object->offsetExists(/* parameters */);
	}

	/**
	 * Tests JFeed->offsetGet()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testOffsetGet()
	{
		$this->markTestIncomplete("offsetGet test not implemented");

		$this->object->offsetGet(/* parameters */);
	}

	/**
	 * Tests JFeed->offsetSet()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testOffsetSet()
	{
		$this->markTestIncomplete("offsetSet test not implemented");

		$this->object->offsetSet(/* parameters */);
	}

	/**
	 * Tests JFeed->offsetUnset()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testOffsetUnset()
	{
		$this->markTestIncomplete("offsetUnset test not implemented");

		$this->object->offsetUnset(/* parameters */);
	}

	/**
	 * Tests JFeed->removeCategory()
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
	 * Tests JFeed->removeContributor()
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
	 * Tests JFeed->removeEntry()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testRemoveEntry()
	{
		$this->markTestIncomplete("removeEntry test not implemented");

		$this->object->removeEntry(/* parameters */);
	}

	/**
	 * Tests JFeed->setAuthor()
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
