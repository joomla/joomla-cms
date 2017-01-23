<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Data set class for JHtmlFieldTel.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlFieldTelTest_DataSet
{
	public static $getInputTest = array(
		'NoValue' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<input type="tel" name="myTestName" class="form-control" id="myTestId" value="" />',
		),

		'Value' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'value' => 'foobar',
			),
			'<input type="tel" name="myTestName" class="form-control" id="myTestId" value="foobar" />',
		),

		'Class' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			'<input type="tel" name="myTestName" class="form-control foo bar" id="myTestId" value="" />',
		),

		'Size' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'size' => 60,
			),
			'<input type="tel" name="myTestName" class="form-control" id="myTestId" value="" size="60" />',
		),

		'Disabled' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'disabled' => true,
			),
			'<input type="tel" name="myTestName" class="form-control" id="myTestId" value="" disabled />',
		),

		'Readonly' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'readonly' => true,
			),
			'<input type="tel" name="myTestName" class="form-control" id="myTestId" value="" readonly />',
		),

		'Hint' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'hint' => 'Type any tel.',
			),
			'<input type="tel" name="myTestName" class="form-control" id="myTestId" value="" placeholder="Type any tel." />',
		),

		'Autocomplete' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autocomplete' => false,
			),
			'<input type="tel" name="myTestName" class="form-control" id="myTestId" value="" autocomplete="off" />',
		),

		'Autofocus' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			'<input type="tel" name="myTestName" class="form-control" id="myTestId" value="" autofocus />',
		),

		'Spellcheck' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'spellcheck' => false,
			),
			'<input type="tel" name="myTestName" class="form-control" id="myTestId" value="" spellcheck="false" />',
		),

		'Onchange' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'onchange' => 'foobar();',
			),
			'<input type="tel" name="myTestName" class="form-control" id="myTestId" value="" onchange="foobar();" />',
		),

		'Maxlength' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'maxLength' => 250,
			),
			'<input type="tel" name="myTestName" class="form-control" id="myTestId" value="" maxlength="250" />',
		),

		'Required' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'required' => true,
			),
			'<input type="tel" name="myTestName" class="form-control" id="myTestId" value="" required aria-required="true" />',
		),

	);
}
