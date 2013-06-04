<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('JFeedParserMock', __DIR__ . '/stubs/JFeedParserMock.php');
JLoader::register('JFeedParserMockNamespace', __DIR__ . '/stubs/JFeedParserMockNamespace.php');

/**
 * Test class for JFeedFactory.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.3
 */
class JFeedFactoryTest extends TestCase
{
	/**
	 * @var    JFeedFactory
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * Tests JFeedFactory::getFeed() with a bad feed.
	 *
	 * @return  void
	 *
	 * @covers             JFeedFactory::getFeed
	 * @expectedException  RuntimeException
	 * @since              12.3
	 */
	public function testGetFeedBad()
	{
		$this->markTestSkipped('Unexpected failure testing in CMS environment');
		$this->_instance->getFeed(JPATH_TESTS . '/tmp/test.bad.feed');
	}

	/**
	 * Tests JFeedFactory::getFeed() with a bad feed.
	 *
	 * @return  void
	 *
	 * @covers             JFeedFactory::getFeed
	 * @expectedException  LogicException
	 * @since              12.3
	 */
	public function testGetFeedNoParser()
	{
		$this->_instance->getFeed(JPATH_TESTS . '/tmp/test.myfeed.feed');
	}

	/**
	 * Tests JFeedFactory::getFeed() with a feed parser.
	 *
	 * @return  void
	 *
	 * @covers  JFeedFactory::getFeed
	 * @since   12.3
	 */
	public function testGetFeedMockParser()
	{
		$this->_instance->registerParser('myfeed', 'JFeedParserMock', true);

		JFeedParserMock::$parseReturn = 'test';

		$this->assertEquals($this->_instance->getFeed(JPATH_TESTS . '/tmp/test.myfeed.feed'), 'test');
	}

	/**
	 * Tests JFeedFactory::getFeed()
	 *
	 * @return  void
	 *
	 * @covers  JFeedFactory::getFeed
	 * @since   12.3
	 */
	public function testGetFeed()
	{
		$this->_instance->getFeed(JPATH_TESTS . '/tmp/test.feed');
	}

	/**
	 * Tests JFeedFactory::registerParser()
	 *
	 * @return  void
	 *
	 * @covers  JFeedFactory::registerParser
	 * @since   12.3
	 */
	public function testRegisterParser()
	{
		TestReflection::setValue($this->_instance, 'parsers', array());

		$this->_instance->registerParser('mock', 'JFeedParserMock');

		$this->assertNotEmpty(TestReflection::getValue($this->_instance, 'parsers'));
	}

	/**
	 * Tests JFeedFactory::registerParser()
	 *
	 * @return  void
	 *
	 * @covers             JFeedFactory::registerParser
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testRegisterParserWithInvalidClass()
	{
		TestReflection::setValue($this->_instance, 'parsers', array());

		$this->_instance->registerParser('mock', 'JFeedParserMocks');

		$this->assertNotEmpty(TestReflection::getValue($this->_instance, 'parsers'));
	}

	/**
	 * Tests JFeedFactory::registerParser()
	 *
	 * @return  void
	 *
	 * @covers             JFeedFactory::registerParser
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
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
	 *
	 * @covers  JFeedFactory::_fetchFeedParser
	 * @since   12.3
	 */
	public function test_fetchFeedParser()
	{
		$parser = TestReflection::invoke($this->_instance, '_fetchFeedParser', 'rss', new XMLReader);
		$this->assertInstanceOf('JFeedParserRss', $parser);

		$parser = TestReflection::invoke($this->_instance, '_fetchFeedParser', 'feed', new XMLReader);
		$this->assertInstanceOf('JFeedParserAtom', $parser);
	}

	/**
	 * Tests JFeedFactory::_fetchFeedParser()
	 *
	 * @return  void
	 *
	 * @covers             JFeedFactory::_fetchFeedParser
	 * @expectedException  LogicException
	 * @since              12.3
	 */
	public function test_fetchFeedParserWithInvalidTag()
	{
		$parser = TestReflection::invoke($this->_instance, '_fetchFeedParser', 'foobar', new XMLReader);
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

		$this->_instance = new JFeedFactory;
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
