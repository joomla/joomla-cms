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
 * @var   boolean  $spellchec       Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 * @var   array    $checked         Is this field checked?
 * @var   array    $position        Is this field checked?
 * @var   array    $control         Is this field checked?
 */

$class    = ' class="custom-select ' . trim('simplecolors chzn-done ' . $class) . '"';
$disabled = $disabled ? ' disabled' : '';
$readonly = $readonly ? ' readonly' : '';

// Include jQuery
JHtml::_('jquery.framework');
JHtml::_('script', 'system/fields/jquery.simplecolors.min.js', array('version' => 'auto', 'relative' => true));
JHtml::_('stylesheet', 'system/jquery.simplecolors.css', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'system/fields/color-field-init.min.js', array('version' => 'auto', 'relative' => true));
?>
<select data-chosen="true" name="<?php echo $name; ?>" id="<?php echo $id; ?>"<?php
echo $disabled; ?><?php echo $readonly; ?><?php echo $required; ?><?php echo $class; ?><?php echo $position; ?><?php
echo $onchange; ?><?php echo $autofocus; ?> style="visibility:hidden;width:22px;height:1px">
	<?php foreach ($colors as $i => $c) : ?>
		<option<?php echo ($c == $color ? ' selected="selected"' : ''); ?>><?php echo $c; ?></option>
		<?php if (($i + 1) % $split == 0) : ?>
			<option>-</option>
		<?php endif; ?>
	<?php endforeach; ?>
</select>
