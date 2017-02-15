<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Data set class for JHtmlFieldText.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlFieldTextTest_DataSet
{
	public static $getInputTest = array(
		'NoValue' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<input type="text" name="myTestName" id="myTestId" value="" class="form-control">',
		),

		'Value' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'value' => 'foobar',
			),
			'<input type="text" name="myTestName" id="myTestId" value="foobar" class="form-control">',
		),

		'Class' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			'<input type="text" name="myTestName" id="myTestId" value="" class="form-control foo bar">',
		),

		'Size' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'size' => 60,
			),
			'<input type="text" name="myTestName" id="myTestId" value="" class="form-control" size="60">',
		),

		'Disabled' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'disabled' => true,
			),
			'<input type="text" name="myTestName" id="myTestId" value="" class="form-control" disabled>',
		),

		'Readonly' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'readonly' => true,
			),
			'<input type="text" name="myTestName" id="myTestId" value="" class="form-control" readonly>',
		),

		'Hint' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'hint' => 'Type any text.',
			),
			'<input type="text" name="myTestName" id="myTestId" value="" class="form-control" placeholder="Type any text.">',
		),

		'Autocomplete' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autocomplete' => false,
			),
			'<input type="text" name="myTestName" id="myTestId" value="" class="form-control" autocomplete="off">',
		),

		'Autofocus' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			'<input type="text" name="myTestName" id="myTestId" value="" class="form-control" autofocus>',
		),

		'Spellcheck' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'spellcheck' => false,
			),
			'<input type="text" name="myTestName" id="myTestId" value="" class="form-control" spellcheck="false">',
		),

		'Onchange' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'onchange' => 'foobar();',
			),
			'<input type="text" name="myTestName" id="myTestId" value="" class="form-control" onchange="foobar();">',
		),

		'Maxlength' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'maxLength' => 250,
			),
			'<input type="text" name="myTestName" id="myTestId" value="" class="form-control" maxlength="250">',
		),

		'Required' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'required' => true,
			),
			'<input type="text" name="myTestName" id="myTestId" value="" class="form-control" required aria-required="true">',
		),

	);
}
