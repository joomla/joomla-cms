<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormFieldList.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldListTest extends TestCase
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
			$form->load('<form><field name="list" type="list" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldList($form);

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

		// TODO: Should check all the attributes have come in properly.
	}

	/**
	 * Test for JFormFieldList::addOption method.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testAddOption()
	{
		$form = new JForm('form1');
		$form->load('<form><field name="list" type="list" /></form>');

		/** @var  \JFormFieldList  $field */
		$field = $form->getField('list');

		// Test adding a simple option
		$this->assertThat(
			$field->addOption('Certainly', '3'),
			$this->isInstanceOf(get_class($field)),
			'Line:' . __LINE__ . ' The method should return instance of the form field itself.'
		);

		$this->assertThat(
			$form->getFieldXml('list')->asXML(),
			$this->stringContains('<option value="3">Certainly</option>'),
			'Line:' . __LINE__ . ' The method should have added the new option [3 => Certainly].'
		);

		// Test adding with all possible arguments
		$field->addOption('Whatever', '4', array('class' => 'disabled', 'selected' => 'selected'), 'Group');

		$this->assertThat(
			$form->getFieldXml('list')->asXML(),
			$this->stringContains('<group label="Group"><option value="4" class="disabled" selected="selected">Whatever</option></group>'),
			'Line:' . __LINE__ . ' The method should add a new option group and the new option inside it with given 2 attributes.'
		);

		// Test adding with 3.7.0 B/C arguments
		$field->addOption('Something', array('value' => 5, 'class' => 'disabled'));

		$this->assertThat(
			$form->getFieldXml('list')->asXML(),
			$this->stringContains('<option value="5" class="disabled">Something</option>'),
			'Line:' . __LINE__ . ' The method should add the new option with given attributes.'
		);

		$field->addOption('Something Else');

		$this->assertThat(
			$form->getFieldXml('list')->asXML(),
			$this->stringContains('<option>Something Else</option>'),
			'Line:' . __LINE__ . ' The method should add a new option with no value.'
		);
	}
}
