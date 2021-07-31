<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

jimport('joomla.base.adapter');
jimport('joomla.base.adapterinstance');

/**
 * Test class for JAdapterInstance.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Base
 * @since       1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
