<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

jimport('joomla.base.adapter');
jimport('joomla.base.adapterinstance');

/**
 * Test class for JAdapterInstance.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Base
 * @since       11.1
 */
class JAdapterInstanceTest extends TestCase
{
	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$database = $this->getMockDatabase();
	}

	/**
	 * Tears down the fixture.
	 *
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetParent()
	{
		$this->object = new JAdapter(__DIR__, 'Test', 'stubs');

		$this->assertThat(
			$this->object->getAdapter('Testadapter3')->getParent(),
			$this->identicalTo($this->object)
		);
	}
}
