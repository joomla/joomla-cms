<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('JFeedParserMock', __DIR__ . '/stubs/JFeedParserMock.php');
JLoader::register('JFeedParserMockNamespace', __DIR__ . '/stubs/JFeedParserMockNamespace.php');

/**
 * Test class for JFeedFactory.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 */
class JFeedFactoryTest extends TestCase
{
	/**
	 * @var    JFeedFactory
	 */
	private $_instance;

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		$this->_instance = new JFeedFactory;
	}

	/**
	 * Method to tear down whatever was set up before the test.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		unset($this->_instance);
	}

	/**
	 * Tests JFeedFactory::getFeed() with a bad feed.
	 *
	 * @return  void
	 *
	 * @expectedException  RuntimeException
	 */
	public function testGetFeedBad()
	{
		$this->markTestSkipped('This test is failing to execute and is locking up the test suite.');
		$this->_instance->getFeed(JPATH_TEST_STUBS . '/feed/test.bad.feed');
	}

	/**
	 * Tests JFeedFactory::getFeed() with a bad feed.
	 *
	 * @return  void
	 *
	 * @expectedException  LogicException
	 */
	public function testGetFeedNoParser()
	{
		$this->_instance->getFeed(JPATH_TEST_STUBS . '/feed/test.myfeed.feed');
	}

	/**
	 * Tests JFeedFactory::getFeed() with an idn feed.
	 *
	 * @return  void
	 *
	 * @medium
	 * @expectedException  RuntimeException
	 */
	public function testGetFeedIdn()
	{
		$this->_instance->getFeed('http://джумла-тест.рф/master/article-category-blog?format=feed&type=rss');
	}

	/**
	 * Tests JFeedFactory::getFeed() with a feed parser.
	 *
	 * @return  void
	 */
	public function testGetFeedMockParser()
	{
		$this->_instance->registerParser('myfeed', 'JFeedParserMock', true);
		JFeedParserMock::$parseReturn = 'test';

		$this->assertEquals(
			'test',
			$this->_instance->getFeed(JPATH_TEST_STUBS . '/feed/test.myfeed.feed')
		);
	}

	/**
	 * Tests JFeedFactory::getFeed()
	 *
	 * @return  void
	 */
	public function testGetFeed()
	{
		$this->assertInstanceOf(
			'JFeed',
			$this->_instance->getFeed(JPATH_TEST_STUBS . '/feed/test.feed')
		);
	}

	/**
	 * Tests JFeedFactory::registerParser()
	 *
	 * @return  void
	 */
	public function testRegisterParser()
	{
		TestReflection::setValue($this->_instance, 'parsers', array());

		$this->_instance->registerParser('mock', 'JFeedParserMock');

		$this->assertNotEmpty(
			TestReflection::getValue($this->_instance, 'parsers')
		);
	}

	/**
	 * Tests JFeedFactory::registerParser()
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 */
	public function testRegisterParserWithInvalidClass()
	{
		TestReflection::setValue($this->_instance, 'parsers', array());

		$this->_instance->registerParser('mock', 'JFeedParserMocks');

		$this->assertNotEmpty(
			TestReflection::getValue($this->_instance, 'parsers')
		);
	}

	/**
	 * Tests JFeedFactory::registerParser()
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 */
	public function testRegisterParserWithInvalidTag()
	{
		TestReflection::setValue($this->_instance, 'parsers', array());

		$this->_instance->registerParser('42tag', 'JFeedParserMock');

		$this->assertNotEmpty(TestReflection::getValue($this->_instance, 'parsers'));
	}

	/**
	 * Tests JFeedFactory::_fetchFeedParser()
	 *
	 * @return  void
	 */
	public function test_fetchFeedParser()
	{
		$this->assertInstanceOf(
			'JFeedParserRss',
			TestReflection::invoke($this->_instance, '_fetchFeedParser', 'rss', new XMLReader)
		);

		$this->assertInstanceOf(
			'JFeedParserAtom',
			TestReflection::invoke($this->_instance, '_fetchFeedParser', 'feed', new XMLReader)
		);
	}

	/**
	 * Tests JFeedFactory::_fetchFeedParser()
	 *
	 * @return  void
	 *
	 * @expectedException  LogicException
	 */
	public function test_fetchFeedParserWithInvalidTag()
	{
		TestReflection::invoke($this->_instance, '_fetchFeedParser', 'foobar', new XMLReader);
	}
}
