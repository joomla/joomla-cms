<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Data set class for JHtmlFieldRadio.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlFieldRadioTest_DataSet
{
	public static $getInputTest = array(
		'NoOptions' => array(
			'<field name="myTestId" type="radio" />',
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<fieldset id="myTestId" class="radio" ></fieldset>',
		),

		'Options' => array(
			'<field name="myTestId" type="radio">
				<option value="1">Yes</option>
				<option value="0">No</option>
			</field>',
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<fieldset id="myTestId" class="radio" ><label class="radio"><input type="radio" name="myTestName" value="1" />Yes</label><label class="radio"><input type="radio" name="myTestName" value="0" />No</label></fieldset>'
		),

		'FieldClass' => array(
			'<field name="myTestId" class="foo bar" type="radio"></field>',
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			'<fieldset id="myTestId" class="radio foo bar" ></fieldset>',
		),

		'OptionClass' => array(
			'<field name="myTestId" type="radio">
				<option value="1" class="foo">Yes</option>
				<option value="0" class="bar">No</option>
			</field>',
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<fieldset id="myTestId" class="radio" ><label class="radio foo"><input type="radio" name="myTestName" value="1" />Yes</label><label class="radio bar"><input type="radio" name="myTestName" value="0" />No</label></fieldset>',
		),

		'FieldDisabled' => array(
			'<field name="myTestId" type="radio">
				<option value="1">Yes</option>
				<option value="0">No</option>
			</field>',
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'disabled' => true,
			),
			'<fieldset id="myTestId" class="radio" disabled ><label class="radio"><input type="radio" name="myTestName" value="1" />Yes</label><label class="radio"><input type="radio" name="myTestName" value="0" />No</label></fieldset>',
		),

		'OptionDisabled' => array(
			'<field name="myTestId" type="radio">
				<option value="1" disabled="true">Yes</option>
				<option value="0">No</option>
			</field>',
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<fieldset id="myTestId" class="radio" ><label class="radio"><input type="radio" name="myTestName" value="1" disabled />Yes</label><label class="radio"><input type="radio" name="myTestName" value="0" />No</label></fieldset>',
		),

		'ReadonlyChecked' => array(
			'<field name="myTestId" type="radio" readonly="true" value="0">
				<option value="1">Yes</option>
				<option value="0">No</option>
				<option value="-1">None</option>
			</field>',
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'readonly' => true,
				'value' => '0',
			),
			'<fieldset id="myTestId" class="radio" ><label class="radio"><input type="radio" name="myTestName" value="1" disabled />Yes</label><label class="radio"><input type="radio" name="myTestName" value="0" checked="checked" />No</label><label class="radio"><input type="radio" name="myTestName" value="-1" disabled />None</label></fieldset>',
		),

		'Autofocus' => array(
			'<field name="myTestId" type="radio" required="true"></field>',
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			'<fieldset id="myTestId" class="radio" autofocus ></fieldset>',
		),

		'OnclickOnchange' => array(
			'<field name="myTestId" type="radio">
				<option value="1" onclick="foo();" >Yes</option>
				<option value="0" onchange="bar();">No</option>
			</field>',
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
			),
			'<fieldset id="myTestId" class="radio" ><label class="radio"><input type="radio" name="myTestName" value="1" onclick="foo();" />Yes</label><label class="radio"><input type="radio" name="myTestName" value="0" onchange="bar();" />No</label></fieldset>',
		),

		'Required' => array(
			'<field name="myTestId" type="radio" required="true">
				<option value="1" required="true" >Yes</option>
				<option value="0">No</option>
			</field>',
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'required' => true,
			),
			'<fieldset id="myTestId" class="radio" required aria-required="true" ><label class="radio"><input type="radio" name="myTestName" value="1" required aria-required="true" />Yes</label><label class="radio"><input type="radio" name="myTestName" value="0" />No</label></fieldset>',
		),
	);
}
