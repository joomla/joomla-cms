<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   string   $value           Value attribute of the field.
 * @var   string   $min
 * @var   string   $max
 */
extract($displayData);

$class        = !empty($class) ? 'class="form-control ' . $class . '"' : 'class="form-control"';
$readonly     = $readonly ? ' readonly' : '';
$disabled     = $disabled ? ' disabled' : '';
$required     = $required ? ' required' : '';
$hint         = strlen($hint)   ? ' placeholder="' . $hint . '"' : '';
$autocomplete = !$autocomplete ? ' autocomplete="off"' : ' autocomplete="' . $autocomplete . '"';
$autocomplete = $autocomplete == ' autocomplete="on"' ? '' : $autocomplete;
$autofocus    = $autofocus ? ' autofocus' : '';
$pattern      = !empty($pattern) ? ' pattern="' . $pattern . '"' : '';
$onchange     = !empty($onchange) ? ' onchange="' . $onchange . '"' : '';
$minAttr      = !empty($min) ? ' min="' . $this->escape($min) . '"' : '';
$maxAttr      = !empty($max) ? ' max="' . $this->escape($max) . '"' : '';

echo '<input type="datetime-local" name="' . $name . '" id="' . $id . '" value="' . $this->escape($value) . '"'
    . $class . $disabled . $readonly
            . $hint . $onchange . $required . $autocomplete . $autofocus . $pattern . $minAttr . $maxAttr . ' />';
