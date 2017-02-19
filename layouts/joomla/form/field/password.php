<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

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

if ($meter)
{
	JHtml::_('behavior.formvalidator');
	JHtml::_('script', 'system/fields/passwordstrength.js', false, true);

	$class = 'js-password-strength ' . $class;

	if ($forcePassword)
	{
		$class = $class . ' meteredPassword';
	}
}

JText::script('JFIELD_PASSWORD_INDICATE_INCOMPLETE');
JText::script('JFIELD_PASSWORD_INDICATE_COMPLETE');

$attributes = array(
	strlen($hint) ? 'placeholder="' . $hint . '"' : '',
	!$autocomplete ? 'autocomplete="off"' : '',
	!empty($class) ? 'class="form-control ' . $class . '"' : 'class="form-control"',
	$readonly ? 'readonly' : '',
	$disabled ? 'disabled' : '',
	!empty($size) ? 'size="' . $size . '"' : '',
	!empty($maxLength) ? 'maxlength="' . $maxLength . '"' : '',
	$required ? 'required aria-required="true"' : '',
	$autofocus ? 'autofocus' : '',
	!empty($minLength) ? 'data-min-length="' . $minLength . '"' : '',
	!empty($minIntegers) ? 'data-min-integers="' . $minIntegers . '"' : '',
	!empty($minSymbols) ? 'data-min-symbols="' . $minSymbols . '"' : '',
	!empty($minUppercase) ? 'data-min-uppercase="' . $minUppercase . '"' : '',
	!empty($minLowercase) ? 'data-min-lowercase="' . $minLowercase . '"' : '',
	!empty($forcePassword) ? 'data-min-force="' . $forcePassword . '"' : '',
);

?>
<input
	type="password"
	name="<?php echo $name; ?>"
	id="<?php echo $id; ?>"
	value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"
	<?php echo implode(' ', $attributes); ?>>
