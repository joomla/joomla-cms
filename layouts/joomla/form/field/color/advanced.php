<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

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
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellchec       Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 * @var   array    $checked         Is this field checked?
 * @var   array    $position        Position of input.
 * @var   array    $control         The forms control.
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attributes for eg, data-*.
 */

if ($validate !== 'color' && in_array($format, ['rgb', 'rgba'], true)) {
    $alpha = ($format === 'rgba');
    $placeholder = $alpha ? 'rgba(0, 0, 0, 0.5)' : 'rgb(0, 0, 0)';
} else {
    $placeholder = '#rrggbb';
}

$inputclass   = ($keywords && ! in_array($format, ['rgb', 'rgba'], true)) ? ' keywords' : ' ' . $format;
$class        = ' class="form-control ' . trim('minicolors ' . $class) . ($validate === 'color' ? '' : $inputclass) . '"';
$control      = $control ? ' data-control="' . $control . '"' : '';
$format       = $format ? ' data-format="' . $format . '"' : '';
$keywords     = $keywords ? ' data-keywords="' . $keywords . '"' : '';
$colors       = $colors ? ' data-colors="' . $colors . '"' : '';
$validate     = $validate ? ' data-validate="' . $validate . '"' : '';
$disabled     = $disabled ? ' disabled' : '';
$readonly     = $readonly ? ' readonly' : '';
$hint         = strlen($hint) ? ' placeholder="' . $this->escape($hint) . '"' : ' placeholder="' . $placeholder . '"';
$onchange     = $onchange ? ' onchange="' . $onchange . '"' : '';
$required     = $required ? ' required' : '';
$autocomplete = !empty($autocomplete) ? ' autocomplete="' . $autocomplete . '"' : '';

// Force LTR input value in RTL, due to display issues with rgba/hex colors
$direction = $lang->isRtl() ? ' dir="ltr" style="text-align:right"' : '';

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->usePreset('minicolors')
    ->useScript('field.color-adv');
?>
<input type="text" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo $this->escape($color); ?>"<?php
    echo $hint,
        $class,
        $position,
        $control,
        $readonly,
        $disabled,
        $required,
        $onchange,
        $autocomplete,
        $autofocus,
        $format,
        $keywords,
        $direction,
        $validate,
        $dataAttribute;
?>/>
