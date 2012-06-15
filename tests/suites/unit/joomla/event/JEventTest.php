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
}