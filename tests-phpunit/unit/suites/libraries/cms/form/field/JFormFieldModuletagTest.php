<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormFieldModuletag.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       3.1
 */
class JFormFieldModuletagTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Tests the getInput method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetInput()
	{
		$form = new JForm('form1');

		$this->assertThat(
			$form->load('<form><field name="moduletag" type="moduletag" label="Module Tag" description="Module Tag listing" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldModuletag($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$this->assertContains(
			'<option value="nav">nav</option>',
			$field->input,
			'Line:' . __LINE__ . ' The getInput method should return an option with various opening tags, verify nav tag is in list.'
		);
	}
}
