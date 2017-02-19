<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHtmlSelect.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       3.1
 */
class JHtmlSelectTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Generic list dataset
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function getGenericlistData()
	{
		return array(
			// Function parameters array($expected, $data, $name, $attribs = null, $optKey = 'value', $optText = 'text',
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
	}

	/**
	 * Radio list dataset
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function getRadiolistData()
	{
		return array(
			// Function parameters array($expected, $data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false,
			// 						$translate = false)
			array(
				"<div class=\"controls\">\n\t" .
				"<label for=\"yesId\" id=\"yesId-lbl\" class=\"radio\">\n\t\n\t" .
				"<input type=\"radio\" name=\"myRadioListName\" id=\"yesId\" value=\"1\" >Yes\n\t" .
				"</label>\n\t" .
				"<label for=\"myRadioListName0\" id=\"myRadioListName0-lbl\" class=\"radio\">\n\t\n\t" .
				"<input type=\"radio\" name=\"myRadioListName\" id=\"myRadioListName0\" value=\"0\" >No\n\t" .
				"</label>\n\t" .
				"<label for=\"myRadioListName-1\" id=\"myRadioListName-1-lbl\" class=\"radio\">\n\t\n\t" .
				"<input type=\"radio\" name=\"myRadioListName\" id=\"myRadioListName-1\" value=\"-1\" >Maybe\n\t" .
				"</label>\n" .
				"</div>\n",
				array(
					array(
						'value' => '1',
						'text' => 'Yes',
						'id' => "yesId",
					),
					array(
						'value' => '0',
						'text' => 'No',
					),
					array(
						'value' => '-1',
						'text' => 'Maybe',
					),
				),
				"myRadioListName"
			),
			array(
				"<div class=\"controls\">\n\t" .
				"<label for=\"fooId\" id=\"fooId-lbl\" class=\"radio\">\n\t\n\t" .
				"<input type=\"radio\" name=\"myFooBarListName\" id=\"fooId\" value=\"foo\" class=\"i am radio\" onchange=\"jsfunc();\">FOO\n\t" .
				"</label>\n\t" .
				"<label for=\"myFooBarListNamebar\" id=\"myFooBarListNamebar-lbl\" class=\"radio\">\n\t\n\t" .
				"<input type=\"radio\" name=\"myFooBarListName\" id=\"myFooBarListNamebar\" value=\"bar\" class=\"i am radio\" onchange=\"jsfunc();\">BAR\n\t" .
				"</label>\n" .
				"</div>\n",
				array(
					array(
						'key' => 'foo',
						'val' => 'FOO',
						'id' => "fooId",
					),
					array(
						'key' => 'bar',
						'val' => 'BAR',
					),
				),
				"myFooBarListName",
				array(
					'class' => 'i am radio',
					'onchange' => 'jsfunc();',
				),
				'key',
				'val',
			),
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getOptionsData()
	{
		return array(
			// Function parameters array($expected, $arr, $optKey = 'value', $optText = 'text', $selected = null, $translate = false)
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
				"<option value=\"1\" class=\"foo bar\" style=\"color:red;\">-&nbsp;Test -</option>\n",
				array(
					array(
						'value' => '1',
						'text' => '-&nbsp;Test -',
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
	}

	/**
	 * Options dataset
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getOptionData()
	{
		return array(
			// Function parameters array($expected, $value, $text = '', $optKey = 'value', $optText = 'text', $disable = false)
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

	/**
	 * Test the genericlist method.
	 *
	 * @param   string   $expected   Expected generated HTML <select> string.
	 * @param   array    $data       An array of objects, arrays, or scalars.
	 * @param   string   $name       The value of the HTML name attribute.
	 * @param   mixed    $attribs    Additional HTML attributes for the <select> tag. This
	 *                               can be an array of attributes, or an array of options. Treated as options
	 *                               if it is the last argument passed. Valid options are:
	 *                               Format options, see {@see JHtml::$formatOptions}.
	 *                               Selection options, see {@see JHtmlSelect::options()}.
	 *                               list.attr, string|array: Additional attributes for the select
	 *                               element.
	 *                               id, string: Value to use as the select element id attribute.
	 *                               Defaults to the same as the name.
	 *                               list.select, string|array: Identifies one or more option elements
	 *                               to be selected, based on the option key values.
	 * @param   string   $optKey     The name of the object variable for the option value. If
	 *                               set to null, the index of the value array is used.
	 * @param   string   $optText    The name of the object variable for the option text.
	 * @param   mixed    $selected   The key that is selected (accepts an array or a string).
	 * @param   mixed    $idtag      Value of the field id or null by default
	 * @param   boolean  $translate  True to translate
	 *
	 * @return  void
	 *
	 * @dataProvider  getGenericlistData
	 * @since         3.2
	 */
	public function testGenericlist($expected, $data, $name, $attribs = null, $optKey = 'value', $optText = 'text',
		$selected = null, $idtag = false, $translate = false)
	{
		if (func_num_args() == 4)
		{
			$this->assertEquals(
				$expected,
				JHtmlSelect::genericlist($data, $name, $attribs)
			);
		}
		else
		{
			$this->assertEquals(
				$expected,
				JHtmlSelect::genericlist($data, $name, $attribs, $optKey, $optText, $selected, $idtag, $translate)
			);
		}
	}

	/**
	 * Test the option method.
	 *
	 * @param   object   $expected  Expected Object.
	 * @param   string   $value     The value of the option
	 * @param   string   $text      The text for the option
	 * @param   mixed    $optKey    If a string, the returned object property name for
	 *                              the value. If an array, options. Valid options are:
	 *                              attr: String|array. Additional attributes for this option.
	 *                              Defaults to none.
	 *                              disable: Boolean. If set, this option is disabled.
	 *                              label: String. The value for the option label.
	 *                              option.attr: The property in each option array to use for
	 *                              additional selection attributes. Defaults to none.
	 *                              option.disable: The property that will hold the disabled state.
	 *                              Defaults to "disable".
	 *                              option.key: The property that will hold the selection value.
	 *                              Defaults to "value".
	 *                              option.label: The property in each option array to use as the
	 *                              selection label attribute. If a "label" option is provided, defaults to
	 *                              "label", if no label is given, defaults to null (none).
	 *                              option.text: The property that will hold the the displayed text.
	 *                              Defaults to "text". If set to null, the option array is assumed to be a
	 *                              list of displayable scalars.
	 * @param   string   $optText   The property that will hold the the displayed text. This
	 *                              parameter is ignored if an options array is passed.
	 * @param   boolean  $disable   Not used.
	 *
	 * @return  void
	 *
	 * @dataProvider  getOptionData
	 * @since         3.2
	 */
	public function testOption($expected, $value, $text = '', $optKey = 'value', $optText = 'text', $disable = false)
	{
		$this->assertEquals(
			(object) $expected,
			JHtmlSelect::option($value, $text, $optKey, $optText, $disable)
		);
	}

	/**
	 * Test the options method.
	 *
	 * @param   string   $expected   Expected generated HTML <option> list.
	 * @param   array    $arr        An array of objects, arrays, or values.
	 * @param   mixed    $optKey     If a string, this is the name of the object variable for
	 *                               the option value. If null, the index of the array of objects is used. If
	 *                               an array, this is a set of options, as key/value pairs. Valid options are:
	 *                               -Format options, {@see JHtml::$formatOptions}.
	 *                               -groups: Boolean. If set, looks for keys with the value
	 *                                "&lt;optgroup>" and synthesizes groups from them. Deprecated. Defaults
	 *                                true for backwards compatibility.
	 *                               -list.select: either the value of one selected option or an array
	 *                                of selected options. Default: none.
	 *                               -list.translate: Boolean. If set, text and labels are translated via
	 *                                JText::_(). Default is false.
	 *                               -option.id: The property in each option array to use as the
	 *                                selection id attribute. Defaults to none.
	 *                               -option.key: The property in each option array to use as the
	 *                                selection value. Defaults to "value". If set to null, the index of the
	 *                                option array is used.
	 *                               -option.label: The property in each option array to use as the
	 *                                selection label attribute. Defaults to null (none).
	 *                               -option.text: The property in each option array to use as the
	 *                               displayed text. Defaults to "text". If set to null, the option array is
	 *                               assumed to be a list of displayable scalars.
	 *                               -option.attr: The property in each option array to use for
	 *                                additional selection attributes. Defaults to none.
	 *                               -option.disable: The property that will hold the disabled state.
	 *                                Defaults to "disable".
	 *                               -option.key: The property that will hold the selection value.
	 *                                Defaults to "value".
	 *                               -option.text: The property that will hold the the displayed text.
	 *                               Defaults to "text". If set to null, the option array is assumed to be a
	 *                               list of displayable scalars.
	 * @param   string   $optText    The name of the object variable for the option text.
	 * @param   mixed    $selected   The key that is selected (accepts an array or a string)
	 * @param   boolean  $translate  Translate the option values.
	 *
	 * @return  void
	 *
	 * @dataProvider  getOptionsData
	 * @since         3.1
	 */
	public function testOptions($expected, $arr, $optKey = 'value', $optText = 'text', $selected = null, $translate = false)
	{
		$this->assertEquals(
			$expected,
			JHtmlSelect::options($arr, $optKey, $optText, $selected, $translate)
		);
	}

	/**
	 * Test the radiolist method.
	 *
	 * @param   string   $expected   Expected generated HTML of radio list.
	 * @param   array    $data       An array of objects
	 * @param   string   $name       The value of the HTML name attribute
	 * @param   string   $attribs    Additional HTML attributes for the <select> tag
	 * @param   mixed    $optKey     The key that is selected
	 * @param   string   $optText    The name of the object variable for the option value
	 * @param   string   $selected   The name of the object variable for the option text
	 * @param   boolean  $idtag      Value of the field id or null by default
	 * @param   boolean  $translate  True if options will be translated
	 *
	 * @return  void
	 *
	 * @dataProvider  getRadiolistData
	 * @since         3.2
	 */
	public function testRadiolist($expected, $data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false,
		$translate = false)
	{
		foreach ($data as $arr)
		{
			$dataObject[] = (object) $arr;
		}

		$data = $dataObject;

		if (func_num_args() == 4)
		{
			$this->assertEquals(
				$expected,
				JHtmlSelect::radiolist((object) $data, $name, $attribs)
			);
		}
		else
		{
			$this->assertEquals(
				$expected,
				JHtmlSelect::radiolist((object) $data, $name, $attribs, $optKey, $optText, $selected, $idtag, $translate)
			);
		}
	}
}
