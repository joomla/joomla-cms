<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('combo');

/**
 * Test class for JFormFieldCombo.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldComboTest extends TestCase
{
	/**
	 * Test the getInput method.
	 *
	 * @return void
	 */
	public function testGetInput()
	{
		$form = new JForm('form1');

		$this->assertThat(
			$form->load('<form><field name="combo" type="combo" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldCombo($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);
	}
}
