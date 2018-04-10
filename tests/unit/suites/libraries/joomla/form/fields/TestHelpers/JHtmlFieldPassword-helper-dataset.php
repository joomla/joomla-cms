<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Data set class for JHtmlFieldPassword.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlFieldPasswordTest_DataSet
{
	public static $getInputTest = array(
		'NoValue' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<div class="password-group"><div class="input-group"><input type="password" name="myTestName" id="myTestId" value="" class="form-control"><span class="input-group-addon"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">Show</span></span></div></div>',
		),

		'Value' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'value' => 'foobar',
			),
			'<div class="password-group"><div class="input-group"><input type="password" name="myTestName" id="myTestId" value="foobar" class="form-control"><span class="input-group-addon"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">Show</span></span></div></div>',
		),

		'Class' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			'<div class="password-group"><div class="input-group"><input type="password" name="myTestName" id="myTestId" value="" class="form-control foo bar"><span class="input-group-addon"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">Show</span></span></div></div>',
		),

		'Size' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'size' => 60,
			),
			'<div class="password-group"><div class="input-group"><input type="password" name="myTestName" id="myTestId" value="" class="form-control" size="60"><span class="input-group-addon"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">Show</span></span></div></div>',
		),

		'Disabled' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'disabled' => true,
			),
			'<div class="password-group"><div class="input-group"><input type="password" name="myTestName" id="myTestId" value="" class="form-control" disabled><span class="input-group-addon"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">Show</span></span></div></div>',
		),

		'Readonly' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'readonly' => true,
			),
			'<div class="password-group"><div class="input-group"><input type="password" name="myTestName" id="myTestId" value="" class="form-control" readonly><span class="input-group-addon"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">Show</span></span></div></div>',
		),

		'Hint' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'hint' => 'Type any password.',
			),
			'<div class="password-group"><div class="input-group"><input type="password" name="myTestName" id="myTestId" value="" placeholder="Type any password." class="form-control"><span class="input-group-addon"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">Show</span></span></div></div>',
		),

		'Autocomplete' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autocomplete' => false,
			),
			'<div class="password-group"><div class="input-group"><input type="password" name="myTestName" id="myTestId" value="" autocomplete="off" class="form-control"><span class="input-group-addon"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">Show</span></span></div></div>',
		),

		'Autofocus' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			'<div class="password-group"><div class="input-group"><input type="password" name="myTestName" id="myTestId" value="" class="form-control" autofocus><span class="input-group-addon"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">Show</span></span></div></div>',
		),

		'Maxlength' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'maxLength' => 250,
			),
			'<div class="password-group"><div class="input-group"><input type="password" name="myTestName" id="myTestId" value="" class="form-control" maxlength="250"><span class="input-group-addon"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">Show</span></span></div></div>',
		),

		'Required' => array(
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'required' => true,
			),
			'<div class="password-group"><div class="input-group"><input type="password" name="myTestName" id="myTestId" value="" class="form-control" required aria-required="true"><span class="input-group-addon"><span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">Show</span></span></div></div>',
		),

	);
}
