<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  HTML
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for creating HTML select lists
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
abstract class JHtmlSelect
{
	/**
	 * Default values for options. Organized by option group.
	 *
	 * @var array
	 */
	static protected $_optionDefaults = array(
		'option' => array(
			'option.attr' => null,
			'option.disable' => 'disable',
			'option.id' => null,
			'option.key' => 'value',
			'option.key.toHtml' => true,
			'option.label' => null,
			'option.label.toHtml' => true,
			'option.text' => 'text',
			'option.text.toHtml' => true,
		),
	);

	/**
	* Generates a yes/no radio list.
	*
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @return string HTML for the radio list
	*/
	public static function booleanlist(
		$name, $attribs = null, $selected = null, $yes = 'JYES', $no = 'JNO', $id = false
	) {
		$arr = array(
			JHtml::_('select.option', '0', JText::_($no)),
			JHtml::_('select.option', '1', JText::_($yes))
		);
		return JHtml::_('select.radiolist', $arr, $name, $attribs, 'value', 'text', (int) $selected, $id);
	}

	/**
	 * Generates an HTML selection list.
	 *
	 * @param	array	An array of objects, arrays, or scalars.
	 * @param	string  The value of the HTML name attribute.
	 * @param	mixed	Additional HTML attributes for the <select> tag. This
	 * can be an array of attributes, or an array of options. Treated as options
	 * if it is the last argument passed. Valid options are:
	 * <ul><li>Format options, see {@see JHtml::$formatOptions}.
	 * </li><li>Selection options, see {@see JHtmlSelect::options()}.
	 * </li><li>list.attr, string|array: Additional attributes for the select
	 * element.
	 * </li><li>id, string: Value to use as the select element id attribute.
	 * Defaults to the same as the name.
	 * </li><li>list.select, string|array: Identifies one or more option elements
	 * to be selected, bassed on the option key values.
	 * </li></ul>
	 * @param	string  The name of the object variable for the option value. If
	 * set to null, the index of the value array is used.
	 * @param	string  The name of the object variable for the option text.
	 * @param	mixed	The key that is selected (accepts an array or a string).
	 * @return  string  HTML for the select list.
	 */
	public static function genericlist(
		$data, $name, $attribs = null, $optKey = 'value', $optText = 'text',
		$selected = null, $idtag = false, $translate = false
	) {
		// Set default options
		$options = array_merge(
			JHtml::$formatOptions,
			array(
				'format.depth' => 0,
				'id' => false,
			)
		);
		if (is_array($attribs) && func_num_args() == 3) {
			// Assume we have an options array
			$options = array_merge($options, $attribs);
		} else {
			// Get options from the parameters
			$options['id'] = $idtag;
			$options['list.attr'] = $attribs;
			$options['list.translate'] = $translate;
			$options['option.key'] = $optKey;
			$options['option.text'] = $optText;
			$options['list.select'] = $selected;
		}
		$attribs = '';
		if (isset($options['list.attr'])) {
			if (is_array($options['list.attr'])) {
				$attribs = JArrayHelper::toString($options['list.attr']);
			} else {
				$attribs = $options['list.attr'];
			}
			if ($attribs != '') {
				$attribs = ' ' . $attribs;
			}
		}

		$id = $options['id'] !== false ? $options['id'] : $name;
		$id = str_replace(array('[', ']'), '', $id);

		$baseIndent = str_repeat($options['format.indent'], $options['format.depth']++);
		$html = $baseIndent . '<select' . ($id !== '' ? ' id="' . $id . '"' : '')
			. ' name="' . $name . '"'
			. $attribs . '>'
			. $options['format.eol']
			. self::options($data, $options)
			. $baseIndent . '</select>'
			. $options['format.eol']
		;
		return $html;
	}

