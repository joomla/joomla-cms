<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\XCacheHandler;

/**
 * Test class for Joomla\Session\Handler\XCacheHandler.
 */
class XCacheHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * XCacheHandler for testing
	 *
	 * @var  XCacheHandler
	 */
	private $handler;

	/**
	 * Options to inject into the handler
	 *
	 * @var  array
	 */
	private $options = array('prefix' => 'jfwtest_');

	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		// Make sure the handler is supported in this environment
		if (!XCacheHandler::isSupported())
		{
			static::markTestSkipped('The XCacheHandler is unsupported in this environment.');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->handler = new XCacheHandler($this->options);
	}

	/**
	 * @covers  Joomla\Session\Handler\XCacheHandler::isSupported()
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertTrue(XCacheHandler::isSupported());
	}

	/**
	 * @covers  Joomla\Session\Handler\XCacheHandler::open()
	 */
	public function testTheHandlerOpensTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->open('foo', 'bar'));
	}

	/**
	 * @covers  Joomla\Session\Handler\XCacheHandler::close()
	 */
	public function testTheHandlerClosesTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->close());
	}

	/**
	 * @covers  Joomla\Session\Handler\XCacheHandler::read()
	 */
	public function testTheHandlerReadsDataFromTheSessionCorrectly()
	{
		$this->assertSame('', $this->handler->read('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\XCacheHandler::write()
	 */
	public function testTheHandlerWritesDataToTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->write('id', 'data'));
	}

	/**
	 * @covers  Joomla\Session\Handler\XCacheHandler::destroy()
	 */
	public function testTheHandlerDestroysTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->destroy('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\XCacheHandler::gc()
	 */
	public function testTheHandlerGarbageCollectsTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->gc(60));
	}
}
