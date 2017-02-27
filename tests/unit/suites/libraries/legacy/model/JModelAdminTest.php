<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JModelAdmin.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Model
 *
 * @since       12.3
 */
class JModelAdminTest extends TestCase
{
	/**
	 * @var    JModelAdmin
	 * @since  12.3
	 */
	public $object;

	/**
	 * Setup each test.
	 *
	 * @since   12.3
	 *
	 * @return  void
	 */
	public function setUp()
	{
		// Create mock of abstract class JModelAdmin to test concrete methods in there
		$this->object = $this->getMockForAbstractClass('JModelAdmin');
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->object);
	}

	/**
	 * Test JModelAdmin::__construct
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox Constructor applies configuration
	 */
	public function testConstructorAppliesConfiguration()
	{
		$config = array(
			'event_after_delete' => 'event_after_delete',
			'event_after_save' => 'event_after_save',
			'event_before_delete' => 'event_before_delete',
			'event_before_save' => 'event_before_save',
			'event_change_state' => 'event_change_state',
			'text_prefix' => 'text_prefix'
		);

		$this->object->__construct($config);

		// Check if config was applied correctly
		foreach ($config as $key => $value)
		{
			if ($key == "text_prefix")
			{
				$this->assertEquals(strtoupper($value), TestReflection::getValue($this->object, $key));
			}
			else
			{
				$this->assertEquals($value, TestReflection::getValue($this->object, $key));
			}
		}
	}

	/**
	 * Test JModelAdmin::__construct
	 *
	 * @since   3.4
	 *
	 * @return  void
	 *
	 * @testdox Constructor applies default configuration
	 */
	public function testConstructorAppliesDefaultConfiguration()
	{
		$config = array(
			'event_after_delete' => 'onContentAfterDelete',
			'event_after_save' => 'onContentAfterSave',
			'event_before_delete' => 'onContentBeforeDelete',
			'event_before_save' => 'onContentBeforeSave',
			'event_change_state' => 'onContentChangeState',
			'text_prefix' => 'COM_MOCK_J'
		);

		foreach ($config as $key => $value)
		{
			$this->assertEquals($value, TestReflection::getValue($this->object, $key));
		}
	}
}
