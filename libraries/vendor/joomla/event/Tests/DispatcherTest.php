<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Event\Tests;

use Joomla\Event\Dispatcher;
use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Joomla\Event\EventImmutable;
use Joomla\Event\Priority;
use Joomla\Event\Tests\Stubs\EmptyListener;
use Joomla\Event\Tests\Stubs\FirstListener;
use Joomla\Event\Tests\Stubs\SecondListener;
use Joomla\Event\Tests\Stubs\SomethingListener;
use Joomla\Event\Tests\Stubs\ThirdListener;

/**
 * Tests for the Dispatcher class.
 *
 * @since  1.0
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Object under tests.
	 *
	 * @var    Dispatcher
	 *
	 * @since  1.0
	 */
	private $instance;

	/**
	 * Test the setEvent method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::setEvent
	 * @since   1.0
	 */
	public function testSetEvent()
	{
		$event = new Event('onTest');
		$this->instance->setEvent($event);
		$this->assertTrue($this->instance->hasEvent('onTest'));
		$this->assertSame($event, $this->instance->getEvent('onTest'));

		$immutableEvent = new EventImmutable('onAfterSomething');
		$this->instance->setEvent($immutableEvent);
		$this->assertTrue($this->instance->hasEvent('onAfterSomething'));
		$this->assertSame($immutableEvent, $this->instance->getEvent('onAfterSomething'));

		// Setting an existing event will replace the old one.
		$eventCopy = new Event('onTest');
		$this->instance->setEvent($eventCopy);
		$this->assertTrue($this->instance->hasEvent('onTest'));
		$this->assertSame($eventCopy, $this->instance->getEvent('onTest'));
	}

	/**
	 * Test the setEvent method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::setListenerFilter
	 * @since   1.0
	 * @deprecated
	 */
	public function testSetListenerFilter()
	{
		$listener1 = new FirstListener;
		$this->instance->addListener($listener1);
		$this->assertTrue($this->instance->hasListener($listener1, new Event('fooBar')));
		$this->assertTrue($this->instance->hasListener($listener1, new Event('onSomething')));

		$this->instance->setListenerFilter('^on');

		$listener2 = new SecondListener;
		$this->instance->addListener($listener2);
		$this->assertFalse($this->instance->hasListener($listener2, new Event('fooBar')), 'Tests that `fooBar` was filtered out.');
		$this->assertTrue($this->instance->hasListener($listener2, new Event('onSomething')));
	}

	/**
	 * Test the addEvent method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::addEvent
	 * @since   1.0
	 */
	public function testAddEvent()
	{
		$event = new Event('onTest');
		$this->instance->addEvent($event);
		$this->assertTrue($this->instance->hasEvent('onTest'));
		$this->assertSame($event, $this->instance->getEvent('onTest'));

		$immutableEvent = new EventImmutable('onAfterSomething');
		$this->instance->addEvent($immutableEvent);
		$this->assertTrue($this->instance->hasEvent('onAfterSomething'));
		$this->assertSame($immutableEvent, $this->instance->getEvent('onAfterSomething'));

		// Adding an existing event will have no effect.
		$eventCopy = new Event('onTest');
		$this->instance->addEvent($eventCopy);
		$this->assertTrue($this->instance->hasEvent('onTest'));
		$this->assertSame($event, $this->instance->getEvent('onTest'));
	}

	/**
	 * Test the hasEvent method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::hasEvent
	 * @since   1.0
	 */
	public function testHasEvent()
	{
		$this->assertFalse($this->instance->hasEvent('onTest'));

		$event = new Event('onTest');
		$this->instance->addEvent($event);
		$this->assertTrue($this->instance->hasEvent($event));
	}

	/**
	 * Test the getEvent method when the event doesn't exist.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::getEvent
	 * @since   1.0
	 */
	public function testGetEventNonExisting()
	{
		$this->assertNull($this->instance->getEvent('non-existing'));
		$this->assertFalse($this->instance->getEvent('non-existing', false));
	}

	/**
	 * Test the removeEvent method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::removeEvent
	 * @since   1.0
	 */
	public function testRemoveEvent()
	{
		// No exception.
		$this->instance->removeEvent('non-existing');

		$event = new Event('onTest');
		$this->instance->addEvent($event);

		// Remove by passing the instance.
		$this->instance->removeEvent($event);
		$this->assertFalse($this->instance->hasEvent('onTest'));

		$this->instance->addEvent($event);

		// Remove by name.
		$this->instance->removeEvent('onTest');
		$this->assertFalse($this->instance->hasEvent('onTest'));
	}

	/**
	 * Test the getEvents method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::getEvents
	 * @since   1.0
	 */
	public function testGetEvents()
	{
		$this->assertEmpty($this->instance->getEvents());

		$event1 = new Event('onBeforeTest');
		$event2 = new Event('onTest');
		$event3 = new Event('onAfterTest');

		$this->instance->addEvent($event1)
			->addEvent($event2)
			->addEvent($event3);

		$expected = array(
			'onBeforeTest' => $event1,
			'onTest' => $event2,
			'onAfterTest' => $event3
		);

		$this->assertSame($expected, $this->instance->getEvents());
	}

	/**
	 * Test the clearEvents method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::clearEvents
	 * @since   1.0
	 */
	public function testClearEvents()
	{
		$event1 = new Event('onBeforeTest');
		$event2 = new Event('onTest');
		$event3 = new Event('onAfterTest');

		$this->instance->addEvent($event1)
			->addEvent($event2)
			->addEvent($event3);

		$this->instance->clearEvents();

		$this->assertFalse($this->instance->hasEvent('onBeforeTest'));
		$this->assertFalse($this->instance->hasEvent('onTest'));
		$this->assertFalse($this->instance->hasEvent('onAfterTest'));
		$this->assertEmpty($this->instance->getEvents());
	}

	/**
	 * Test the countEvents method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::countEvents
	 * @since   1.0
	 */
	public function testCountEvents()
	{
		$this->assertEquals(0, $this->instance->countEvents());

		$event1 = new Event('onBeforeTest');
		$event2 = new Event('onTest');
		$event3 = new Event('onAfterTest');

		$this->instance->addEvent($event1)
			->addEvent($event2)
			->addEvent($event3);

		$this->assertEquals(3, $this->instance->countEvents());
	}

	/**
	 * Test the addListener method with an empty listener (no methods).
	 * It shouldn't be registered to any event.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::addListener
	 * @since   1.0
	 */
	public function testAddListenerEmpty()
	{
		$listener = new EmptyListener;
		$this->instance->addListener($listener);

		$this->assertFalse($this->instance->hasListener($listener));

		$this->instance->addListener($listener, array('onSomething'));
		$this->assertFalse($this->instance->hasListener($listener, 'onSomething'));
	}

	/**
	 * Test the addListener method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::addListener
	 * @since   1.0
	 */
	public function testAddListener()
	{
		// Add 3 listeners listening to the same events.
		$listener1 = new SomethingListener;
		$listener2 = new SomethingListener;
		$listener3 = new SomethingListener;

		$this->instance->addListener($listener1)
			->addListener($listener2)
			->addListener($listener3);

		$this->assertTrue($this->instance->hasListener($listener1));
		$this->assertTrue($this->instance->hasListener($listener1, 'onBeforeSomething'));
		$this->assertTrue($this->instance->hasListener($listener1, 'onSomething'));
		$this->assertTrue($this->instance->hasListener($listener1, 'onAfterSomething'));

		$this->assertTrue($this->instance->hasListener($listener2));
		$this->assertTrue($this->instance->hasListener($listener2, 'onBeforeSomething'));
		$this->assertTrue($this->instance->hasListener($listener2, 'onSomething'));
		$this->assertTrue($this->instance->hasListener($listener2, 'onAfterSomething'));

		$this->assertTrue($this->instance->hasListener($listener3));
		$this->assertTrue($this->instance->hasListener($listener3, 'onBeforeSomething'));
		$this->assertTrue($this->instance->hasListener($listener3, 'onSomething'));
		$this->assertTrue($this->instance->hasListener($listener3, 'onAfterSomething'));

		$this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority($listener1, 'onBeforeSomething'));
		$this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority($listener1, 'onSomething'));
		$this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority($listener1, 'onAfterSomething'));

		$this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority($listener1, 'onBeforeSomething'));
		$this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority($listener1, 'onSomething'));
		$this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority($listener1, 'onAfterSomething'));

		$this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority($listener3, 'onBeforeSomething'));
		$this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority($listener3, 'onSomething'));
		$this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority($listener3, 'onAfterSomething'));
	}

	/**
	 * Test the addListener method by specifying the events and priorities.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::addListener
	 * @since   1.0
	 */
	public function testAddListenerSpecifiedPriorities()
	{
		$listener = new SomethingListener;

		$this->instance->addListener(
			$listener,
			array(
				'onBeforeSomething' => Priority::MIN,
				'onSomething' => Priority::ABOVE_NORMAL,
				'onAfterSomething' => Priority::HIGH
			)
		);

		$this->assertTrue($this->instance->hasListener($listener, 'onBeforeSomething'));
		$this->assertTrue($this->instance->hasListener($listener, 'onSomething'));
		$this->assertTrue($this->instance->hasListener($listener, 'onAfterSomething'));

		$this->assertEquals(Priority::MIN, $this->instance->getListenerPriority($listener, 'onBeforeSomething'));
		$this->assertEquals(Priority::ABOVE_NORMAL, $this->instance->getListenerPriority($listener, 'onSomething'));
		$this->assertEquals(Priority::HIGH, $this->instance->getListenerPriority($listener, 'onAfterSomething'));
	}

	/**
	 * Test the addListener method by specifying less events than its methods.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::addListener
	 * @since   1.0
	 */
	public function testAddListenerLessEvents()
	{
		$listener = new SomethingListener;

		$this->instance->addListener(
			$listener,
			array(
				'onBeforeSomething' => Priority::NORMAL,
				'onAfterSomething' => Priority::HIGH
			)
		);

		$this->assertFalse($this->instance->hasListener($listener, 'onSomething'));
		$this->assertTrue($this->instance->hasListener($listener, 'onBeforeSomething'));
		$this->assertTrue($this->instance->hasListener($listener, 'onAfterSomething'));
	}

	/**
	 * Test the addListener method with a closure listener.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::addListener
	 * @since   1.0
	 */
	public function testAddClosureListener()
	{
		$listener = function (EventInterface $event) {

		};

		$this->instance->addListener(
			$listener,
			array(
				'onSomething' => Priority::HIGH,
				'onAfterSomething' => Priority::NORMAL
			)
		);

		$this->assertTrue($this->instance->hasListener($listener));
		$this->assertTrue($this->instance->hasListener($listener, 'onSomething'));
		$this->assertTrue($this->instance->hasListener($listener, 'onAfterSomething'));

		$this->assertEquals(Priority::HIGH, $this->instance->getListenerPriority($listener, 'onSomething'));
		$this->assertEquals(Priority::NORMAL, $this->instance->getListenerPriority($listener, 'onAfterSomething'));
	}

	/**
	 * Test the addListener method with a closure listener without specified event.
	 *
	 * @return  void
	 *
	 * @covers             Joomla\Event\Dispatcher::addListener
	 * @expectedException  \InvalidArgumentException
	 * @since              1.0
	 */
	public function testAddClosureListenerNoEventsException()
	{
		$this->instance->addListener(
			function (EventInterface $event) {

			}
		);
	}

	/**
	 * Test the addListener method with an invalid listener.
	 *
	 * @expectedException  \InvalidArgumentException
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::addListener
	 * @since   1.0
	 */
	public function testAddListenerInvalidListenerException()
	{
		$this->instance->addListener('foo');
	}

	/**
	 * Test the getListenerPriority method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::getListenerPriority
	 * @since   1.0
	 */
	public function testGetListenerPriority()
	{
		$this->assertNull($this->instance->getListenerPriority(new \stdClass, 'onTest'));

		$listener = new SomethingListener;
		$this->instance->addListener($listener);

		$this->assertEquals(
			Priority::NORMAL,
			$this->instance->getListenerPriority(
				$listener,
				new Event('onSomething')
			)
		);
	}

	/**
	 * Test the getListeners method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::getListeners
	 * @since   1.0
	 */
	public function testGetListeners()
	{
		$this->assertEmpty($this->instance->getListeners('onSomething'));

		$listener1 = new SomethingListener;
		$listener2 = new SomethingListener;
		$listener3 = new SomethingListener;

		$this->instance->addListener($listener1)
			->addListener($listener2)
			->addListener($listener3);

		$onBeforeSomethingListeners = $this->instance->getListeners('onBeforeSomething');

		$this->assertSame($listener1, $onBeforeSomethingListeners[0]);
		$this->assertSame($listener2, $onBeforeSomethingListeners[1]);
		$this->assertSame($listener3, $onBeforeSomethingListeners[2]);

		$onSomethingListeners = $this->instance->getListeners(new Event('onSomething'));

		$this->assertSame($listener1, $onSomethingListeners[0]);
		$this->assertSame($listener2, $onSomethingListeners[1]);
		$this->assertSame($listener3, $onSomethingListeners[2]);

		$onAfterSomethingListeners = $this->instance->getListeners('onAfterSomething');

		$this->assertSame($listener1, $onAfterSomethingListeners[0]);
		$this->assertSame($listener2, $onAfterSomethingListeners[1]);
		$this->assertSame($listener3, $onAfterSomethingListeners[2]);
	}

	/**
	 * Test the hasListener method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::hasListener
	 * @since   1.0
	 */
	public function testHasListener()
	{
		$this->assertFalse($this->instance->hasListener(new \stdClass, 'onTest'));

		$listener = new SomethingListener;
		$this->instance->addListener($listener);
		$this->assertTrue($this->instance->hasListener($listener, new Event('onSomething')));
	}

	/**
	 * Test the removeListener method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::removeListener
	 * @since   1.0
	 */
	public function testRemoveListeners()
	{
		$listener = new SomethingListener;
		$this->instance->addListener($listener);

		// Remove the listener from all events.
		$this->instance->removeListener($listener);

		$this->assertFalse($this->instance->hasListener($listener, 'onBeforeSomething'));
		$this->assertFalse($this->instance->hasListener($listener, 'onSomething'));
		$this->assertFalse($this->instance->hasListener($listener, 'onAfterSomething'));

		$this->instance->addListener($listener);

		// Remove the listener from a specific event.
		$this->instance->removeListener($listener, 'onBeforeSomething');

		$this->assertFalse($this->instance->hasListener($listener, 'onBeforeSomething'));
		$this->assertTrue($this->instance->hasListener($listener, 'onSomething'));
		$this->assertTrue($this->instance->hasListener($listener, 'onAfterSomething'));

		// Remove the listener from a specific event by passing an event object.
		$this->instance->removeListener($listener, new Event('onSomething'));

		$this->assertFalse($this->instance->hasListener($listener, 'onSomething'));
		$this->assertTrue($this->instance->hasListener($listener, 'onAfterSomething'));
	}

	/**
	 * Test the clearListeners method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::clearListeners
	 * @since   1.0
	 */
	public function testClearListeners()
	{
		$listener1 = new SomethingListener;
		$listener2 = new SomethingListener;
		$listener3 = new SomethingListener;

		$this->instance->addListener($listener1)
			->addListener($listener2)
			->addListener($listener3);

		// Test without specified event.
		$this->instance->clearListeners();

		$this->assertFalse($this->instance->hasListener($listener1));
		$this->assertFalse($this->instance->hasListener($listener2));
		$this->assertFalse($this->instance->hasListener($listener3));

		// Test with an event specified.
		$this->instance->addListener($listener1)
			->addListener($listener2)
			->addListener($listener3);

		$this->instance->clearListeners('onSomething');

		$this->assertTrue($this->instance->hasListener($listener1));
		$this->assertTrue($this->instance->hasListener($listener2));
		$this->assertTrue($this->instance->hasListener($listener3));

		$this->assertFalse($this->instance->hasListener($listener1, 'onSomething'));
		$this->assertFalse($this->instance->hasListener($listener2, 'onSomething'));
		$this->assertFalse($this->instance->hasListener($listener3, 'onSomething'));

		// Test with a specified event object.
		$this->instance->clearListeners(new Event('onAfterSomething'));

		$this->assertTrue($this->instance->hasListener($listener1));
		$this->assertTrue($this->instance->hasListener($listener2));
		$this->assertTrue($this->instance->hasListener($listener3));

		$this->assertFalse($this->instance->hasListener($listener1, 'onAfterSomething'));
		$this->assertFalse($this->instance->hasListener($listener2, 'onAfterSomething'));
		$this->assertFalse($this->instance->hasListener($listener3, 'onAfterSomething'));
	}

	/**
	 * Test the clearListeners method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::clearListeners
	 * @since   1.0
	 */
	public function testCountListeners()
	{
		$this->assertEquals(0, $this->instance->countListeners('onTest'));

		$listener1 = new SomethingListener;
		$listener2 = new SomethingListener;
		$listener3 = new SomethingListener;

		$this->instance->addListener($listener1)
			->addListener($listener2)
			->addListener($listener3);

		$this->assertEquals(3, $this->instance->countListeners('onSomething'));
		$this->assertEquals(3, $this->instance->countListeners(new Event('onSomething')));
	}

	/**
	 * Test the triggerEvent method with no listeners listening to the event.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::triggerEvent
	 * @since   1.0
	 */
	public function testTriggerEventNoListeners()
	{
		$this->assertInstanceOf('Joomla\Event\Event', $this->instance->triggerEvent('onTest'));

		$event = new Event('onTest');
		$this->assertSame($event, $this->instance->triggerEvent($event));
	}

	/**
	 * Test the trigger event method with listeners having the same priority.
	 * We expect the listener to be called in the order they were added.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::triggerEvent
	 * @since   1.0
	 */
	public function testTriggerEventSamePriority()
	{
		$first = new FirstListener;
		$second = new SecondListener;
		$third = new ThirdListener;

		$fourth = function (Event $event) {
			$listeners = $event->getArgument('listeners');
			$listeners[] = 'fourth';
			$event->setArgument('listeners', $listeners);
		};

		$fifth = function (Event $event) {
			$listeners = $event->getArgument('listeners');
			$listeners[] = 'fifth';
			$event->setArgument('listeners', $listeners);
		};

		$this->instance->addListener($first)
			->addListener($second)
			->addListener($third)
			->addListener($fourth, array('onSomething' => Priority::NORMAL))
			->addListener($fifth, array('onSomething' => Priority::NORMAL));

		// Inspect the event arguments to know the order of the listeners.
		/** @var $event Event */
		$event = $this->instance->triggerEvent('onSomething');

		$listeners = $event->getArgument('listeners');

		$this->assertEquals(
			$listeners,
			array('first', 'second', 'third', 'fourth', 'fifth')
		);
	}

	/**
	 * Test the trigger event method with listeners having different priorities.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::triggerEvent
	 * @since   1.0
	 */
	public function testTriggerEventDifferentPriorities()
	{
		$first = new FirstListener;
		$second = new SecondListener;
		$third = new ThirdListener;

		$fourth = function (Event $event) {
			$listeners = $event->getArgument('listeners');
			$listeners[] = 'fourth';
			$event->setArgument('listeners', $listeners);
		};

		$fifth = function (Event $event) {
			$listeners = $event->getArgument('listeners');
			$listeners[] = 'fifth';
			$event->setArgument('listeners', $listeners);
		};

		$this->instance->addListener($fourth, array('onSomething' => Priority::BELOW_NORMAL));
		$this->instance->addListener($fifth, array('onSomething' => Priority::BELOW_NORMAL));
		$this->instance->addListener($first, array('onSomething' => Priority::HIGH));
		$this->instance->addListener($second, array('onSomething' => Priority::HIGH));
		$this->instance->addListener($third, array('onSomething' => Priority::ABOVE_NORMAL));

		// Inspect the event arguments to know the order of the listeners.
		/** @var $event Event */
		$event = $this->instance->triggerEvent('onSomething');

		$listeners = $event->getArgument('listeners');

		$this->assertEquals(
			$listeners,
			array('first', 'second', 'third', 'fourth', 'fifth')
		);
	}

	/**
	 * Test the trigger event method with a listener stopping the event propagation.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::triggerEvent
	 * @since   1.0
	 */
	public function testTriggerEventStopped()
	{
		$first = new FirstListener;
		$second = new SecondListener;
		$third = new ThirdListener;

		$stopper = function (Event $event) {
			$event->stop();
		};

		$this->instance->addListener($first)
			->addListener($second)
			->addListener($stopper, array('onSomething' => Priority::NORMAL))
			->addListener($third);

		/** @var $event Event */
		$event = $this->instance->triggerEvent('onSomething');

		$listeners = $event->getArgument('listeners');

		// The third listener was not called because the stopper stopped the event.
		$this->assertEquals(
			$listeners,
			array('first', 'second')
		);
	}

	/**
	 * Test the triggerEvent method with a previously registered event.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Event\Dispatcher::triggerEvent
	 * @since   1.0
	 */
	public function testTriggerEventRegistered()
	{
		$event = new Event('onSomething');

		$mockedListener = $this->getMock('Joomla\Event\Test\Stubs\SomethingListener', array('onSomething'));
		$mockedListener->expects($this->once())
			->method('onSomething')
			->with($event);

		$this->instance->addEvent($event);
		$this->instance->addListener($mockedListener);

		$this->instance->triggerEvent('onSomething');
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
		$this->instance = new Dispatcher;
	}
}
