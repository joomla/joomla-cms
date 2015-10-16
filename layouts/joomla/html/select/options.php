<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  array  $options  The tag options
 * @var  array  $arr      The tag array data
 */

extract($displayData);

$baseIndent = str_repeat($options['format.indent'], $options['format.depth']);

foreach ($arr as $elementKey => &$element)
{
	$attr  = '';
	$extra = '';
	$label = '';
	$id    = '';

	if (is_array($element))
	{
		$key  = $options['option.key'] === null ? $elementKey : $element[$options['option.key']];
		$text = $element[$options['option.text']];

		if (isset($element[$options['option.attr']]))
		{
			$attr = $element[$options['option.attr']];
		}

		if (isset($element[$options['option.id']]))
		{
			$id = $element[$options['option.id']];
		}

		if (isset($element[$options['option.label']]))
		{
			$label = $element[$options['option.label']];
		}

		if (isset($element[$options['option.disable']]) && $element[$options['option.disable']])
		{
			$extra .= ' disabled="disabled"';
		}
	}
	elseif (is_object($element))
	{
		$key  = $options['option.key'] === null ? $elementKey : $element->{$options['option.key']};
		$text = $element->{$options['option.text']};

		if (isset($element->{$options['option.attr']}))
		{
			$attr = $element->{$options['option.attr']};
		}

		if (isset($element->{$options['option.id']}))
		{
			$id = $element->{$options['option.id']};
		}

		if (isset($element->{$options['option.label']}))
		{
			$label = $element->{$options['option.label']};
		}

		if (isset($element->{$options['option.disable']}) && $element->{$options['option.disable']})
		{
			$extra .= ' disabled="disabled"';
		}

		if (isset($element->{$options['option.class']}) && $element->{$options['option.class']})
		{
			$extra .= ' class="' . $element->{$options['option.class']} . '"';
		}

		if (isset($element->{$options['option.onclick']}) && $element->{$options['option.onclick']})
		{
			$extra .= ' onclick="' . $element->{$options['option.onclick']} . '"';
		}
	}
	else
	{
		// This is a simple associative array
		$key  = $elementKey;
		$text = $element;
	}

	/*
	 * The use of options that contain optgroup HTML elements is
	 * deprecated. Use instead the grouplist() method
	 * to handle this better. This part shall be removed in 4.0.
	 */

	$key = (string) $key;

	if ($options['groups'] && $key == '<OPTGROUP>')
	{
		echo $baseIndent . '<optgroup label="' . ($options['list.translate'] ? JText::_($text) : $text) . '">' . $options['format.eol'];
		$baseIndent = str_repeat($options['format.indent'], ++$options['format.depth']);
	}
	elseif ($options['groups'] && $key == '</OPTGROUP>')
	{
		$baseIndent = str_repeat($options['format.indent'], --$options['format.depth']);
		echo $baseIndent . '</optgroup>' . $options['format.eol'];
	}
	else
	{
		// If no string after hyphen - take hyphen out
		$splitText = explode(' - ', $text, 2);
		$text = $splitText[0];

		if (isset($splitText[1]) && $splitText[1] != "" && !preg_match('/^[\s]+$/', $splitText[1]))
		{
			$text .= ' - ' . $splitText[1];
		}

		if ($options['list.translate'] && !empty($label))
		{
			$label = JText::_($label);
		}

		if ($options['option.label.toHtml'])
		{
			$label = htmlentities($label);
		}

		if (is_array($attr))
		{
			$attr = Joomla\Utilities\ArrayHelper::toString($attr);
		}
		else
		{
			$attr = trim($attr);
		}

		$extra = ($id ? ' id="' . $id . '"' : '') . ($label ? ' label="' . $label . '"' : '') . ($attr ? ' ' . $attr : '') . $extra;

		if (is_array($options['list.select']))
		{
			foreach ($options['list.select'] as $val)
			{
				$key2 = is_object($val) ? $val->$options['option.key'] : $val;

				if ($key == $key2)
				{
					$extra .= ' selected="selected"';
					break;
				}
			}
		}
		elseif ((string) $key == (string) $options['list.select'])
		{
			$extra .= ' selected="selected"';
		}

		if ($options['list.translate'])
		{
			$text = JText::_($text);
		}

		// Generate the option, encoding as required
		echo $baseIndent . '<option value="' . ($options['option.key.toHtml'] ? htmlspecialchars($key, ENT_COMPAT, 'UTF-8') : $key) . '"'
			. $extra . '>';
		echo $options['option.text.toHtml'] ? htmlentities(html_entity_decode($text, ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8') : $text;
		echo '</option>' . $options['format.eol'];
	}
}
