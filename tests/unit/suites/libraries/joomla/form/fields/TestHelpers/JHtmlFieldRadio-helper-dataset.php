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
			// Matcher
			array(
				'id' => 'myTestId',
				'attributes' => array('class' => 'radio')
			),
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
			// Matchers
			array(
				'id' => 'myTestId0',
				'attributes' => array(
					'name' => 'myTestName',
					'value' => "1"
				),
				'ancestor' => array('id' => 'myTestId')
			),
			array(
				'id' => 'myTestId1',
				'attributes' => array(
					'name' => 'myTestName',
					'value' => "0"
				),
				'ancestor' => array('id' => 'myTestId')
			),
		),

		'FieldClass' => array(
			'<field name="myTestId" class="foo bar" type="radio"></field>',
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'class' => 'foo bar',
			),
			// Matcher
			array(
				'id' => 'myTestId',
				'attributes' => array(
					'class' => 'foo bar radio'
				)
			),
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
			// Matchers
			array(
				'id' => 'myTestId0',
				'attributes' => array(
					'class' => 'foo',
					'name' => 'myTestName',
					'value' => "1"
				),
				'ancestor' => array('id' => 'myTestId')
			),
			array(
				'id' => 'myTestId1',
				'attributes' => array(
					'class' => 'bar',
					'name' => 'myTestName',
					'value' => "0"
				),
				'ancestor' => array('id' => 'myTestId')
			),
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
			// Matchers
			array(
				'id' => 'myTestId0',
				'attributes' => array(
					'name' => 'myTestName',
					'disabled' => true
				),
				'ancestor' => array(
					'id' => 'myTestId',
					'attributes' => array('disabled' => true)
				)
			),
			array(
				'id' => 'myTestId1',
				'attributes' => array(
					'name' => 'myTestName',
					'disabled' => true
				),
				'ancestor' => array(
					'id' => 'myTestId',
					'attributes' => array('disabled' => true)
				)
			),
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
			// Matchers
			array(
				'id' => 'myTestId0',
				'attributes' => array(
					'name' => 'myTestName',
					'disabled' => true
				),
				'ancestor' => array(
					'id' => 'myTestId',
					'attributes' => array('disabled' => false)
				)
			),
			array(
				'id' => 'myTestId1',
				'attributes' => array(
					'name' => 'myTestName',
					'disabled' => false
				),
				'ancestor' => array(
					'id' => 'myTestId',
					'attributes' => array('disabled' => false)
				)
			),
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
			// Matchers
			array(
				'id' => 'myTestId0',
				'attributes' => array(
					'name' => 'myTestName',
					'checked' => false,
					'value' => 1
				),
				'ancestor' => array(
					'id' => 'myTestId',
				)
			),
			array(
				'id' => 'myTestId1',
				'attributes' => array(
					'name' => 'myTestName',
					'checked' => true,
					'value' => 0
				),
				'ancestor' => array(
					'id' => 'myTestId',
				)
			),
			array(
				'id' => 'myTestId2',
				'attributes' => array(
					'name' => 'myTestName',
					'checked' => false,
					'value' => -1
				),
				'ancestor' => array(
					'id' => 'myTestId',
				)
			),
		),

		'Autofocus' => array(
			'<field name="myTestId" type="radio" required="true"></field>',
			array(
				'id' => 'myTestId',
				'name' => 'myTestName',
				'autofocus' => true,
			),
			// Matcher
			array(
				'id' => 'myTestId',
				'attributes' => array(
					'autofocus' => null
				)
			),
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
			// Matchers
			array(
				'id' => 'myTestId0',
				'attributes' => array(
					'name' => 'myTestName',
					'onclick' => 'foo();',
					'onchange' => false
				),
				'ancestor' => array(
					'id' => 'myTestId',
				)
			),
			array(
				'id' => 'myTestId1',
				'attributes' => array(
					'name' => 'myTestName',
					'onclick' => false,
					'onchange' => 'bar();'
				),
				'ancestor' => array(
					'id' => 'myTestId',
				)
			),
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
			// Matchers
			array(
				'id' => 'myTestId0',
				'attributes' => array(
					'name' => 'myTestName',
					'aria-required' => 'true',
					'required' => null
				),
				'ancestor' => array(
					'id' => 'myTestId',
					'attributes' => array(
						'aria-required' => 'true',
						'required' => null
					),
				)
			),
			array(
				'id' => 'myTestId1',
				'attributes' => array(
					'name' => 'myTestName',
					'aria-required' => 'true',
					'required' => null
				),
				'ancestor' => array(
					'id' => 'myTestId',
					'attributes' => array(
						'aria-required' => 'true',
						'required' => null
					),
				)
			),
		),
	);
}
