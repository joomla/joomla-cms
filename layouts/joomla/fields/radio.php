<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', false, true);

$field = $displayData['field'];
$html  = array();

// Initialize some field attributes.
$class     = strlen($field->class) != 0 ? ' class="radio ' . $field->class . '"' : ' class="radio"';
$required  = $field->required ? ' required aria-required="true"' : '';
$autofocus = $field->autofocus ? ' autofocus' : '';
$disabled  = $field->disabled ? ' disabled' : '';
$readonly  = $field->readonly;

// Start the radio field output.
$html[] = '<fieldset id="' . $field->id . '"' . $class . $required . $autofocus . $disabled . ' >';

// Get the field options.
$options = $displayData['options'];

// Build the radio field output.
foreach ($options as $i => $option)
{
	// Initialize some option attributes.
	$checked = ((string) $option->value == (string) $field->value) ? ' checked="checked"' : '';
	$class = !empty($option->class) ? ' class="' . $option->class . '"' : '';

	$disabled = !empty($option->disable) || ($readonly && !$checked);

	$disabled = $disabled ? ' disabled' : '';

	// Initialize some JavaScript option attributes.
	$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
	$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

	$html[] = '<input type="radio" id="' . $field->id . $i . '" name="' . $field->name . '" value="'
			. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $required . $onclick
			. $onchange . $disabled . ' />';

	$html[] = '<label for="' . $field->id . $i . '"' . $class . ' >'
			. JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $field->fieldname)) . '</label>';

	$required = '';
}

// End the radio field output.
$html[] = '</fieldset>';

echo implode($html);