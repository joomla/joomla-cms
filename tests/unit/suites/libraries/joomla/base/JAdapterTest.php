<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

jimport('joomla.base.adapter');
jimport('joomla.base.adapterinstance');

/**
 * Test class for JAdapter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Base
 * @since       11.1
 */
class JAdapterTest extends TestCase
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
	 * Tests the JAdapter::getDbo method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetDbo()
	{
		$this->object = new JAdapter(__DIR__, 'Test', 'stubs');

		$this->assertThat(
			$this->object->getDbo(),
			$this->isInstanceOf('JDatabaseDriver')
		);
	}

	/**
	 * Tests the JAdapter::setAdapter method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testSetAdapter()
	{
		$this->object = new JAdapter(__DIR__, 'Test', 'stubs');
		$this->object->setAdapter('Testadapter');

		$this->assertThat(
			$this->object->getAdapter('Testadapter'),
			$this->isInstanceOf('TestTestadapter')
		);

		$this->assertThat(
			$this->object->setAdapter('NoAdapterHere'),
			$this->isFalse()
		);

		$this->assertThat(
			$this->object->setAdapter('Testadapter2'),
			$this->isFalse()
		);
	}

	/**
	 * Tests the JAdapter::getAdapter method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetAdapter()
	{
		$this->object = new JAdapter(__DIR__, 'Test', 'stubs');

		$this->assertThat(
			$this->object->getAdapter('Testadapter3'),
			$this->isInstanceOf('TestTestadapter3')
		);

		$this->assertThat(
			$this->object->getAdapter('badadapter'),
			$this->isFalse()
		);
	}

	/**
	 * Tests the JAdapter::loadAllAdapters method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testLoadAllAdapters()
	{
		$this->object = new JAdapter(__DIR__, 'Test', 'stubs');
		$this->object->loadAllAdapters();

		$this->assertThat(
			$this->object->getAdapter('Testadapter4'),
			$this->isInstanceOf('TestTestadapter4')
		);
	}
}
