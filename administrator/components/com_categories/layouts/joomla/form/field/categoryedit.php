<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

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
 * @var   boolean  $repeat          Allows extensions to duplicate elements.
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 * @var   array    $inputType       Options available for this field.
 * @var   string   $accept          File types that are accepted.
 */

$html    = array();
$classes = array();
$attr    = '';

// Initialize some field attributes.
$classes[] = !empty($class) ? $class : '';

if ($allowCustom)
{
	$customGroupText = Text::_('JGLOBAL_CUSTOM_CATEGORY');

	$classes[] = 'chosen-custom-value';
	$attr .= ' data-custom_group_text="' . $customGroupText . '" '
		. 'data-no_results_text="' . Text::_('JGLOBAL_ADD_CUSTOM_CATEGORY') . '" '
		. 'data-placeholder="' . Text::_('JGLOBAL_TYPE_OR_SELECT_CATEGORY') . '" ';
}

if ($classes)
{
	$attr .= 'class="' . implode(' ', $classes) . '"';
}

$attr .= !empty($size) ? ' size="' . $size . '"' : '';
$attr .= $multiple ? ' multiple' : '';
$attr .= $required ? ' required' : '';
$attr .= $autofocus ? ' autofocus' : '';

// To avoid user's confusion, readonly="true" should imply disabled="true".
if ($readonly || $disabled)
{
	$attr .= ' disabled="disabled"';
}

// Initialize JavaScript field attributes.
$attr .= $onchange ? ' onchange="' . $onchange . '"' : '';

// Create a read-only list (no name) with hidden input(s) to store the value(s).
if ($readonly)
{
	$html[] = HTMLHelper::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $value, $id);

	// E.g. form field type tag sends $this->value as array
	if ($multiple && is_array($value))
	{
		if (!count($value))
		{
			$value[] = '';
		}

		foreach ($value as $val)
		{
			$html[] = '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($val, ENT_COMPAT, 'UTF-8') . '">';
		}
	}
	else
	{
		$html[] = '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '">';
	}
}
else
{
	// Create a regular list.
	if (count($options) === 0)
	{
		// All Categories have been deleted, so we need a new category (This will create on save if selected).
		$options[0]            = new \stdClass;
		$options[0]->value     = 'Uncategorised';
		$options[0]->text      = 'Uncategorised';
		$options[0]->level     = '1';
		$options[0]->published = '1';
		$options[0]->lft       = '1';
	}

	$html[] = HTMLHelper::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $value, $id);
}

Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');
Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');
//Text::script('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS');

HTMLHelper::_('stylesheet', 'vendor/choices.js/choices.min.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'vendor/choices.js/choices.min.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('webcomponent', 'system/webcomponents/joomla-field-categoryedit.min.js', ['version' => 'auto', 'relative' => true]);

?>

<joomla-field-categoryedit><?php
	echo implode($html);
?></joomla-field-categoryedit>

