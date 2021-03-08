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
use Joomla\CMS\Language\Text;

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
 * @var   array    $keywords        Option to set color names like "blue" or values like "transparent" or "inherit"
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attributes for eg, data-*.
 */

// set hsla / rgba to short format
// both tinycolor and <color-picker> handle alpha automatically
// an invalid format defaults to 'hex'
$format = in_array($format, array('hex', 'rgb', 'hsl', 'rgba', 'hsla'), true) ? str_replace('a', '', $format) : 'hex';

// set a generic placeholder
if ($format === 'rgb')
{
	$placeholder = 'rgba(0,0,0,0.5) / rgb(0,0,0)';
}
elseif ($format === 'hsl')
{
	$placeholder = 'hsla(0,100%,50%,0.5) / hsl(0,100%,50%)';
}
else
{
	$placeholder = '#ff0000';
}

// set color form input labels
if ($format === 'hex')
{
	$inputLabels	=					Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HEX');
}
elseif ($format === 'rgb')
{
	$inputLabels	=					Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB') . ' - ' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB_RED');
	$inputLabels .=		',' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB') . ' - ' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB_GREEN');
	$inputLabels .=		',' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB') . ' - ' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB_BLUE');
	$inputLabels .=		',' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB') . ' - ' . Text::_('JFIELD_COLOR_ADVANCED_ALPHA');
}
elseif ($format === 'hsl')
{
	$inputLabels =					Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL') . ' - ' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL_HUE');
	$inputLabels .=		',' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL') . ' - ' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL_SATURATION');
	$inputLabels .=		',' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL') . ' - ' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL_LIGHTNESS');
	$inputLabels .=		',' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL') . ' - ' . Text::_('JFIELD_COLOR_ADVANCED_ALPHA');
}

// set attributes
$disabled			= $disabled ? ' disabled' : '';
$readonly			= $readonly ? ' readonly' : '';
$keywords			= !empty($keywords) ? ' keywords="' . $this->escape($keywords) . '"' : '';
$onchange			= !empty($onchange) ? ' onchange="' . $this->escape($onchange) . '"' : '';
$class        = !empty($class) ? ' class="' . trim($class) . '"' : '';
$format       = ' format="' . $format . '"';
$hint         = ' placeholder="' . (strlen($hint) ?  $this->escape($hint) : $placeholder) . '"';
$autocomplete	= ' autocomplete="' . (!empty($autocomplete) ? $autocomplete : 'off') . '"';
$tabindex     = ' tabindex="' . (!empty($tabindex) ? $tabindex : '0') . '"';
$spellcheck   = ' spellcheck="' . (!empty($spellcheck) ? $spellcheck : 'false') . '"';
$inputLabel		= ' inputLabel="' . Text::_('JFIELD_COLOR_SELECT', 'Select a colour') . '"';
$inputLabels	= ' inputLabels="' . $inputLabels . '"';
$value				= ' value="' . $this->escape($value) . '"';

// Force LTR input value in RTL, due to display issues with rgba/hex colors
$direction = $lang->isRtl() ? ' dir="rtl"' : '';

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

$wa->useScript('field.color-picker');
?>

<joomla-field-color-picker name="<?php echo $name; ?>" id="<?php echo $id; ?>"
	<?php echo $hint,
		$value,
		$class,
		$format,
		$onchange,
		$readonly,
		$disabled,
		$required,
		$direction, 
		$autocomplete,
		$autofocus,
		$direction,
		$validate,
		$inputLabels,
		$keywords,
		$inputLabel,
		$spellcheck,
		$dataAttribute; 
	?>>
	<input type="hidden">
</joomla-field-color-picker>