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
 * @subpackage	Form
 */
class JFormFieldFileTest extends JoomlaTestCase
{
	/**
	 * Sets up dependancies for the test.
	 */
	protected function setUp()
	{
		jimport('joomla.form.form');
		jimport('joomla.form.formfield');
		require_once JPATH_PLATFORM.'/joomla/form/fields/file.php';
		include_once dirname(dirname(__FILE__)).'/inspectors.php';
	}

	/**
	 * Test the getInput method.
	 */
	public function testGetInput()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load('<form><field name="file" type="file" /></form>'),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldFile($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:'.__LINE__.' The setup method should return true.'
		);

		$this->markTestIncomplete('Problems encountered in next assertion');

		$this->assertThat(
			strlen($field->input),
			$this->greaterThan(0),
			'Line:'.__LINE__.' The getInput method should return something without error.'
		);

		// TODO: Should check all the attributes have come in properly.
	}
}