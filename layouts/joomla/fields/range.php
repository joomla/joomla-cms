<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', false, true);

$field = $displayData['field'];

// Initialize some field attributes.
$max      = $field->max != 0 ? ' max="' . $field->max . '"' : '';
$min      = $field->min != 0 ? ' min="' . $field->min . '"' : '';
$step     = $field->step != 0 ? ' step="' . $field->step . '"' : '';
$class    = strlen($field->class) != 0 ? ' class="' . $field->class . '"' : '';
$readonly = $field->readonly ? ' readonly' : '';
$disabled = $field->disabled ? ' disabled' : '';

$autofocus = $field->autofocus ? ' autofocus' : '';

$value = (float) $field->value;
$value = $value < $field->min ? $field->min : $value;

// Initialize JavaScript field attributes.
$onchange = strlen($field->onchange) != 0 ? ' onchange="' . $field->onchange . '"' : '';

echo '<input type="range" name="' . $field->name . '" id="' . $field->id . '"' . ' value="'
	. htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"' . $class . $disabled . $readonly
	. $onchange . $max . $step . $min . $autofocus . ' />';
