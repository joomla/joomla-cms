<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/TestHelpers/JHtmlSelect-helper-dataset.php';

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
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function getGenericlistData()
	{
		return JHtmlSelectTest_DataSet::$genericTest;
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function getRadiolistData()
	{
		return JHtmlSelectTest_DataSet::$radioTest;
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function getOptionsData()
	{
		return JHtmlSelectTest_DataSet::$optionsTest;
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getOptionData()
	{
		return JHtmlSelectTest_DataSet::$optionTest;
	}

	/**
	 * Test...
	 *
	 * @todo Implement testBooleanlist().
	 *
	 * @return void
	 */
	public function testBooleanlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
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
				JHtml::_('select.genericlist', $data, $name, $attribs)
			);
		}
		else
		{
			$this->assertEquals(
				$expected,
				JHtml::_('select.genericlist', $data, $name, $attribs, $optKey, $optText, $selected, $idtag, $translate)
			);
		}
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGroupedlist().
	 *
	 * @return void
	 */
	public function testGroupedlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testIntegerlist().
	 *
	 * @return void
	 */
	public function testIntegerlist()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testOptgroup().
	 *
	 * @return void
	 */
	public function testOptgroup()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
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
			JHtml::_('select.option', $value, $text, $optKey, $optText, $disable)
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
			JHtml::_('select.options', $arr, $optKey, $optText, $selected, $translate)
		);

		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been completely implemented yet.'
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
				JHtml::_('select.radiolist', (object) $data, $name, $attribs)
			);
		}
		else
		{
			$this->assertEquals(
				$expected,
				JHtml::_('select.radiolist', (object) $data, $name, $attribs, $optKey, $optText, $selected, $idtag, $translate)
			);
		}
	}
}
