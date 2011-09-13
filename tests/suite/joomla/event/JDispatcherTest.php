<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Event
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/JDispatcherInspector.php';

/**
 * Test class for JForm.
 *
 * @package		Joomla.UnitTest
 * @subpackage  Event
 * @since       11.3
 */
class JDispatcherTest extends JoomlaTestCase
{
	/**
	 * Tests the JDispatcher::getInstance method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetInstance()
	{
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
	}

	/**
	 * Tests the JDispatcher::register method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRegister()
	{
		$this->markTestIncomplete('Todo');
	}

	/**
	 * Tests the JDispatcher::trigger method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testTrigger()
	{
		$this->markTestIncomplete('Todo');
	}
}