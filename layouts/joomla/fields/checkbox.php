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

$field = $displayData['field'];

// Initialize some field attributes.
$class     = strlen($field->class) != 0 ? ' class="' . $field->class . '"' : '';
$disabled  = $field->disabled ? ' disabled' : '';
$value     = strlen($field->default) != 0 ? $field->default : '1';
$value     = strlen($field->value) != 0 ? $field->value : $value;
$required  = $field->required ? ' required aria-required="true"' : '';
$autofocus = $field->autofocus ? ' autofocus' : '';
$checked   = $field->checked || strlen($field->value) != 0 ? ' checked' : '';

// Initialize JavaScript field attributes.
$onclick  = strlen($field->onclick) != 0 ? ' onclick="' . $field->onclick . '"' : '';
$onchange = strlen($field->onchange) != 0 ? ' onchange="' . $field->onchange . '"' : '';

echo '<input type="checkbox" name="' . $field->name . '" id="' . $field->id . '" value="'
	. htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"' . $class . $checked . $disabled
	. $onclick . $onchange . $required . $autofocus . ' />';

