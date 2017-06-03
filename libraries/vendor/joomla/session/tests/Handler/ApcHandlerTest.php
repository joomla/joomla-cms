<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests\Handler;

use Joomla\Session\Handler\ApcHandler;

/**
 * Test class for Joomla\Session\Handler\ApcHandler.
 */
class ApcHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * ApcHandler for testing
	 *
	 * @var  ApcHandler
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
		if (!ApcHandler::isSupported())
		{
			static::markTestSkipped('The ApcHandler is unsupported in this environment.');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->handler = new ApcHandler($this->options);
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcHandler::isSupported()
	 */
	public function testTheHandlerIsSupported()
	{
		$this->assertTrue(ApcHandler::isSupported());
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcHandler::open()
	 */
	public function testTheHandlerOpensTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->open('foo', 'bar'));
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcHandler::close()
	 */
	public function testTheHandlerClosesTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->close());
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcHandler::read()
	 */
	public function testTheHandlerReadsDataFromTheSessionCorrectly()
	{
		$this->assertSame('', $this->handler->read('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcHandler::write()
	 */
	public function testTheHandlerWritesDataToTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->write('id', 'data'));
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcHandler::destroy()
	 */
	public function testTheHandlerDestroysTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->destroy('id'));
	}

	/**
	 * @covers  Joomla\Session\Handler\ApcHandler::gc()
	 */
	public function testTheHandlerGarbageCollectsTheSessionCorrectly()
	{
		$this->assertTrue($this->handler->gc(60));
	}
}
