<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/feed/factory.php';

/**
 * Test class for JFeedFactory.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.1
 */
class JFeedFactoryTest extends JoomlaTestCase
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

		$this->object = new JFeedFactory($this->getMock('JHttp'));
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
	 * Tests the JFeedFactory->__construct method.
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testConstructor()
	{
		$mockHttp = $this->getMock('JHttp', array('test'), array(), '', false);
		$mockHttp->expects($this->any())
			->method('test')
			->will($this->returnValue('ok'));

		$factory = new JFeedFactory($mockHttp);

		$this->assertThat(ReflectionHelper::getValue($factory, 'http')->test(), $this->equalTo('ok'), 'Tests http client injection.');
	}

	/**
	 * Tests JFeedFactory->getFeed()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testGetFeed()
	{
		$this->markTestIncomplete("getFeed test not implemented");

		$this->object->getFeed(/* parameters */);
	}

	/**
	 * Tests JFeedFactory->registerParser()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testRegisterParser()
	{
		$this->markTestIncomplete("registerParser test not implemented");

		$this->object->registerParser(/* parameters */);
	}

	/**
	 * Tests JFeedFactory->_fetchFeedParser()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function test_fetchFeedParser()
	{
		$this->markTestIncomplete("_fetchFeedParser test not implemented");

		$this->object->_fetchFeedParser(/* parameters */);
	}
}
