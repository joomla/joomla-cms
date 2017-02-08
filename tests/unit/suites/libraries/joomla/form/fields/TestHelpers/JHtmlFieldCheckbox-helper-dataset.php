<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
			'<div class="form-check"><label class="form-check-label"><input type="checkbox" name="myTestName" id="myTestId" value="1" class="form-check-input" /></label></div>',
		),

		'ValueNoChecked' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'default' => 'red',
				'value' => 'red',
			),
			'<div class="form-check"><label class="form-check-label"><input type="checkbox" name="myTestName" id="myTestId" value="red" class="form-check-input" checked /></label></div>',
		),

		'NoValueChecked' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'default' => 'red',
				'checked' => true,
			),
			'<div class="form-check"><label class="form-check-label"><input type="checkbox" name="myTestName" id="myTestId" value="red" class="form-check-input" checked /></label></div>',
		),

		'Disabled' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'disabled' => true,
			),
			'<div class="form-check"><label class="form-check-label"><input type="checkbox" name="myTestName" id="myTestId" value="1" class="form-check-input" disabled /></label></div>',
		),

		'Class' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			'<div class="form-check"><label class="form-check-label"><input type="checkbox" name="myTestName" id="myTestId" value="1" class="form-check-input foo bar" /></label></div>',
		),

		'Autofocus' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			'<div class="form-check"><label class="form-check-label"><input type="checkbox" name="myTestName" id="myTestId" value="1" class="form-check-input" autofocus /></label></div>',
		),

		'OnchangeOnclick' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'onchange' => 'bar();',
				'onclick' => 'foo();',
			),
			'<div class="form-check"><label class="form-check-label"><input type="checkbox" name="myTestName" id="myTestId" value="1" class="form-check-input" onclick="foo();" onchange="bar();" /></label></div>',
		),

		'Required' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'required' => true,
			),
			'<div class="form-check"><label class="form-check-label"><input type="checkbox" name="myTestName" id="myTestId" value="1" class="form-check-input" required aria-required="true" /></label></div>',
		),

	);
}
