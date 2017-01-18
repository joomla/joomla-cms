<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Data set class for JHtmlFieldNumber.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlFieldNumberTest_DataSet
{
	public static $getInputTest = array(
		'NoValue' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<input type="number" name="myTestName" id="myTestId" value="" />',
		),

		'Value' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'value' => 2,
			),
			'<input type="number" name="myTestName" id="myTestId" value="2" />',
		),

		'Min' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'min' => 2,
			),
			'<input type="number" name="myTestName" id="myTestId" value="2" min="2" />',
		),

		'Max' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'max' => 200,
			),
			'<input type="number" name="myTestName" id="myTestId" value="" max="200" />',
		),

		'Step' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'step' => 5,
			),
			'<input type="number" name="myTestName" id="myTestId" value="" step="5" />',
		),

		'Class' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			'<input type="number" name="myTestName" id="myTestId" value="" class="foo bar" />',
		),

		'Disabled' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'disabled' => true,
			),
			'<input type="number" name="myTestName" id="myTestId" value="" disabled />',
		),

		'Readonly' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'readonly' => true,
			),
			'<input type="number" name="myTestName" id="myTestId" value="" readonly />',
		),

		'Autofocus' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			'<input type="number" name="myTestName" id="myTestId" value="" autofocus />',
		),

		'Onchange' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'onchange' => 'foobar();',
			),
			'<input type="number" name="myTestName" id="myTestId" value="" onchange="foobar();" />',
		),
	);
}
