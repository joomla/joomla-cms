<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
	static public $getInputTest = array(
		'NoValue' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<input type="number" name="myTestName" id="myTestId" value="0" />',
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
			'<input type="number" name="myTestName" id="myTestId" value="0" max="200" />',
		),

		'Step' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'step' => 5,
			),
			'<input type="number" name="myTestName" id="myTestId" value="0" step="5" />',
		),

		'Class' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			'<input type="number" name="myTestName" id="myTestId" value="0" class="foo bar" />',
		),

		'Disabled' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'disabled' => true,
			),
			'<input type="number" name="myTestName" id="myTestId" value="0" disabled />',
		),

		'Readonly' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'readonly' => true,
			),
			'<input type="number" name="myTestName" id="myTestId" value="0" readonly />',
		),

		'Autofocus' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			'<input type="number" name="myTestName" id="myTestId" value="0" autofocus />',
		),

		'Onchange' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'onchange' => 'foobar();',
			),
			'<input type="number" name="myTestName" id="myTestId" value="0" onchange="foobar();" />',
		),
	);
}
