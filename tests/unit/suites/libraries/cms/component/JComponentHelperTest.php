<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JComponentHelper.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.2
 */
class JComponentHelperTest extends TestCaseDatabase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.2
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');

		return $dataSet;
	}

	/**
	 * Test JComponentHelper::getComponent
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JComponentHelper::getComponent
	 */
	public function testGetComponent()
	{
		$component = JComponentHelper::getComponent('com_content');

		$this->assertEquals(22, $component->id,	'com_content is extension ID 22');
		$this->assertInstanceOf('JRegistry', $component->params, 'Parameters need to be of type JRegistry');
		$this->assertEquals('1', $component->params->get('show_title'), 'The show_title parameter of com_content should be set to 1');
		$this->assertObjectHasAttribute('enabled', $component, 'The component data needs to have an enabled field');
		$this->assertSame($component, JComponentHelper::getComponent('com_content'), 'The object returned must always be the same');
	}

	/**
	 * Test JComponentHelper::getComponent
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentHelper::getComponent
	 */
	public function testGetComponent_falseComponent()
	{
		$component = JComponentHelper::getComponent('com_false');
		$this->assertObjectNotHasAttribute('id', $component, 'Anonymous component does not have an ID');
		$this->assertInstanceOf('JRegistry', $component->params, 'Parameters need to be of type JRegistry');
		$this->assertEquals(0, $component->params->count(), 'Anonymous component does not have any set parameters');
		$this->assertObjectHasAttribute('enabled', $component, 'The component data needs to have an enabled field');
		$this->assertTrue($component->enabled, 'The anonymous component has to be enabled by default if not strict');
	}

	/**
	 * Test JComponentHelper::getComponent
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentHelper::getComponent
	 */
	public function testGetComponent_falseComponent_strict()
	{		
		$component = JComponentHelper::getComponent('com_false', true);
		$this->assertFalse($component->enabled, 'The anonymous component has to be disabled by default if strict');
	}

	/**
	 * Test JComponentHelper::isEnabled
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JComponentHelper::isEnabled
	 */
	public function testIsEnabled()
	{
		$this->assertTrue(
			(bool) JComponentHelper::isEnabled('com_content'),
			'com_content should be enabled'
		);
	}

	/**
	 * Test JComponentHelper::isInstalled
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentHelper::isInstalled
	 */
	public function testIsInstalled()
	{
		$this->assertTrue(
			(bool) JComponentHelper::isInstalled('com_content'),
			'com_content should be installed'
		);

		$this->assertFalse(
			(bool) JComponentHelper::isInstalled('com_willneverhappen'),
			'com_willneverhappen should not be enabled'
		);
	}

	/**
	 * Test JComponentHelper::getParams
	 *
	 * @return  void
	 * @covers  JComponentHelper::getParams
	 */
	public function testGetParams()
	{
		$params = JComponentHelper::getParams('com_content');

		$this->assertEquals(
			$params->get('show_print_icon'),
			'1',
			"com_content's show_print_icon param should be set to 1"
		);
	}
}
