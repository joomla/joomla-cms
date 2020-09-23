<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Language;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string    $autocomplete    Autocomplete attribute for the field.
 * @var   boolean   $autofocus       Is autofocus enabled?
 * @var   string    $class           Classes for the input.
 * @var   string    $description     Description of the field.
 * @var   boolean   $disabled        Is this field disabled?
 * @var   string    $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean   $hidden          Is this field hidden in the form?
 * @var   string    $hint            Placeholder for the field.
 * @var   string    $id              DOM id of the field.
 * @var   string    $label           Label of the field.
 * @var   string    $labelclass      Classes to apply to the label.
 * @var   boolean   $multiple        Does this field support multiple values?
 * @var   string    $name            Name of the input field.
 * @var   string    $onchange        Onchange attribute for the field.
 * @var   string    $onclick         Onclick attribute for the field.
 * @var   string    $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean   $readonly        Is this field read only?
 * @var   boolean   $repeat          Allows extensions to duplicate elements.
 * @var   boolean   $required        Is this field required?
 * @var   integer   $size            Size attribute of the input.
 * @var   boolean   $spellcheck      Spellcheck state for the form field.
 * @var   string    $validate        Validation rules to apply.
 * @var   string    $value           Value attribute of the field.
 * @var   string    $position        Is this field checked? TODO
 * @var   string    $control         Is this field checked? TODO
 * @var   string    $color           Color representation of the value in the adequate format.
 * @var   string    $format          Format of the color representation.
 * @var   string    $keywords        Keywords for the color selector control element.
 * @var   Language  $lang            Language that is active on the site.
 */

if ($validate !== 'color' && in_array($format, array('rgb', 'rgba'), true))
{
	$alpha = ($format === 'rgba');
	$placeholder = $alpha ? 'rgba(0, 0, 0, 0.5)' : 'rgb(0, 0, 0)';
}
else
{
	$placeholder = '#rrggbb';
}

$inputclass   = ($keywords && ! in_array($format, array('rgb', 'rgba'), true)) ? ' keywords' : ' ' . $format;
$class        = ' class="' . trim('minicolors ' . $class) . ($validate === 'color' ? '' : $inputclass) . '"';
$control      = $control ? ' data-control="' . $control . '"' : '';
$format       = $format ? ' data-format="' . $format . '"' : '';
$keywords     = $keywords ? ' data-keywords="' . $keywords . '"' : '';
$validate     = $validate ? ' data-validate="' . $validate . '"' : '';
$disabled     = $disabled ? ' disabled' : '';
$readonly     = $readonly ? ' readonly' : '';
$hint         = strlen($hint) ? ' placeholder="' . $this->escape($hint) . '"' : ' placeholder="' . $placeholder . '"';
$autocomplete = ! $autocomplete ? ' autocomplete="off"' : '';
$required     = $required ? ' required aria-required="true"' : '';
$onchange     = $onchange ? ' onchange="' . $onchange . '"' : '';

// Force LTR input value in RTL, due to display issues with rgba/hex colors
$direction    = $lang->isRtl() ? ' dir="ltr" style="text-align:right"' : '';

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));
JHtml::_('script', 'jui/jquery.minicolors.min.js', array('version' => 'auto', 'relative' => true));
JHtml::_('stylesheet', 'jui/jquery.minicolors.css', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'system/color-field-adv-init.min.js', array('version' => 'auto', 'relative' => true));
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
		$format,
		$keywords,
		$direction,
		$validate;
?>/>