	/**
	 * Generates a grouped HTML selection list from nested arrays.
	 *
	 * @param	array	An array of groups, each of which is an array of
	 * options.
	 * @param	string  The value of the HTML name attribute
	 * @param	array	Options, an array of key/value pairs. Valid options are:
	 * <ul><li>Format options, {@see JHtml::$formatOptions}.
	 * </li><li>Selection options. See {@see JTtmlSelect::options()}.
	 * </li><li>group.id: The property in each group to use as the group id
	 * attribute. Defaults to none.
	 * </li><li>group.label: The property in each group to use as the group
	 * label. Defaults to "text". If set to null, the data array index key is
	 * used.
	 * </li><li>group.items: The property in each group to use as the array of
	 * items in the group. Defaults to "items". If set to null, group.id and
	 * group. label are forced to null and the data element is assumed to be a
	 * list of selections.
	 * </li><li>id: Value to use as the select element id attribute. Defaults to
	 * the same as the name.
	 * </li><li>list.attr: Attributes for the select element. Can be a string or
	 * an array of key/value pairs. Defaults to none.
	 * </li><li>list.select: either the value of one selected option or an array
	 * of selected options. Default: none.
	 * </li><li>list.translate: Boolean. If set, text and labels are translated via
	 * JText::_().
	 * </li></ul>
	 * @throws JException If a group has unprocessable contents.
	 * @return string  HTML for the select list
	 */
	public static function groupedlist($data, $name, $options = array())
	{
		// Set default options and overwrite with anything passed in
		$options = array_merge(
			JHtml::$formatOptions,
			array(
				'format.depth' => 0,
				'group.items' => 'items',
				'group.label' => 'text',
				'group.label.toHtml' => true,
				'id' => false,
			),
			$options
		);
		// Apply option rules
		if ($options['group.items'] === null)
		{
			$options['group.label'] = null;
		}
		$attribs = '';
		if (isset($options['list.attr']))
		{
			if (is_array($options['list.attr']))
			{
				$attribs = JArrayHelper::toString($options['list.attr']);
			}
			else
			{
				$attribs = $options['list.attr'];
			}
			if ($attribs != '')
			{
				$attribs = ' ' . $attribs;
			}
		}

		$id = $options['id'] !== false ? $options['id'] : $name;
		$id = str_replace(array('[', ']'), '', $id);

		// Disable groups in the options.
		$options['groups'] = false;

		$baseIndent = str_repeat($options['format.indent'], $options['format.depth']++);
		$html = $baseIndent . '<select' . ($id !== '' ? ' id="' . $id . '"' : '')
			. ' name="' . $name . '"'
			. $attribs . '>'
			. $options['format.eol']
		;
		$groupIndent = str_repeat($options['format.indent'], $options['format.depth']++);
		foreach($data as $dataKey => $group)
		{
			$label = $dataKey;
			$id = '';
			$noGroup = is_int($dataKey);
			if ($options['group.items'] == null)
			{
				// Sub-list is an associative array
				$subList = $group;
			}
			elseif (is_array($group))
			{
				// Sub-list is in an element of an array.
				$subList = $group[$options['group.items']];
				if (isset($group[$options['group.label']]))
				{
					$label = $group[$options['group.label']];
					$noGroup = false;
				}
				if (isset($options['group.id']) && isset($group[$options['group.id']]))
				{
					$id = $group[$options['group.id']];
					$noGroup = false;
				}
			}
			elseif (is_object($group))
			{
				// Sub-list is in a property of an object
				$subList = $group->$options['group.items'];
				if (isset($group->$options['group.label']))
				{
					$label = $group->$options['group.label'];
					$noGroup = false;
				}
				if (isset($options['group.id']) && isset($group->$options['group.id']))
				{
					$id = $group->$options['group.id'];
					$noGroup = false;
				}
			}
			else
			{
				throw new JException('Invalid group contents.', 1, E_WARNING);
			}
			if($noGroup)
			{
				$html.=self::options($subList, $options);
			}
			else
			{
				$html .= $groupIndent
					. '<optgroup' . (empty($id) ? '' : ' id="' . $id . '"')
					. ' label="'
					. ($options['group.label.toHtml'] ? htmlspecialchars($label, ENT_COMPAT, 'UTF-8') : $label)
					. '">'
					. $options['format.eol']
					. self::options($subList, $options)
					. $groupIndent . '</optgroup>'
					. $options['format.eol']
				;
			}
		}
		$html .= $baseIndent . '</select>' . $options['format.eol'];

		return $html;
	}

