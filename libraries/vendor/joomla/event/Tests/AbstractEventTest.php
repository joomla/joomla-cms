<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Event\Tests;

use Joomla\Event\AbstractEvent;

/**
 * Tests for the AbstractEvent class.
 *
 * @since  1.0
 */
class AbstractEventTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Object under tests.
	 *
	 * @var    AbstractEvent
	 *
	 * @since  1.0
	 */
	private $instance;

	/**
	 * Test the getName method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetName()
	{
		$this->assertEquals('test', $this->instance->getName());
	}

	/**
	 * Test the getArgument method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetArgument()
	{
		$this->assertFalse($this->instance->getArgument('non-existing', false));

		$object = new \stdClass;
		$array = array(
			'foo' => 'bar',
			'test' => array(
				'foo' => 'bar',
				'test' => 'test'
			)
		);

		$arguments = array(
			'string' => 'bar',
			'object' => $object,
			'array' => $array
		);

		/** @var $event \Joomla\Event\AbstractEvent */
		$event = $this->getMockForAbstractClass('Joomla\Event\AbstractEvent', array('test', $arguments));

		$this->assertEquals('bar', $event->getArgument('string'));
		$this->assertSame($object, $event->getArgument('object'));
		$this->assertSame($array, $event->getArgument('array'));
	}

	/**
	 * Test the hasArgument method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testHasArgument()
	{
		$this->assertFalse($this->instance->hasArgument('non-existing'));

		/** @var $event \Joomla\Event\AbstractEvent */
		$event = $this->getMockForAbstractClass('Joomla\Event\AbstractEvent', array('test', array('foo' => 'bar')));

		$this->assertTrue($event->hasArgument('foo'));
	}

	/**
	 * Test the getArguments method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetArguments()
	{
		$this->assertEmpty($this->instance->getArguments());

		$object = new \stdClass;
		$array = array(
			'foo' => 'bar',
			'test' => array(
				'foo' => 'bar',
				'test' => 'test'
			)
		);

		$arguments = array(
			'string' => 'bar',
			'object' => $object,
			'array' => $array
		);

		/** @var $event \Joomla\Event\AbstractEvent */
		$event = $this->getMockForAbstractClass('Joomla\Event\AbstractEvent', array('test', $arguments));

		$this->assertSame($arguments, $event->getArguments());
	}

	/**
	 * Test the isStopped method.
	 * An immutable event shoudln't be stopped, otherwise it won't trigger.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testIsStopped()
	{
		$this->assertFalse($this->instance->isStopped());
	}

	/**
	 * Test the count method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testCount()
	{
		$this->assertCount(0, $this->instance);

		$event = $this->getMockForAbstractClass('Joomla\Event\AbstractEvent', array('test',
				array(
				'foo' => 'bar',
				'test' => array('test')
				)
			)
		);

		$this->assertCount(2, $event);
	}

	/**
	 * Test the serialize and unserialize methods.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSerializeUnserialize()
	{
		$object = new \stdClass;
		$array = array(
			'foo' => 'bar',
			'test' => array(
				'foo' => 'bar',
				'test' => 'test'
			)
		);

		$arguments = array(
			'string' => 'bar',
			'object' => $object,
			'array' => $array
		);

		$event = $this->getMockForAbstractClass('Joomla\Event\AbstractEvent', array('test', $arguments));

		$serialized = serialize($event);

		$unserialized = unserialize($serialized);

		$this->assertEquals($event, $unserialized);
	}

	/**
	 * Test the offsetExists method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testOffsetExists()
	{
		$this->assertFalse(isset($this->instance['foo']));

		$event = $this->getMockForAbstractClass('Joomla\Event\AbstractEvent', array('test', array('foo' => 'bar')));

		$this->assertTrue(isset($event['foo']));
	}

	/**
	 * Test the offsetGet method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testOffsetGet()
	{
		$this->assertNull($this->instance['foo']);

		$object = new \stdClass;
		$array = array(
			'foo' => 'bar',
			'test' => array(
				'foo' => 'bar',
				'test' => 'test'
			)
		);

		$arguments = array(
			'string' => 'bar',
			'object' => $object,
			'array' => $array
		);

		$event = $this->getMockForAbstractClass('Joomla\Event\AbstractEvent', array('test', $arguments));

		$this->assertEquals('bar', $event['string']);
		$this->assertSame($object, $event['object']);
		$this->assertSame($array, $event['array']);
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
		$this->instance = $this->getMockForAbstractClass('Joomla\Event\AbstractEvent', array('test'));
	}
}
