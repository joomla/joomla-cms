<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
			'<textarea name="myTestName" id="myTestId" class="form-control" ></textarea>',
		),

		'Value' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'value' => 'This is textarea text.',
			),
			'<textarea name="myTestName" id="myTestId" class="form-control" >This is textarea text.</textarea>',
		),

		'Rows' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'rows' => 55,
			),
			'<textarea name="myTestName" id="myTestId" rows="55" class="form-control" ></textarea>',
		),

		'Columns' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'columns' => 55,
			),
			'<textarea name="myTestName" id="myTestId" cols="55" class="form-control" ></textarea>',
		),

		'Class' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			'<textarea name="myTestName" id="myTestId" class="form-control foo bar" ></textarea>',
		),

		'Disabled' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'disabled' => true,
			),
			'<textarea name="myTestName" id="myTestId" class="form-control" disabled ></textarea>',
		),

		'Readonly' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'readonly' => true,
			),
			'<textarea name="myTestName" id="myTestId" class="form-control" readonly ></textarea>',
		),

		'Hint' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'hint' => 'Placeholder for textarea.',
			),
			'<textarea name="myTestName" id="myTestId" class="form-control" placeholder="Placeholder for textarea." ></textarea>',
		),

		'Autocomplete' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autocomplete' => false,
			),
			'<textarea name="myTestName" id="myTestId" class="form-control" autocomplete="off" ></textarea>',
		),

		'Autofocus' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			'<textarea name="myTestName" id="myTestId" class="form-control" autofocus ></textarea>',
		),

		'Spellcheck' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'spellcheck' => false,
			),
			'<textarea name="myTestName" id="myTestId" class="form-control" spellcheck="false" ></textarea>',
		),

		'Onchange' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'onchange' => 'foobar();',
			),
			'<textarea name="myTestName" id="myTestId" class="form-control" onchange="foobar();" ></textarea>',
		),

		'Onclick' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'onclick' => 'barfoo();',
			),
			'<textarea name="myTestName" id="myTestId" class="form-control" onclick="barfoo();" ></textarea>',
		),

		'Required' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'required' => true,
			),
			'<textarea name="myTestName" id="myTestId" class="form-control" required aria-required="true" ></textarea>',
		),
	);
}
