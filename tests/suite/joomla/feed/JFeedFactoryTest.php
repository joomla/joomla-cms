<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JFeedParserMock.php';
require_once __DIR__ . '/stubs/JFeedParserMockNamespace.php';

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

		$this->httpMock = $this->getMockBuilder('JHttp')
							   ->disableOriginalConstructor()
							   ->getMock();

		$this->object = new JFeedFactory($this->httpMock);
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
	 * Tests the JFeedFactory::__construct method.
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedFactory::__construct
	 */
	public function testConstructor()
	{
		$factory = new JFeedFactory($this->httpMock);

		$this->assertTrue(
			ReflectionHelper::getValue($factory, 'http') === $this->httpMock,
			'Tests http client injection.'
		);
	}

	/**
	 * Tests JFeedFactory::getFeed() with a non existent feed.
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedFactory::getFeed
	 *
	 * @expectedException  InvalidArgumentException
	 */
	public function testGetFeedInvalidArgument()
	{
		$this->object->getFeed(JPATH_BASE . '/no_file_here.feed');
	}

	/**
	 * Tests JFeedFactory::getFeed() with a bad feed.
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedFactory::getFeed
	 *
	 * @expectedException  RuntimeException
	 */
	public function testGetFeedBad()
	{
		$this->object->getFeed(JPATH_BASE . '/test.bad.feed');
	}

	/**
	 * Tests JFeedFactory::getFeed() with a bad feed.
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedFactory::getFeed
	 *
	 * @expectedException  LogicException
	 */
	public function testGetFeedNoParser()
	{
		$this->object->getFeed(JPATH_BASE . '/test.myfeed.feed');
	}

	/**
	 * Tests JFeedFactory::getFeed() with a feed parser.
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedFactory::getFeed
	 */
	public function testGetFeedMockParser()
	{
		$this->object->registerParser('myfeed', 'JFeedParserMock', true);

		JFeedParserMock::$parseReturn = 'test';

		$this->assertEquals($this->object->getFeed(JPATH_BASE . '/test.myfeed.feed'), 'test');
	}

	/**
	 * Tests JFeedFactory::getFeed()
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedFactory::getFeed
	 */
	public function testGetFeed()
	{
		$this->object->getFeed(JPATH_BASE . '/test.feed');
	}

	/**
	 * Tests JFeedFactory::registerParser()
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedFactory::registerParser
	 */
	public function testRegisterParser()
	{
		ReflectionHelper::setValue($this->object, 'parsers', array());

		$this->object->registerParser('mock', 'JFeedParserMock');

		$this->assertNotEmpty(ReflectionHelper::getValue($this->object, 'parsers'));
	}

	/**
	 * Tests JFeedFactory::registerParser()
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers             JFeedFactory::registerParser
	 * @expectedException  InvalidArgumentException
	 */
	public function testRegisterParserWithInvalidClass()
	{
		ReflectionHelper::setValue($this->object, 'parsers', array());

		$this->object->registerParser('mock', 'JFeedParserMocks');

		$this->assertNotEmpty(ReflectionHelper::getValue($this->object, 'parsers'));
	}

	/**
	 * Tests JFeedFactory::registerParser()
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers             JFeedFactory::registerParser
	 * @expectedException  InvalidArgumentException
	 */
	public function testRegisterParserWithInvalidTag()
	{
		ReflectionHelper::setValue($this->object, 'parsers', array());

		$this->object->registerParser('42tag', 'JFeedParserMock');

		$this->assertNotEmpty(ReflectionHelper::getValue($this->object, 'parsers'));
	}

	/**
	 * Tests JFeedFactory::_fetchFeedParser()
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers  JFeedFactory::_fetchFeedParser
	 */
	public function test_fetchFeedParser()
	{
		$parser = ReflectionHelper::invoke($this->object, '_fetchFeedParser', 'rss', new XMLReader);
		$this->assertInstanceOf('JFeedParserRss', $parser);

		$parser = ReflectionHelper::invoke($this->object, '_fetchFeedParser', 'feed', new XMLReader);
		$this->assertInstanceOf('JFeedParserAtom', $parser);
	}

	/**
	 * Tests JFeedFactory::_fetchFeedParser()
	 *
	 * @return void
	 *
	 * @since 12.1
	 *
	 * @covers             JFeedFactory::_fetchFeedParser
	 * @expectedException  LogicException
	 */
	public function test_fetchFeedParserWithInvalidTag()
	{
		$parser = ReflectionHelper::invoke($this->object, '_fetchFeedParser', 'foobar', new XMLReader);
	}
}
