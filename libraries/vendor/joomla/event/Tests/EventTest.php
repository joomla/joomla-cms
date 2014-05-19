<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Event\Tests;

use Joomla\Event\Event;

/**
 * Tests for the Event class.
 *
 * @since  1.0
 */
class EventTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Object under tests.
	 *
	 * @var    Event
	 *
	 * @since  1.0
	 */
	private $instance;

	/**
	 * Test the addArgument method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testAddArgument()
	{
		$object = new \stdClass;

		$array = array(
			'test' => array(
				'foo' => 'bar',
				'test' => 'test'
			)
		);

		$this->instance->addArgument('object', $object);
		$this->assertTrue($this->instance->hasArgument('object'));
		$this->assertSame($object, $this->instance->getArgument('object'));

		$this->instance->addArgument('array', $array);
		$this->assertTrue($this->instance->hasArgument('array'));
		$this->assertSame($array, $this->instance->getArgument('array'));
	}

	/**
	 * Test the addArgument method when the argument already exists, it should be untouched.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testAddArgumentExisting()
	{
		$this->instance->addArgument('foo', 'bar');
		$this->instance->addArgument('foo', 'foo');

		$this->assertTrue($this->instance->hasArgument('foo'));
		$this->assertEquals('bar', $this->instance->getArgument('foo'));
	}

	/**
	 * Test the setArgument method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSetArgument()
	{
		$object = new \stdClass;

		$array = array(
			'test' => array(
				'foo' => 'bar',
				'test' => 'test'
			)
		);

		$this->instance->setArgument('object', $object);
		$this->assertTrue($this->instance->hasArgument('object'));
		$this->assertSame($object, $this->instance->getArgument('object'));

		$this->instance->setArgument('array', $array);
		$this->assertTrue($this->instance->hasArgument('array'));
		$this->assertSame($array, $this->instance->getArgument('array'));
	}

	/**
	 * Test the setArgument method when the argument already exists, it should be overriden.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSetArgumentExisting()
	{
		$this->instance->setArgument('foo', 'bar');
		$this->instance->setArgument('foo', 'foo');

		$this->assertTrue($this->instance->hasArgument('foo'));
		$this->assertEquals('foo', $this->instance->getArgument('foo'));
	}

	/**
	 * Test the removeArgument method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testRemoveArgument()
	{
		$this->assertNull($this->instance->removeArgument('non-existing'));

		$this->instance->addArgument('foo', 'bar');

		$old = $this->instance->removeArgument('foo');

		$this->assertEquals('bar', $old);
		$this->assertFalse($this->instance->hasArgument('foo'));
	}

	/**
	 * Test the clearArguments method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testClearArguments()
	{
		$this->assertEmpty($this->instance->clearArguments());

		$arguments = array(
			'test' => array(
				'foo' => 'bar',
				'test' => 'test'
			),
			'foo' => new \stdClass
		);

		$event = new Event('test', $arguments);

		$oldArguments = $event->clearArguments();

		$this->assertSame($oldArguments, $arguments);
		$this->assertFalse($event->hasArgument('test'));
		$this->assertFalse($event->hasArgument('foo'));
	}

	/**
	 * Test the stop method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testStop()
	{
		$this->assertFalse($this->instance->isStopped());

		$this->instance->stop();

		$this->assertTrue($this->instance->isStopped());
	}

	/**
	 * Test the offsetSet method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testOffsetSet()
	{
		$this->instance['foo'] = 'bar';

		$this->assertTrue($this->instance->hasArgument('foo'));
		$this->assertEquals('bar', $this->instance->getArgument('foo'));

		$argument = array(
			'test' => array(
				'foo' => 'bar',
				'test' => 'test'
			),
			'foo' => new \stdClass
		);

		$this->instance['foo'] = $argument;
		$this->assertTrue($this->instance->hasArgument('foo'));
		$this->assertSame($argument, $this->instance->getArgument('foo'));
	}

	/**
	 * Test the offsetSet method exception.
	 *
	 * @expectedException  \InvalidArgumentException
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testOffsetSetException()
	{
		$this->instance[] = 'bar';
	}

	/**
	 * Test the offsetUnset method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testOffsetUnset()
	{
		// No exception.
		unset($this->instance['foo']);

		$this->instance['foo'] = 'bar';
		unset($this->instance['foo']);

		$this->assertFalse($this->instance->hasArgument('foo'));
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		$this->instance = new Event('test');
	}
}
