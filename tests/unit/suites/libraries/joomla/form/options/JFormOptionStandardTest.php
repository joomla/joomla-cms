<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadOptionClass('standard');

/**
 * Test class for JFormOptionStandard.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       12.3
 */
class JFormOptionStandardTest extends TestCase
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
		$element = simplexml_load_string('<option value="test">TEST</option>');

		$options = JFormOption::getOptions($element, 'TestField');

		$stores = JCache::getStores();

		$this->assertEquals(
			count($options),
			1,
			'Line:' . __LINE__ . ' There should be exactly one option.'
		);

		$option = array_pop($options);

		$this->assertEquals(
			$option->value,
			'test',
			'Line:' . __LINE__ . ' The option value should "test".'
		);

		$this->assertEquals(
			$option->text,
			JText::_('TEST'),
			'Line:' . __LINE__ . ' The the option text should be "TEST".'
		);
	}
}
