<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JForm.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 */
class JFormFieldCheckboxesTest extends JoomlaTestCase
{
	/**
	 * Sets up dependencies for the test.
	 */
	protected function setUp()
	{
		jimport('joomla.form.form');
		jimport('joomla.form.formfield');
		require_once JPATH_PLATFORM.'/joomla/form/fields/checkboxes.php';
		include_once dirname(dirname(__FILE__)).'/inspectors.php';
	}

	/**
	 * Test the getInput method.
	 */
	public function testGetInput()
	{
		$form = new JFormInspector('form1');

		$this->assertThat(
			$form->load('<form><field name="checkboxes" type="checkboxes" /></form>'),
			$this->isTrue(),
			'Line:'.__LINE__.' XML string should load successfully.'
		);

		$field = new JFormFieldCheckboxes($form);

		$this->markTestIncomplete();
		
		// TODO: Should check all the attributes have come in properly.
	}
}
