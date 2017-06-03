<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\MemcachedHandler;

/**
 * Test class for Joomla\Session\Handler\MemcachedHandler.
 */
class MemcachedHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * MemcachedHandler for testing
	 *
	 * @var  MemcachedHandler
	 */
	private $handler;

	/**
	 * Memcached object for testing
	 *
	 * @var  \Memcached
	 */
	private $memcached;

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
		if (!MemcachedHandler::isSupported())
		{
			static::markTestSkipped('The MemcachedHandler is unsupported in this environment.');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->memcached = new \Memcached;
		$this->memcached->setOption(\Memcached::OPT_COMPRESSION, false);
		$this->memcached->addServer('127.0.0.1', 11211);

		if (@fsockopen('127.0.0.1', 11211) === false)
		{
			unset($this->memcached);
			$this->markTestSkipped('Cannot connect to Memcached.');
		}

		$this->handler = new MemcachedHandler($this->memcached, $this->options);
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::isSupported()
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertTrue(MemcachedHandler::isSupported());
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::open()
	 */
	public function testTheHandlerOpensTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->open('foo', 'bar'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::close()
	 */
	public function testTheHandlerClosesTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->close());
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::read()
	 */
	public function testTheHandlerReadsDataFromTheSessionCorrectly()
	{
		$this->handler->write('id', 'foo');

		$this->assertSame('foo', $this->handler->read('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::write()
	 */
	public function testTheHandlerWritesDataToTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->write('id', 'data'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::destroy()
	 */
	public function testTheHandlerDestroysTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->destroy('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcachedHandler::gc()
	 */
	public function testTheHandlerGarbageCollectsTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->gc(60));
	}
}
