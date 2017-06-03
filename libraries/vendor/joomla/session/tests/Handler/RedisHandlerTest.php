<?php
/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\RedisHandler;

/**
 * Test class for Joomla\Session\Handler\RedisHandler.
 */
class RedisHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * RedisHandler for testing
	 *
	 * @var  RedisHandler
	 */
	private $handler;

	/**
	 * Mock Redis object for testing
	 *
	 * @var  \PHPUnit_Framework_MockObject_MockObject|\Redis
	 */
	private $redis;

	/**
	 * Options to inject into the handler
	 *
	 * @var  array
	 */
	private $options = ['prefix' => 'jfwtest_', 'ttl' => 1000];

	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		// Make sure the handler is supported in this environment
		if (!RedisHandler::isSupported())
		{
			static::markTestSkipped('The RedisHandler is unsupported in this environment.');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->redis   = $this->getMock('Redis');
		$this->handler = new RedisHandler($this->redis, $this->options);
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler::isSupported()
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertSame(
			(extension_loaded('redis') && class_exists('Redis')),
			RedisHandler::isSupported()
		);
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler::open()
	 */
	public function testTheHandlerOpensTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->open('foo', 'bar'));
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler::close()
	 */
	public function testTheHandlerClosesTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->close());
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler::read()
	 */
	public function testTheHandlerReadsDataFromTheSessionCorrectly()
	{
		$this->redis->expects($this->once())
			->method('get')
			->with($this->options['prefix'] . 'id')
			->willReturn('foo');

		$this->assertSame('foo', $this->handler->read('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler::write()
	 */
	public function testTheHandlerWritesDataToTheSessionCorrectlyWithATimeToLive()
	{
		$this->redis->expects($this->once())
			->method('setex')
			->with($this->options['prefix'] . 'id', $this->options['ttl'], 'data')
			->willReturn(true);

		$this->assertTrue($this->handler->write('id', 'data'));
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler::write()
	 */
	public function testTheHandlerWritesDataToTheSessionCorrectlyWithoutATimeToLive()
	{
		$handler = new RedisHandler($this->redis, ['prefix' => 'jfwtest_', 'ttl' => 0]);

		$this->redis->expects($this->once())
			->method('set')
			->with($this->options['prefix'] . 'id', 'data')
			->willReturn(true);

		$this->assertTrue($handler->write('id', 'data'));
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler::destroy()
	 */
	public function testTheHandlerDestroysTheSessionCorrectly()
	{
		$this->redis->expects($this->once())
			->method('del')
			->with($this->options['prefix'] . 'id')
			->willReturn(true);

		$this->assertTrue($this->handler->destroy('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\RedisHandler::gc()
	 */
	public function testTheHandlerGarbageCollectsTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->gc(60));
	}
}
