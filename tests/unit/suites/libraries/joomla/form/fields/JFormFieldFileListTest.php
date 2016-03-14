<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('filelist');

/**
 * Test class for JFormFieldFileList.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       12.1
 */
class JFormFieldFileListTest extends TestCase
{
	/**
	 * Test the getInput method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetInput()
	{
		$form = new JForm('form1');

		$this->assertThat(
			$form->load('<form><field name="filelist" type="filelist" directory="modules/mod_finder/tmpl" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldFileList($form);

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
}
