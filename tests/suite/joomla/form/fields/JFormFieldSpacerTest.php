<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Test class for JForm.
 *
 * @package		Joomla.UnitTest
 * @subpackage  Form
 */
class JFormFieldSpacerTest extends JoomlaTestCase
{
	/**
	 * Sets up dependancies for the test.
	 */
	protected function setUp()
	{
		jimport('joomla.form.form');
		jimport('joomla.form.formfield');
		require_once JPATH_PLATFORM.'/joomla/form/fields/spacer.php';
		include_once dirname(dirname(__FILE__)).'/inspectors.php';
	}

	/**
	 * Test the getInput method.
	 */
	public function testGetInput()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" /></form>'),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true.'
		);

		$this->assertThat(
			strlen($field->input),
			$this->greaterThan(0),
			'Line:'.__LINE__.' The getInput method should return something without error.'
		);
	}

	/**
	 * Test the getLabel method.
	 */
	public function testGetLabel()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" /></form>'),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true.'
		);

		$this->assertEquals(
			$field->label,
			'<span class="spacer"><span class="before"></span><span class=""><label id="spacer-lbl" class="">spacer</label></span><span class="after"></span></span>' ,
			'Line:'.__LINE__.' The getLabel method should return something without error.'
		);

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" class="text" /></form>'),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true.'
		);

		$this->assertEquals(
			$field->label,
			'<span class="spacer"><span class="before"></span><span class="text"><label id="spacer-lbl" class="">spacer</label></span><span class="after"></span></span>' ,
			'Line:'.__LINE__.' The getLabel method should return something without error.'
		);

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" class="text" label="MyLabel" /></form>'),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true.'
		);

		$this->assertEquals(
			$field->label,
			'<span class="spacer"><span class="before"></span><span class="text"><label id="spacer-lbl" class="">MyLabel</label></span><span class="after"></span></span>' ,
			'Line:'.__LINE__.' The getLabel method should return something without error.'
		);

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" hr="true" /></form>'),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true.'
		);

		$this->assertEquals(
			$field->label,
			'<span class="spacer"><span class="before"></span><span class=""><hr class="" /></span><span class="after"></span></span>' ,
			'Line:'.__LINE__.' The getLabel method should return something without error.'
		);
	}
	/**
	 * Test the getTitle method.
	 */
	public function testGetTitle()
	{
		$this->testGetLabel();
	}
}
