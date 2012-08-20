<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Event
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/JEventInspector.php';
require_once __DIR__ . '/JEventDispatcherInspector.php';
require_once __DIR__ . '/JEventStub.php';

/**
 * Test class for JEvent.
 *
 * @package		Joomla.UnitTest
 * @subpackage  Event
 * @since       11.3
 */
class JEventTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test JEvent::__construct().
     *
     * @since 11.3
     */
	public function test__construct()
	{
		$dispatcher = new JEventDispatcherInspector();
		$event = new JEventInspector($dispatcher);

		$this->assertThat(
			$event->_subject,
			$this->equalTo($dispatcher)
		);
	}

    /**
     * Test JEvent::update().
     *
     * @since 11.3
     */
    public function testUpdate()
    {
		$dispatcher = new JEventDispatcherInspector();
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
	 */
	public function testUpdateNoArgs()
	{
		// get a mock for the
		$observable = $this->getMock('Observable', array('attach'));

		// we expect that the attach method of our mock object will be called
		// because when we instantiate an observer it needs something observable
		// to attach itself to
		$observable->expects($this->once())
					->method('attach');

		// we create our object and pass our mock
		$object = new JEventStub($observable);

		// we reset the calls property.  Our stub method will populate this when it gets called
		$object->calls = array();

		// we setup the arguments to pass to update and call it.
		$args = array(
			'event' => 'myEvent'
		);

		// we call update and assert that it returns true (the value from the stub)
		$this->assertThat(
			$object->update($args),
			$this->equalTo(true)
		);

		// first, we want to assert that myEvent was called
		$this->assertThat(
			$object->calls[0]['method'],
			$this->equalTo('myEvent')
		);

		// with no arguments
		$this->assertThat(
			$object->calls[0]['args'],
			$this->equalTo(array())
		);

		// only once
		$this->assertThat(
			count($object->calls),
			$this->equalTo(1)
		);
	}

	/**
	 * tests the firing of the update event with one argument
	 */
	public function testUpdateOneArg()
	{
		// get a mock for the
		$observable = $this->getMock('Observable', array('attach'));

		// we expect that the attach method of our mock object will be called
		// because when we instantiate an observer it needs something observable
		// to attach itself to
		$observable->expects($this->once())
					->method('attach');

		// we create our object and pass our mock
		$object = new JEventStub($observable);

		// we reset the calls property.  Our stub method will populate this when it gets called
		$object->calls = array();

		// we setup the arguments to pass to update and call it.
		$args = array('myFirstArgument');
		$args['event'] = 'myEvent';

		// we call update and assert that it returns true (the value from the stub)
		$this->assertThat(
			$object->update($args),
			$this->equalTo(true)
		);

		// first, we want to assert that myEvent was called
		$this->assertThat(
			$object->calls[0]['method'],
			$this->equalTo('myEvent')
		);

		// with one arguments
		$this->assertThat(
			$object->calls[0]['args'],
			$this->equalTo(array('myFirstArgument'))
		);

		// only once
		$this->assertThat(
			count($object->calls),
			$this->equalTo(1)
		);
	}

	/**
	 * tests the firing of the update event with multiple arguments
	 */
	public function testUpdateMultipleArgs()
	{
		// get a mock for the
		$observable = $this->getMock('Observable', array('attach'));

		// we expect that the attach method of our mock object will be called
		// because when we instantiate an observer it needs something observable
		// to attach itself to
		$observable->expects($this->once())
					->method('attach');

		// we create our object and pass our mock
		$object = new JEventStub($observable);

		// we reset the calls property.  Our stub method will populate this when it gets called
		$object->calls = array();

		// we setup the arguments to pass to update and call it.
		$args = array('myFirstArgument', 5);
		$args['event'] = 'myEvent';

		// we call update and assert that it returns true (the value from the stub)
		$this->assertThat(
			$object->update($args),
			$this->equalTo(true)
		);

		// first, we want to assert that myEvent was called
		$this->assertThat(
			$object->calls[0]['method'],
			$this->equalTo('myEvent')
		);

		// with one arguments
		$this->assertThat(
			$object->calls[0]['args'],
			$this->equalTo(array('myFirstArgument', 5))
		);

		// only once
		$this->assertThat(
			count($object->calls),
			$this->equalTo(1)
		);
	}

	/**
	 * tests the firing of an event that does not exist
	 */
	public function testUpdateBadEvent()
	{
		// get a mock for the
		$observable = $this->getMock('Observable', array('attach'));

		// we expect that the attach method of our mock object will be called
		// because when we instantiate an observer it needs something observable
		// to attach itself to
		$observable->expects($this->once())
					->method('attach');

		// we create our object and pass our mock
		$object = new JEventStub($observable);

		// we reset the calls property.  Our stub method will populate this when it gets called
		$object->calls = array();

		// we setup the arguments to pass to update and call it.
		$args = array('myFirstArgument');
		$args['event'] = 'myNonExistentEvent';

		// we call update and assert that it returns null (the value from the stub)
		$this->assertThat(
			$object->update($args),
			$this->equalTo(null)
		);

		// first, we want to assert that no methods were called
		$this->assertThat(
			count($object->calls),
			$this->equalTo(0)
		);
	}
}
