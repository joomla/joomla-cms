<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', false, true);

$field  = $displayData['field'];
$html   = array();

// Initialize some field attributes.
$class          = strlen($field->class) != 0 ? ' class=checkboxes "' . $field->class . '"' : ' class="checkboxes"';
$checkedOptions = explode(',', (string) $field->checkedOptions);
$required       = $field->required ? ' required aria-required="true"' : '';
$autofocus      = $field->autofocus ? ' autofocus' : '';
$fieldValue		= $field->value;

// Start the checkbox field output.
$html[] = '<fieldset id="' . $field->id . '"' . $class . $required . $autofocus . '>';

// Get the field options.
$options = $displayData['options'];

// Build the checkbox field output.
$html[] = '<ul>';

foreach ($options as $i => $option)
{
	// Initialize some option attributes.
	if (empty($fieldValue))
	{
		$checked = (in_array((string) $option->value, (array) $checkedOptions) ? ' checked' : '');
	}
	else
	{
		$value = !is_array($fieldValue) ? explode(',', $fieldValue) : $fieldValue;
		$checked = (in_array((string) $option->value, $value) ? ' checked' : '');
	}

	$checked = strlen($checked) == 0 && $option->checked ? ' checked' : $checked;

	$class = !empty($option->class) ? ' class="' . $class . '"' : '';
	$disabled = !empty($option->disable) || $field->disabled ? ' disabled' : '';

	// Initialize some JavaScript option attributes.
	$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
	$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

	$html[] = '<li>';
	$html[] = '<input type="checkbox" id="' . $field->id . $i . '" name="' . $field->name . '" value="'
		. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $onchange . $disabled . '/>';

	$html[] = '<label for="' . $field->id . $i . '"' . $class . '>' . JText::_($option->text) . '</label>';
	$html[] = '</li>';
}

$html[] = '</ul>';

// End the checkbox field output.
$html[] = '</fieldset>';

echo implode($html);
