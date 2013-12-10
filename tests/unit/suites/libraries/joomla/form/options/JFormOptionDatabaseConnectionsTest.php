<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadOptionClass('databaseconnections');

/**
 * Test class for JFormOptionDatabaseConnections.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       12.3
 */
class JFormOptionDatabaseConnectionsTest extends TestCase
{
	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockApplication();

		$this->backupServer = $_SERVER;

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '';
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
		$_SERVER = $this->backupServer;

		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Test that the correct options are generated.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetOptions()
	{
		$element = simplexml_load_string('<option provider="databaseconnections" />');

		$options = JFormOption::getOptions($element, 'TestField');

		$connectors = JDatabaseDriver::getConnectors();

		// Skip test of no connection types are available.
		if (empty($connectors))
		{
			$this->markTestSkipped('Line:' . __LINE__ . ' Test skipped because no supported database connection types could be found.');
			return;
		}

		$this->assertCount(
			count($connectors),
			$options,
			'Line:' . __LINE__ . ' There should be exactly one option per connection type.'
		);

		foreach ($options as $option)
		{
			$this->assertContains(
				$option->value,
				$connectors,
				'Line:' . __LINE__ . ' The option value should be one of the store types.'
			);

			$this->assertEquals(
				$option->text,
				JText::_(ucfirst($option->value)),
				'Line:' . __LINE__ . ' The the option text should be derived from the value.'
			);

		}
	}

	/**
	 * Test that the correct options are generated when limited by supported types.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetOptionsSupported()
	{
		// Get a list of all available connection types.
		$connectors = JDatabaseDriver::getConnectors();

		// Skip test of no connection types are available.
		if (empty($connectors))
		{
			$this->markTestSkipped('Line:' . __LINE__ . ' Test skipped because no supported database connection types could be found.');
			return;
		}

		// Request only a subset of them.
		$supported = array_slice($connectors, 0, 2);

		$xml = '<option provider="databaseconnections" supported="' . implode(',', $supported) . '" />';
		$element = simplexml_load_string($xml);

		$options = JFormOption::getOptions($element, 'TestField');

		$this->assertCount(
			count($supported),
			$options,
			'Line:' . __LINE__ . ' There should be exactly one option per "supported" connection type.'
		);

		foreach ($options as $option)
		{
			$this->assertContains(
				$option->value,
				$supported,
				'Line:' . __LINE__ . ' The option value should be one of the "supported" store types.'
			);

			$this->assertEquals(
				$option->text,
				JText::_(ucfirst($option->value)),
				'Line:' . __LINE__ . ' The the option text should be derived from the value.'
			);
		}
	}

	/**
	 * Test that the correct options are generated when limited to unavailable types.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetOptionsSupportedNone()
	{
		// Request a type of connection that does is not available.
		$xml = '<option provider="databaseconnections" supported="AFakeTypeWhichCouldNotPossiblyExist" />';
		$element = simplexml_load_string($xml);

		$options = JFormOption::getOptions($element, 'TestField');

		$this->assertCount(
			1,
			$options,
			'Line:' . __LINE__ . ' There should be exactly one option.'
		);

		$option = array_pop($options);

		$this->assertEquals(
			$option->value,
			'',
			'Line:' . __LINE__ . ' The option value should be empty.'
		);

		$this->assertEquals(
			$option->text,
			JText::_('JNONE'),
			'Line:' . __LINE__ . ' The the option text should be "None".'
		);
	}
}
