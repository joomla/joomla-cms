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

if (in_array($format, array('rgb', 'rgba')) && $validate != 'color')
{
	$alpha = ($format === 'rgba');
	$placeholder = $alpha ? 'rgba(0, 0, 0, 0.5)' : 'rgb(0, 0, 0)';
}
else
{
	$placeholder = '#rrggbb';
}

$inputclass   = ($keywords && ! in_array($format, array('rgb', 'rgba'))) ? ' keywords' : ' ' . $format;
$class        = ' class="' . trim('minicolors ' . $class) . ($validate == 'color' ? '' : $inputclass) . '"';
$control      = $control ? ' data-control="' . $control . '"' : '';
$format       = $format ? ' data-format="' . $format . '"' : '';
$keywords     = $keywords ? ' data-keywords="' . $keywords . '"' : '';
$readonly     = $readonly ? ' readonly' : '';
$hint         = strlen($hint) ? ' placeholder="' . $hint . '"' : ' placeholder="' . $placeholder . '"';
$autocomplete = ! $autocomplete ? ' autocomplete="off"' : '';

// Force LTR input value in RTL, due to display issues with rgba/hex colors
$direction    = $lang->isRTL() ? ' dir="ltr" style="text-align:right"' : '';

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'jui/jquery.minicolors.min.js', array('version' => 'auto', 'relative' => true));
JHtml::_('stylesheet', 'jui/jquery.minicolors.css', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'system/color-field-adv-init.min.js', array('version' => 'auto', 'relative' => true));
?>
<input type="text" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php
echo htmlspecialchars($color, ENT_COMPAT, 'UTF-8'); ?>"<?php echo $hint; ?><?php echo $class; ?><?php echo
$position; ?><?php echo $control; ?><?php echo $readonly; ?><?php echo $disabled; ?><?php echo
$required; ?><?php echo $onchange; ?><?php echo $autocomplete; ?><?php echo $autofocus; ?><?php echo
$format; ?><?php echo $keywords; ?><?php echo $direction; ?><?php echo $validate; ?>/>
