<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JToolbarButton.
 */
class JToolbarButtonTest extends TestCase
{
	/**
	 * @var    JToolbar
	 * @since  3.0
	 */
	protected $toolbar;

	/**
	 * Since JToolbarButton is abstract, test that class with a child class
	 *
	 * @var    JToolbarButtonStandard
	 * @since  3.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->toolbar = JToolbar::getInstance();
		$this->object  = $this->toolbar->loadButtonType('standard');

		$this->saveFactoryState();

		JFactory::$application = $this->getMockApplication();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests the constructor
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function test__construct()
	{
		$this->assertThat(
			new JToolbarButtonStandard($this->toolbar),
			$this->isInstanceOf('JToolbarButton')
		);
	}

	/**
	 * Tests the getName method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetName()
	{
		$this->assertThat(
			$this->object->getName(),
			$this->equalTo('Standard')
		);
	}

	/**
	 * @todo   Implement testRender().
	 */
	public function testRender()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.'
		);
	}

	/**
	 * Tests the fetchIconClass method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testFetchIconClass()
	{
		$this->assertThat(
			$this->object->fetchIconClass('standard'),
			$this->equalTo('icon-standard')
		);
	}
}
