<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JForm.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @since       11.1
 */
class JFormFieldSpacerTest extends TestCase
{
	/**
	 * Sets up dependancies for the test.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		require_once JPATH_PLATFORM . '/joomla/form/fields/spacer.php';
		require_once JPATH_TESTS . '/stubs/FormInspectors.php';
	}

	/**
	 * Test the getInput method.
	 *
	 * @return void
	 */
	public function testGetInput()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$this->assertThat(
			strlen($field->input),
			$this->greaterThan(0),
			'Line:' . __LINE__ . ' The getInput method should return something without error.'
		);
	}

	/**
	 * Test the getLabel method.
	 *
	 * @return void
	 */
	public function testGetLabel()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" description="spacer" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$equals = '<span class="spacer"><span class="before"></span><span class="">' .
			'<label id="spacer-lbl" class="hasTip" title="spacer::spacer">spacer</label></span>' .
			'<span class="after"></span></span>';

		$this->assertEquals(
			$field->label,
			$equals,
			'Line:' . __LINE__ . ' The getLabel method should return something without error.'
		);

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" class="text" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$equals = '<span class="spacer"><span class="before"></span><span class="text">' .
			'<label id="spacer-lbl" class="">spacer</label></span><span class="after"></span></span>';

		$this->assertEquals(
			$field->label,
			$equals,
			'Line:' . __LINE__ . ' The getLabel method should return something without error.'
		);

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" class="text" label="MyLabel" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$equals = '<span class="spacer"><span class="before"></span><span class="text">' .
			'<label id="spacer-lbl" class="">MyLabel</label></span><span class="after"></span></span>';

		$this->assertEquals(
			$field->label,
			$equals,
			'Line:' . __LINE__ . ' The getLabel method should return something without error.'
		);

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" hr="true" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$expected = '<span class="spacer"><span class="before"></span><span class=""><hr class="" /></span>' .
			'<span class="after"></span></span>';

		$this->assertEquals(
			$field->label,
			$expected,
			'Line:' . __LINE__ . ' The getLabel method should return something without error.'
		);
	}

	/**
	 * Test the getTitle method.
	 *
	 * @return void
	 */
	public function testGetTitle()
	{
		$this->testGetLabel();
	}
}
