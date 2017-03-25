<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Event
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/JEventInspector.php';
require_once __DIR__ . '/JEventStub.php';

/**
 * Test class for JEvent.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Event
 * @since       11.3
 */
class JEventTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Test JEvent::__construct().
	 *
	 * @since 11.3
	 *
	 * @return void
	 */
	public function test__construct()
	{
		$dispatcher = new JEventDispatcher;
		$event = new JEventInspector($dispatcher);

		$this->assertThat(
			TestReflection::getValue($event, '_subject'),
			$this->equalTo($dispatcher)
		);
	}

	/**
	 * Test JEvent::update().
	 *
	 * @since 11.3
	 *
	 * @return void
	 */
	public function testUpdate()
	{
		$dispatcher = new JEventDispatcher;
		$event = new JEventInspector($dispatcher);

		$args = array('event' => 'onTestEvent');

		$this->assertThat(
			$event->update($args),
			$this->equalTo('')
		);

		$args = array('event' => 'onTestEvent', 'test1');

		$this->assertThat(
			$event->update($args),
			$this->equalTo('test1')
		);

		$args = array('event' => 'onTestEvent', 'test1', 'test2');

		$this->assertThat(
			$event->update($args),
			$this->equalTo('test1test2')
		);

		$args = array('event' => 'onTestEvent', array('test3', 'test4'));

		$this->assertThat(
			$event->update($args),
			$this->equalTo('test3test4')
		);

		$args = array('event' => 'onTestEvent2');

		$this->assertThat(
			$event->update($args),
			$this->equalTo(null)
		);
	}

	/**
	 * tests the firing of the update event with no arguments
	 *
	 * @return void
	 */
	public function testUpdateNoArgs()
	{
		// Build the mock object
		$observable = $this->getMockBuilder('Observable')->setMethods(array('attach'))->getMock();

		// We expect that the attach method of our mock object will be called because
		// when we instantiate an observer it needs something observable to attach itself to
		$observable->expects($this->once())
			->method('attach');

		// We create our object and pass our mock
		$object = new JEventStub($observable);

		// We reset the calls property.  Our stub method will populate this when it gets called
		$object->calls = array();

		// We setup the arguments to pass to update and call it.
		$args = array(
			'event' => 'myEvent'
		);

		// We call update and assert that it returns true (the value from the stub)
		$this->assertThat(
			$object->update($args),
			$this->equalTo(true)
		);

		// First, we want to assert that myEvent was called
		$this->assertThat(
			$object->calls[0]['method'],
			$this->equalTo('myEvent')
		);

		// With no arguments
		$this->assertThat(
			$object->calls[0]['args'],
			$this->equalTo(array())
		);

		// Only once
		$this->assertThat(
			count($object->calls),
			$this->equalTo(1)
		);
	}

	/**
	 * tests the firing of the update event with one argument
	 *
	 * @return void
	 */
	public function testUpdateOneArg()
	{
		// Build the mock object
		$observable = $this->getMockBuilder('Observable')->setMethods(array('attach'))->getMock();

		// We expect that the attach method of our mock object will be called because
		// when we instantiate an observer it needs something observable to attach itself to
		$observable->expects($this->once())
			->method('attach');

		// We create our object and pass our mock
		$object = new JEventStub($observable);

		// We reset the calls property.  Our stub method will populate this when it gets called
		$object->calls = array();

		// We setup the arguments to pass to update and call it.
		$args = array('myFirstArgument');
		$args['event'] = 'myEvent';

		// We call update and assert that it returns true (the value from the stub)
		$this->assertThat(
			$object->update($args),
			$this->equalTo(true)
		);

		// First, we want to assert that myEvent was called
		$this->assertThat(
			$object->calls[0]['method'],
			$this->equalTo('myEvent')
		);

		// With one arguments
		$this->assertThat(
			$object->calls[0]['args'],
			$this->equalTo(array('myFirstArgument'))
		);

		// Only once
		$this->assertThat(
			count($object->calls),
			$this->equalTo(1)
		);
	}

	/**
	 * tests the firing of the update event with multiple arguments
	 *
	 * @return void
	 */
	public function testUpdateMultipleArgs()
	{
		// Build the mock object
		$observable = $this->getMockBuilder('Observable')->setMethods(array('attach'))->getMock();

		// We expect that the attach method of our mock object will be called because
		// when we instantiate an observer it needs something observable to attach itself to
		$observable->expects($this->once())
			->method('attach');

		// We create our object and pass our mock
		$object = new JEventStub($observable);

		// We reset the calls property.  Our stub method will populate this when it gets called
		$object->calls = array();

		// We setup the arguments to pass to update and call it.
		$args = array('myFirstArgument', 5);
		$args['event'] = 'myEvent';

		// We call update and assert that it returns true (the value from the stub)
		$this->assertThat(
			$object->update($args),
			$this->equalTo(true)
		);

		// First, we want to assert that myEvent was called
		$this->assertThat(
			$object->calls[0]['method'],
			$this->equalTo('myEvent')
		);

		// With one arguments
		$this->assertThat(
			$object->calls[0]['args'],
			$this->equalTo(array('myFirstArgument', 5))
		);

		// Only once
		$this->assertThat(
			count($object->calls),
			$this->equalTo(1)
		);
	}

	/**
	 * tests the firing of an event that does not exist
	 *
	 * @return void
	 */
	public function testUpdateBadEvent()
	{
		// Build the mock object
		$observable = $this->getMockBuilder('Observable')->setMethods(array('attach'))->getMock();

		// We expect that the attach method of our mock object will be called because
		// when we instantiate an observer it needs something observable to attach itself to
		$observable->expects($this->once())
			->method('attach');

		// We create our object and pass our mock
		$object = new JEventStub($observable);

		// We reset the calls property.  Our stub method will populate this when it gets called
		$object->calls = array();

		// We setup the arguments to pass to update and call it.
		$args = array('myFirstArgument');
		$args['event'] = 'myNonExistentEvent';

		// We call update and assert that it returns null (the value from the stub)
		$this->assertThat(
			$object->update($args),
			$this->equalTo(null)
		);

		// First, we want to assert that no methods were called
		$this->assertThat(
			count($object->calls),
			$this->equalTo(0)
		);
	}
}
