<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/PlgSystemBase.php';
require_once __DIR__ . '/stubs/PlgSystemJoomla.php';
require_once __DIR__ . '/stubs/PlgSystemPrivate.php';

/**
 * Test class for JPlugin.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Plugin
 * @since       3.2
 */
class JPluginTest extends TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function setUp()
	{
		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
		JFactory::$database    = $this->getMockDatabase();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
	
		parent::tearDown();
	}

	/**
	 * Test constructor with app and database variables
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function test__constructWithAppAndDb()
	{
		// Load our test plugin
		$plugin = new PlgSystemJoomla;

		$this->assertInstanceOf(
			'JApplicationBase',
			TestReflection::getValue($plugin, 'app'),
			'Assert the $app property is an instance of JApplicationBase'
		);

		$this->assertInstanceOf(
			'JDatabaseDriver',
			TestReflection::getValue($plugin, 'db'),
			'Assert the $db property is an instance of JDatabaseDriver'
		);

		$this->assertThat(
			TestReflection::getValue($plugin, '_name'),
			$this->equalTo('Joomla')
		);
	}

	/**
	 * Test constructor without app and database variables
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function test__constructWithoutAppAndDb()
	{
		// Load our test plugin
		$plugin = new PlgSystemBase;

		$this->assertClassNotHasAttribute(
			'app',
			'PlgSystemBase',
			'Assert the $app property does not exist'
		);

		$this->assertClassNotHasAttribute(
			'db',
			'PlgSystemBase',
			'Assert the $db property does not exist'
		);

		$this->assertThat(
			TestReflection::getValue($plugin, '_name'),
			$this->equalTo('Base')
		);
	}

	/**
	 * Test constructor without app and database variables
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function test__constructPrivateAppAndDb()
	{
		// Load our test plugin
		$plugin = new PlgSystemPrivate;

		$this->assertNull(
			TestReflection::getValue($plugin, 'app'),
			'Assert the $app property is not set if private and a fatal error does not occur'
		);

		$this->assertNull(
			TestReflection::getValue($plugin, 'db'),
			'Assert the $db property is not set if private and a fatal error does not occur'
		);

		$this->assertThat(
			TestReflection::getValue($plugin, '_name'),
			$this->equalTo('Private')
		);
	}
}
