<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\MemcacheHandler;

/**
 * Test class for Joomla\Session\Handler\MemcacheHandler.
 */
class MemcacheHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * MemcacheHandler for testing
	 *
	 * @var  MemcacheHandler
	 */
	private $handler;

	/**
	 * Memcache object for testing
	 *
	 * @var  \Memcache
	 */
	private $memcache;

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
		if (!MemcacheHandler::isSupported())
		{
			static::markTestSkipped('The MemcacheHandler is unsupported in this environment.');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->memcache = new \Memcache;

		if (@$this->memcache->connect('localhost', 11211) === false)
		{
			unset($this->memcache);
			$this->markTestSkipped('Cannot connect to Memcache.');
		}

		$this->handler = new MemcacheHandler($this->memcache, $this->options);
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::isSupported()
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertTrue(MemcacheHandler::isSupported());
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::open()
	 */
	public function testTheHandlerOpensTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->open('foo', 'bar'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::close()
	 */
	public function testTheHandlerClosesTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->close());
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::read()
	 */
	public function testTheHandlerReadsDataFromTheSessionCorrectly()
	{
		$this->handler->write('id', 'foo');

		$this->assertSame('foo', $this->handler->read('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::write()
	 */
	public function testTheHandlerWritesDataToTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->write('id', 'data'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::destroy()
	 */
	public function testTheHandlerDestroysTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->destroy('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\MemcacheHandler::gc()
	 */
	public function testTheHandlerGarbageCollectsTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->gc(60));
	}
}
