<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Data set class for JHtmlSelect.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlSelectTest_DataSet
{
	static public $genericTest = array(
		// @todo remove: array($expected, $data, $name, $attribs = null, $optKey = 'value', $optText = 'text',
		// 						$selected = null, $idtag = false, $translate = false)
		array(
			"<select id=\"myName\" name=\"myName\">\n\t<option value=\"1\">Foo</option>\n\t<option value=\"2\">Bar</option>\n</select>\n",
			array(
				array(
					'value' => '1',
					'text' => 'Foo',
				),
				array(
					'value' => '2',
					'text' => 'Bar',
				),
			),
			'myName',
		),
		array(
			"<select id=\"myId\" name=\"myName\">\n\t<option value=\"1\">Foo</option>\n\t<option value=\"2\" selected=\"selected\">Bar</option>\n</select>\n",
			array(
				array(
					'value' => '1',
					'text' => 'Foo',
				),
				array(
					'value' => '2',
					'text' => 'Bar',
				),
			),
			'myName',
			null,
			'value',
			'text',
			'2',
			'myId',
		),
		array(
			"<select id=\"myId\" name=\"myName\">\n\t<option value=\"1\">Foo</option>\n\t<option value=\"2\" selected=\"selected\">Bar</option>\n</select>\n",
			array(
				array(
					'value' => '1',
					'text' => 'Foo',
				),
				array(
					'value' => '2',
					'text' => 'Bar',
				),
			),
			'myName',
			array(
				'id' => 'myId',
				'list.select' => '2',
			),
		),
	);

	static public $optionsTest = array(
		// @todo remove: array($expected, $arr, $optKey = 'value', $optText = 'text', $selected = null, $translate = false)
		array(
			"<option value=\"1\">&nbsp;Test</option>\n",
			array(
				array(
					'value' => '1',
					'text' => '&nbsp;Test',
				),
			),
		),
		array(
			"<option value=\"1\" disabled=\"disabled\">&nbsp;Test</option>\n",
			array(
				array(
					'value' => '1',
					'text' => '&nbsp;Test',
					'disable' => true,
				),
			),
		),
		array(
			"<option value=\"1\">&nbsp;Test</option>\n",
			array(
				array(
					'optionValue' => '1',
					'optionText' => '&nbsp;Test',
				),
			),
			array(
				'option.key' => 'optionValue',
				'option.text' => 'optionText'
			),
		),
		array(
			"<option value=\"1\" id=\"myId\" label=\"My Label\" readonly>&nbsp;Test</option>\n",
			array(
				array(
					'value' => '1',
					'text' => '&nbsp;Test -         ',
					'label' => 'My Label',
					'id' => 'myId',
					'extraAttrib' => 'readonly',
				),
			),
			array(
				'option.label' => 'label',
				'option.id' => 'id',
				'option.attr' => 'extraAttrib',
			),
		),
		array(
			"<option value=\"1\" class=\"foo bar\" style=\"color:red;\">&nbsp;Test</option>\n",
			array(
				array(
					'value' => '1',
					'text' => '&nbsp;Test -         ',
					'label' => 'My Label',
					'id' => 'myId',
					'attrs' => array('class' => "foo bar",'style' => 'color:red;',),
				),
			),
			array(
				'option.attr' => 'attrs',
			),
		),
	);

	static public $optionTest = array(
		// @todo remove: array($expected, $value, $text = '', $optKey = 'value', $optText = 'text', $disable = false)
		array(
			array(
				'value' => 'optionValue',
				'text' => 'optionText',
				'disable' => false,
			),
			'optionValue',
			'optionText'
		),
		array(
			array(
				'fookey' => 'optionValue',
				'bartext' => 'optionText',
				'disable' => false,
			),
			'optionValue',
			'optionText',
			'fookey',
			'bartext',
		),
		array(
			array(
				'value' => 'optionValue',
				'text' => 'optionText',
				'disable' => true,
			),
			'optionValue',
			'optionText',
			'value',
			'text',
			true,
		),
		array(
			array(
				'optionValue' => 'optionValue',
				'optionText' => 'optionText',
				'foobarDisabled' => false,
				'lebal' => 'My Label',
				'class' => 'foo bar',
			),
			'optionValue',
			'optionText',
			array(
				'option.disable' => 'foobarDisabled',
				'option.attr' => 'class',
				'attr' => 'foo bar',
				'option.label' => 'lebal',
				'label' => "My Label",
				'option.key' => 'optionValue',
				'option.text' => 'optionText',
			),
		),
	);
}
