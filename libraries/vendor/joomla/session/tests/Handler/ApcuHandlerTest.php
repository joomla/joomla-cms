<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\ApcuHandler;

/**
 * Test class for Joomla\Session\Handler\ApcuHandler.
 */
class ApcuHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * ApcuHandler for testing
	 *
	 * @var  ApcuHandler
	 */
	private $handler;

	/**
	 * Options to inject into the handler
	 *
	 * @var  array
	 */
	private $options = ['prefix' => 'jfwtest_'];

	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		// Make sure the handler is supported in this environment
		if (!ApcuHandler::isSupported())
		{
			static::markTestSkipped('The ApcuHandler is unsupported in this environment.');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->handler = new ApcuHandler($this->options);
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcuHandler::isSupported()
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertTrue(ApcuHandler::isSupported());
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcuHandler::open()
	 */
	public function testTheHandlerOpensTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->open('foo', 'bar'));
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcuHandler::close()
	 */
	public function testTheHandlerClosesTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->close());
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcuHandler::read()
	 */
	public function testTheHandlerReadsDataFromTheSessionCorrectly()
	{
		$this->assertSame('', $this->handler->read('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcuHandler::write()
	 */
	public function testTheHandlerWritesDataToTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->write('id', 'data'));
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcuHandler::destroy()
	 */
	public function testTheHandlerDestroysTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->destroy('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcuHandler::gc()
	 */
	public function testTheHandlerGarbageCollectsTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->gc(60));
	}
}
