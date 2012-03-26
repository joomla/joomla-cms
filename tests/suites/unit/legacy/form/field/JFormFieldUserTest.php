<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JForm.
 *
 * @package		Joomla.UnitTest
 * @subpackage  Form
 */
class JFormFieldUserTest extends TestCase
{
	/**
	 * Sets up dependancies for the test.
	 */
	protected function setUp()
	{
		require_once JPATH_PLATFORM . '/legacy/form/field/user.php';
		include_once dirname(dirname(dirname(__DIR__))) . '/joomla/form/inspectors.php';
	}

	/**
	 * Test the getInput method.
	 * @covers JFormFieldUser::getInput()
	 * @covers JFormFieldUser::<!public>
	 */
	public function testGetInput()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load('<form><field name="user" type="user" /></form>'),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldUser($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true.'
		);

		$this->markTestIncomplete('Incomplete test');

		$this->assertThat(
			strlen($field->input),
			$this->greaterThan(0),
			'Line:'.__LINE__.' The getInput method should return something without error.'
		);

		// TODO: Should check all the attributes have come in properly.
	}
}
