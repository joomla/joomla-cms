<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('hidden');

/**
 * Test class for JFormFieldHidden.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldHiddenTest extends TestCase
{
	/**
	 * Test the getInput method.
	 *
	 * @return void
	 */
	public function testGetInput()
	{
		$form = new JForm('form1');

		// Test a traditional hidden field type.

		$this->assertThat(
			$form->load('<form><field name="hidden" type="hidden" label="foo" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldHidden($form);

		$this->assertThat(
			$form->getLabel('hidden'),
			$this->equalTo(''),
			'Line:' . __LINE__ . ' The label of a hidden element should be nothing.'
		);

		// Test a field with attribute hidden = true.

		$this->assertThat(
			$form->load('<form><field name="hidden" type="text" label="foo" hidden="true" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldHidden($form);

		$this->assertThat(
			$form->getLabel('hidden'),
			$this->equalTo(''),
			'Line:' . __LINE__ . ' The label of a hidden element should be nothing.'
		);

		// Test a field with attribute hidden = false.

		$this->assertThat(
			$form->load('<form><field name="hidden" type="text" label="foo" hidden="false" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldHidden($form);

		$this->assertThat(
			$form->getLabel('hidden'),
			$this->equalTo('<label id="hidden-lbl" for="hidden" class="">foo</label>'),
			'Line:' . __LINE__ . ' The label of a non-hidden element should be some HTML.'
		);
	}
}