	/**
	* Generates a selection list of integers.
	*
	* @param int The start integer
	* @param int The end integer
	* @param int The increment
	* @param string The value of the HTML name attribute
	* @param mixed Additional HTML attributes for the <select> tag, an array of
	* attributes, or an array of options. Treated as options if it is the last
	* argument passed.
	* @param mixed The key that is selected
	* @param string The printf format to be applied to the number
	* @return string HTML for the select list
	*/
	public static function integerlist(
		$start, $end, $inc, $name, $attribs = null, $selected = null, $format = ''
	) {
		// Set default options
		$options = array_merge(
			JHtml::$formatOptions,
			array(
				'format.depth' => 0,
				'option.format' => '',
				'id' => null,
			)
		);
		if (is_array($attribs) && func_num_args() == 5) {
			// Assume we have an options array
			$options = array_merge($options, $attribs);
			// Extract the format and remove it from downstream options
			$format = $options['option.format'];
			unset($options['option.format']);
		} else {
			// Get options from the parameters
			$options['list.attr'] = $attribs;
			$options['list.select'] = $selected;
		}
		$start = intval($start);
		$end = intval($end);
		$inc = intval($inc);

		$data = array();
		for ($i = $start; $i <= $end; $i += $inc)
		{
			$data[$i] = $format ? sprintf($format, $i) : $i;
		}

		// Tell genericlist() to use array keys
		$options['option.key'] = null;

		return JHtml::_('select.genericlist', $data, $name, $options);
	}

	/**
	 * Create a placeholder for an option group.
	 *
	 * @deprecated From version 1.6. See {@see JHtmlSelect::groupedList()}.
	 * @param	string  The text for the option
	 * @param	string  The returned object property name for the value
	 * @param	string  The returned object property name for the text
	 * @return  object
	 */
	public static function optgroup($text, $optKey = 'value', $optText = 'text')
	{
		// Set initial state
		static $state = 'open';

		// Toggle between open and close states:
		switch($state)
		{
			case 'open':
				$obj = new stdClass;
				$obj->$optKey = '<OPTGROUP>';
				$obj->$optText = $text;
				$state = 'close';
				break;
			case 'close':
				$obj = new stdClass;
				$obj->$optKey = '</OPTGROUP>';
				$obj->$optText = $text;
				$state = 'open';
				break;
		}

		return $obj;
	}

	/**
	 * Create an object that represents an option in an option list.
	 *
	 * @param	string  The value of the option
	 * @param	string  The text for the option
	 * @param	string|array If a string, the returned object property name for
	 * the value. If an array, options. Valid options are:
	 * <ul><li>attr: String|array. Additional attributes for this option.
	 * Defaults to none.
	 * </li><li>disable: Boolean. If set, this option is disabled.
	 * </li><li>label: String. The value for the option label.
	 * </li><li>option.attr: The property in each option array to use for
	 * additional selection attributes. Defaults to none.
	 * </li><li>option.disable: The property that will hold the disabled state.
	 * Defaults to "disable".
	 * </li><li>option.key: The property that will hold the selection value.
	 * Defaults to "value".
	 * </li><li>option.label: The property in each option array to use as the
	 * selection label attribute. If a "label" option is provided, defaults to
	 * "label", if no label is given, defaults to null (none).
	 * </li><li>option.text: The property that will hold the the displayed text.
	 * Defaults to "text". If set to null, the option array is assumed to be a
	 * list of displayable scalars.
	 * </ul>
	 * @param	string  The property that will hold the the displayed text. This
	 * parameter is ignored if an options array is passed.
	 * @return  object
	 */
	public static function option(
		$value, $text = '', $optKey = 'value', $optText = 'text', $disable = false
	) {
		$options = array(
			'attr' => null,
			'disable' => false,
			'option.attr' => null,
			'option.disable' => 'disable',
			'option.key' => 'value',
			'option.label' => null,
			'option.text' => 'text',
		);
		if (is_array($optKey)) {
			// Merge in caller's options
			$options = array_merge($options, $optKey);
		} else {
			// Get options from the parameters
			$options['option.key'] = $optKey;
			$options['option.text'] = $optText;
			$options['disable'] = $disable;
		}
		$obj = new JObject;
		$obj->$options['option.key'] = $value;
		$obj->$options['option.text'] = trim($text) ? $text : $value;

		/*
		 * If a label is provided, save it. If no label is provided and there is
		 * a label name, initialise to an empty string.
		 */
		$hasProperty = $options['option.label'] !== null;
		if (isset($options['label'])) {
			$labelProperty = $hasProperty ? $options['option.label'] : 'label';
			$obj->$labelProperty = $options['label'];
		} elseif ($hasProperty) {
			$obj->$options['option.label'] = '';
		}

		// Set attributes only if there is a property and a value
		if ($options['attr'] !== null) {
			$obj->$options['option.attr'] = $options['attr'];
		}

		// Set disable only if it has a property and a value
		if ($options['disable'] !== null) {
			$obj->$options['option.disable'] = $options['disable'];
		}
		return $obj;
	}

