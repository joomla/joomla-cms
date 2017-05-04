<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Data set class for JHtmlFieldUrl.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlFieldUrlTest_DataSet
{
	public static $getInputTest = array(
		'NoValue' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<input type="url" name="myTestName" id="myTestId" value="" />',
		),

		'Value' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'value' => 'http://foobar.com',
			),
			'<input type="url" name="myTestName" id="myTestId" value="http://foobar.com" />',
		),

		// Stript always illegal characters that may be used in XSS.
		'Value2' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'value' => 'http://<>"foobar.com',
			),
			'<input type="url" name="myTestName" id="myTestId" value="http://&lt;&gt;&quot;foobar.com" />',
		),

		'Class' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			'<input type="url" name="myTestName" class="foo bar" id="myTestId" value="" />',
		),

		'Size' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'size' => 60,
			),
			'<input type="url" name="myTestName" id="myTestId" value="" size="60" />',
		),

		'Disabled' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'disabled' => true,
			),
			'<input type="url" name="myTestName" id="myTestId" value="" disabled />',
		),

		'Readonly' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'readonly' => true,
			),
			'<input type="url" name="myTestName" id="myTestId" value="" readonly />',
		),

		'Hint' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'hint' => 'Type any url.',
			),
			'<input type="url" name="myTestName" id="myTestId" value="" placeholder="Type any url." />',
		),

		'Autocomplete' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autocomplete' => false,
			),
			'<input type="url" name="myTestName" id="myTestId" value="" autocomplete="off" />',
		),

		'Autofocus' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			'<input type="url" name="myTestName" id="myTestId" value="" autofocus />',
		),

		'Spellcheck' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'spellcheck' => false,
			),
			'<input type="url" name="myTestName" id="myTestId" value="" spellcheck="false" />',
		),

		'Onchange' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'onchange' => 'foobar();',
			),
			'<input type="url" name="myTestName" id="myTestId" value="" onchange="foobar();" />',
		),

		'Maxlength' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'maxlength' => 250,
			),
			'<input type="url" name="myTestName" id="myTestId" value="" maxlength="250" />',
		),

		'Required' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'required' => true,
			),
			'<input type="url" name="myTestName" class="required" id="myTestId" value="" required aria-required="true" />',
		),
	);
}
