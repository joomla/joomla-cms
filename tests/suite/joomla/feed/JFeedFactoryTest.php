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
		$mockHttp = $this->getMock('JHttp', array('test'), array(), '', false);
		$mockHttp->expects($this->any())
			->method('test')
			->will($this->returnValue('ok'));

		$factory = new JFeedFactory($mockHttp);

		$this->assertThat(
			ReflectionHelper::getValue($factory, 'http')->test(),
			$this->equalTo('ok'),
			'Tests http client injection.'
		);
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
		$this->markTestIncomplete("getFeed test not implemented");
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

		$parser = ReflectionHelper::invoke($this->object, '_fetchFeedParser', 'atom', new XMLReader);
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