	/**
	 * Generates the option tags for an HTML select list (with no select tag
	 * surrounding the options).
	 *
	 * @param	array	An array of objects, arrays, or values.
	 * @param	mixed	If a string, this is the name of the object variable for
	 * the option value. If null, the index of the array of objects is used. If
	 * an array, this is a set of options, as key/value pairs. Valid options
	 * are:
	 * <ul><li>Format options, {@see JHtml::$formatOptions}.
	 * </li><li>groups: Boolean. If set, looks for keys with the value
	 * "&lt;optgroup>" and synthesizes groups from them. Deprecated. Defaults
	 * true for backwards compatibility.
	 * </li><li>list.select: either the value of one selected option or an array
	 * of selected options. Default: none.
	 * </li><li>list.translate: Boolean. If set, text and labels are translated via
	 * JText::_(). Default is false.
	 * </li><li>option.id: The property in each option array to use as the
	 * selection id attribute. Defaults to none.
	 * </li><li>option.key: The property in each option array to use as the
	 * selection value. Defaults to "value". If set to null, the index of the
	 * option array is used.
	 * </li><li>option.label: The property in each option array to use as the
	 * selection label attribute. Defaults to null (none).
	 * </li><li>option.text: The property in each option array to use as the
	 * displayed text. Defaults to "text". If set to null, the option array is
	 * assumed to be a list of displayable scalars.
	 * </li><li>option.attr: The property in each option array to use for
	 * additional selection attributes. Defaults to none.
	 * </li><li>option.disable: The property that will hold the disabled state.
	 * Defaults to "disable".
	 * </li><li>option.key: The property that will hold the selection value.
	 * Defaults to "value".
	 * </li><li>option.text: The property that will hold the the displayed text.
	 * Defaults to "text". If set to null, the option array is assumed to be a
	 * list of displayable scalars.
	 * </li></ul>
	 * @param	string  The name of the object variable for the option text.
	 * @param	mixed	The key that is selected (accepts an array or a string)
	 * @return  string  HTML for the select list
	 */
	public static function options(
		$arr, $optKey = 'value', $optText = 'text', $selected = null, $translate = false
	) {
		$options = array_merge(
			JHtml::$formatOptions,
			self::$_optionDefaults['option'],
			array(
				'format.depth' => 0,
				'groups' => true,
				'list.select' => null,
				'list.translate' => false,
			)
		);
		if (is_array($optKey)) {
			// Set default options and overwrite with anything passed in
			$options = array_merge($options, $optKey);
		} else {
			// Get options from the parameters
			$options['option.key'] = $optKey;
			$options['option.text'] = $optText;
			$options['list.select'] = $selected;
			$options['list.translate'] = $translate;
		}

		$html = '';
		$baseIndent = str_repeat($options['format.indent'], $options['format.depth']);

		foreach ($arr as $elementKey => &$element)
		{
			$attr = '';
			$extra = '';
			$label = '';
			$id = '';
			if (is_array($element))
			{
				$key = $options['option.key'] === null
					? $elementKey : $element[$options['option.key']];
				$text = $element[$options['option.text']];
				if (isset($element[$options['option.attr']])) {
					$attr = $element[$options['option.attr']];
				}
				if (isset($element[$options['option.id']])) {
					$id = $element[$options['option.id']];
				}
				if (isset($element[$options['option.label']])) {
					$label = $element[$options['option.label']];
				}
				if (isset($element[$options['option.disable']]) && $element[$options['option.disable']]) {
					$extra .= ' disabled="disabled"';
				}
			} elseif (is_object($element)) {
				$key = $options['option.key'] === null
					? $elementKey : $element->$options['option.key'];
				$text = $element->$options['option.text'];
				if (isset($element->$options['option.attr'])) {
					$attr = $element->$options['option.attr'];
				}
				if (isset($element->$options['option.id'])) {
					$id = $element->$options['option.id'];
				}
				if (isset($element->$options['option.label'])) {
					$label = $element->$options['option.label'];
				}
				if (isset($element->$options['option.disable']) && $element->$options['option.disable']) {
					$extra .= ' disabled="disabled"';
				}
			} else {
				// This is a simple associative array
				$key = $elementKey;
				$text = $element;
			}

			/*
			 * The use of options that contain optgroup HTML elements was
			 * somewhat hacked for J1.5. J1.6 introduces the grouplist() method
			 * to handle this better. The old solution is retained through the
			 * "groups" option, which defaults true in J1.6, but should be
			 * deprecated at some point in the future.
			 */
			$key = (string) $key;
			if ($options['groups'] && $key == '<OPTGROUP>') {
				$html .= $baseIndent . '<optgroup label="'
					. ($options['list.translate'] ? JText::_($text) : $text)
					. '">' . $options['format.eol'];
				$baseIndent = str_repeat($options['format.indent'], ++$options['format.depth']);
			} else if ($options['groups'] && $key == '</OPTGROUP>') {
				$baseIndent = str_repeat($options['format.indent'], --$options['format.depth']);
				$html .= $baseIndent . '</optgroup>' . $options['format.eol'];
			} else {
				// if no string after hypen - take hypen out
				$splitText = explode(' - ', $text, 2);
				$text = $splitText[0];
				if (isset($splitText[1])) {
					$text .= ' - ' . $splitText[1];
				}

				if ($options['list.translate'] && !empty($label)) {
					$label = JText::_($label);
				}
				if ($options['option.label.toHtml']) {
					$label = htmlentities($label);
				}
				if (is_array($attr)) {
					$attr = JArrayHelper::toString($attr);
				} else {
					$attr = trim($attr);
				}
				$extra = ($id ? ' id="' . $id . '"' : '')
					. ($label ? ' label="' . $label . '"' : '')
					. ($attr ? ' ' . $attr : '')
					. $extra
				;
				if (is_array($options['list.select']))
				{
					foreach ($options['list.select'] as $val)
					{
						$key2 = is_object($val) ? $val->$options['option.key'] : $val;
						if ($key == $key2) {
							$extra .= ' selected="selected"';
							break;
						}
					}
				} elseif ((string)$key == (string)$options['list.select']) {
					$extra .= ' selected="selected"';
				}

				if ($options['list.translate']) {
					$text = JText::_($text);
				}

				// Generate the option, encoding as required
				$html .= $baseIndent . '<option value="'
					. ($options['option.key.toHtml'] ? htmlspecialchars($key, ENT_COMPAT, 'UTF-8') : $key) . '"'
					. $extra . '>'
					. ($options['option.text.toHtml'] ? htmlentities(html_entity_decode($text), ENT_COMPAT, 'UTF-8') : $text)
					. '</option>'
					. $options['format.eol']
				;
			}
		}

		return $html;
	}

