<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Data set class for JHtmlFieldEmail.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlFieldEmailTest_DataSet
{
	public static $getInputTest = array(
		'NoValue' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<input type="email" name="myTestName" class="form-control validate-email" id="myTestId" value="">',
		),

		'Value' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'value' => 'foo@bar.com',
			),
			'<input type="email" name="myTestName" class="form-control validate-email" id="myTestId" value="foo@bar.com">',
		),

		'Class' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			'<input type="email" name="myTestName" class="form-control validate-email foo bar" id="myTestId" value="">',
		),

		'Size' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'size' => 60,
			),
			'<input type="email" name="myTestName" class="form-control validate-email" id="myTestId" value="" size="60">',
		),

		'Disabled' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'disabled' => true,
			),
			'<input type="email" name="myTestName" class="form-control validate-email" id="myTestId" value="" disabled>',
		),

		'Readonly' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'readonly' => true,
			),
			'<input type="email" name="myTestName" class="form-control validate-email" id="myTestId" value="" readonly>',
		),

		'Hint' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'hint' => 'Type any email.',
			),
			'<input type="email" name="myTestName" class="form-control validate-email" id="myTestId" value="" placeholder="Type any email.">',
		),

		'Autocomplete' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autocomplete' => false,
			),
			'<input type="email" name="myTestName" class="form-control validate-email" id="myTestId" value="" autocomplete="off">',
		),

		'Autofocus' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			'<input type="email" name="myTestName" class="form-control validate-email" id="myTestId" value="" autofocus>',
		),

		'Spellcheck' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'spellcheck' => false,
			),
			'<input type="email" name="myTestName" class="form-control validate-email" id="myTestId" value="" spellcheck="false">',
		),

		'Onchange' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'onchange' => 'foobar();',
			),
			'<input type="email" name="myTestName" class="form-control validate-email" id="myTestId" value="" onchange="foobar();">',
		),

		'Maxlength' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'maxlength' => 250,
			),
			'<input type="email" name="myTestName" class="form-control validate-email" id="myTestId" value="" maxlength="250">',
		),

		'Required' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'required' => true,
			),
			'<input type="email" name="myTestName" class="form-control validate-email required" id="myTestId" value="" required aria-required="true">',
		),

	);
}
