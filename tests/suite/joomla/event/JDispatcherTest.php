<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Event
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/JDispatcherInspector.php';
require_once __DIR__ . '/JEventInspector.php';

/**
 * Test class for JDispatcher.
 *
 * @package		Joomla.UnitTest
 * @subpackage  Event
 * @since       11.3
 */
class JDispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var JDispatcher
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new JDispatcherInspector;
        $this->object->setInstance($this->object);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    	$this->object->setInstance(null);
    }

	/**
	 * Tests the JDispatcher::getInstance method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetInstance()
	{
		$mock = JDispatcher::getInstance();
		
		$this->assertInstanceOf(
			'JDispatcherInspector',
			$mock
		);
		
		$this->object->setInstance(null);
		
		$instance = JDispatcher::getInstance();

		$this->assertInstanceOf(
			'JDispatcher',
			$instance,
			'Tests that getInstance returns a JDispatcher object.'
		);

		// Push a new instance into the class.
		JDispatcherInspector::setInstance('foo');

		$this->assertThat(
			JDispatcher::getInstance(),
			$this->equalTo('foo'),
			'Tests that a subsequent call to JDispatcher::getInstance returns the cached singleton.'
		);
		
		JDispatcherInspector::setInstance($mock);
	}

    /**
     * Test JDispatcher::getState().
     * 
     * @return void
     * 
     * @since 11.3
     */
    public function testGetState()
    {
    	$this->assertThat(
    		$this->object->getState(),
    		$this->equalTo(null)
    	);
    	
    	$this->object->_state = 'test';
    	
    	$this->assertThat(
    		$this->object->getState(),
    		$this->equalTo('test')
    	);
    }

    /**
     * @todo Implement testRegister().
     */
    public function testRegister()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testTrigger().
     */
    public function testTrigger()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAttach().
     */
    public function testAttach()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testDetach().
     */
    public function testDetach()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}