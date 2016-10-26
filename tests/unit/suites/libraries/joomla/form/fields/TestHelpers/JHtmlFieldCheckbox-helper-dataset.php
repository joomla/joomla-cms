<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Data set class for JHtmlFieldCheckbox.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlFieldCheckboxTest_DataSet
{
	public static $getInputTest = array(
		'NoValueNoChecked' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<input type="checkbox" name="myTestName" id="myTestId" value="1" />',
		),

		'ValueNoChecked' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'default' => 'red',
				'value' => 'red',
			),
			'<input type="checkbox" name="myTestName" id="myTestId" value="red" checked />',
		),

		'NoValueChecked' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'default' => 'red',
				'checked' => true,
			),
			'<input type="checkbox" name="myTestName" id="myTestId" value="red" checked />',
		),

		'Disabled' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'disabled' => true,
			),
			'<input type="checkbox" name="myTestName" id="myTestId" value="1" disabled />',
		),

		'Class' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			'<input type="checkbox" name="myTestName" id="myTestId" value="1" class="foo bar" />',
		),

		'Autofocus' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			'<input type="checkbox" name="myTestName" id="myTestId" value="1" autofocus />',
		),

		'OnchangeOnclick' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'onchange' => 'bar();',
				'onclick' => 'foo();',
			),
			'<input type="checkbox" name="myTestName" id="myTestId" value="1" onclick="foo();" onchange="bar();" />',
		),

		'Required' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'required' => true,
			),
			'<input type="checkbox" name="myTestName" id="myTestId" value="1" required aria-required="true" />',
		),

	);
}
