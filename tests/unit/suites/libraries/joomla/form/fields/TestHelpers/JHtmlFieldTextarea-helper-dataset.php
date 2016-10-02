<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Data set class for JHtmlFieldTextarea.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlFieldTextareaTest_DataSet
{
	public static $getInputTest = array(
		'NoValue' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<textarea name="myTestName" id="myTestId" ></textarea>',
		),

		'Value' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'value' => 'This is textarea text.',
			),
			'<textarea name="myTestName" id="myTestId" >This is textarea text.</textarea>',
		),

		'Rows' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'rows' => 55,
			),
			'<textarea name="myTestName" id="myTestId" rows="55" ></textarea>',
		),

		'Columns' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'columns' => 55,
			),
			'<textarea name="myTestName" id="myTestId" cols="55" ></textarea>',
		),

		'Class' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			'<textarea name="myTestName" id="myTestId" class="foo bar" ></textarea>',
		),

		'Disabled' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'disabled' => true,
			),
			'<textarea name="myTestName" id="myTestId" disabled ></textarea>',
		),

		'Readonly' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'readonly' => true,
			),
			'<textarea name="myTestName" id="myTestId" readonly ></textarea>',
		),

		'Hint' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'hint' => 'Placeholder for textarea.',
			),
			'<textarea name="myTestName" id="myTestId" placeholder="Placeholder for textarea." ></textarea>',
		),

		'Autocomplete' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autocomplete' => false,
			),
			'<textarea name="myTestName" id="myTestId" autocomplete="off" ></textarea>',
		),

		'Autofocus' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			'<textarea name="myTestName" id="myTestId" autofocus ></textarea>',
		),

		'Spellcheck' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'spellcheck' => false,
			),
			'<textarea name="myTestName" id="myTestId" spellcheck="false" ></textarea>',
		),

		'Onchange' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'onchange' => 'foobar();',
			),
			'<textarea name="myTestName" id="myTestId" onchange="foobar();" ></textarea>',
		),

		'Onclick' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'onclick' => 'barfoo();',
			),
			'<textarea name="myTestName" id="myTestId" onclick="barfoo();" ></textarea>',
		),

		'Required' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'required' => true,
			),
			'<textarea name="myTestName" id="myTestId" required aria-required="true" ></textarea>',
		),
	);
}