	/**
	* Generates an HTML radio list.
	*
	* @param array An array of objects
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @param string The name of the object variable for the option value
	* @param string The name of the object variable for the option text
	* @return string HTML for the select list
	*/
	public static function radiolist(
		$data, $name, $attribs = null, $optKey = 'value', $optText = 'text',
		$selected = null, $idtag = false, $translate = false
	) {
		reset($data);
		$html = '';

		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		$id_text = $idtag ? $idtag : $name;

		foreach ($data as $ind => $obj)
		{
			$k  = $obj->$optKey;
			$t  = $translate ? JText::_($obj->$optText) : $obj->$optText;
			$id = (isset($obj->id) ? $obj->id : null);

			$extra  = '';
			$extra  .= $id ? ' id="' . $obj->id . '"' : '';
			if (is_array($selected))
			{
				foreach ($selected as $val)
				{
					$k2 = is_object($val) ? $val->$optKey : $val;
					if ($k == $k2)
					{
						$extra .= ' selected="selected"';
						break;
					}
				}
			} else {
				$extra .= ((string)$k == (string)$selected ? ' checked="checked"' : '');
			}
			$html .= "\n\t" .'<input type="radio" name="' . $name . '"'
				. ' id="' . $id_text . $k . '" value="' . $k .'"'
				. ' ' . $extra . ' ' . $attribs . '/>'
				. "\n\t" . '<label for="' . $id_text . $k . '" id="' . $id_text . $k . '-lbl" class="radiobtn-'.strtolower($obj->$optText).'">'. $t .'</label>';
		}
		$html .= "\n";
		return $html;
	}

}
