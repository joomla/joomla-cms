<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormFieldModuletag.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       3.1
 */
class JFormFieldModuletagTest extends TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests the getInput method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetInput()
	{
		$field = new JFormFieldModuletag;
		$field->setup(
			new SimpleXMLElement('<field name="moduletag" type="moduletag" label="Module Tag" description="Module Tag listing" />'),
			'value'
		);

		$this->assertContains(
			'<option value="nav">nav</option>',
			$field->input,
			'The getInput method should return an option with various opening tags, verify nav tag is in list.'
		);
	}
}
