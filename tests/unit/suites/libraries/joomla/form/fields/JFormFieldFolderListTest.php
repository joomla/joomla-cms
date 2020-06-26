<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

JFormHelper::loadFieldClass('folderlist');

/**
 * Test class for JFormFieldFolderList.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       3.0.0
 */
class JFormFieldFolderListTest extends TestCase
{
	/**
	 * Test the getInput method.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	public function testGetInput()
	{
		$form = new JForm('form1');

		$this->assertThat(
			$form->load('<form><field name="folderlist" type="folderlist" directory="modules" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldFolderList($form);

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

		$options = $field->options;
		$this->assertThat(
			count($options),
			$this->greaterThan(0),
			'Line:' . __LINE__ . ' The getOptions method should return several entries.'
		);

		$field->directory = JPATH_ROOT . '/modules';
		$this->assertEquals(
			$options,
			$field->options,
			'Line:' . __LINE__ . ' The getOptions method should return the same for relative and absolute paths.'
		);

		// TODO: Should check all the attributes have come in properly.
	}
}
