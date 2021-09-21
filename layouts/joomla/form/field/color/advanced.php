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
// both tinycolor and <joomla-field-color-picker> handle alpha automatically
// an invalid format defaults to 'hex'
$format = in_array($format, array('hex', 'rgb', 'hsl', 'rgba', 'hsla'), true) ? str_replace('a', '', $format) : 'hex';

// all formats require color component labels, color names, format and other labels required for accessibility
$labelStrings = '';
$labelStrings .= ' alphaLabel="' . Text::_('JFIELD_COLOR_ADVANCED_ALPHA') . '"';
$labelStrings .= ' appearanceLabel="' . Text::_('JFIELD_COLOR_ADVANCED_COLOR_APPEARANCE') . '"';
$labelStrings .= ' hexLabel="' . Text::_('JFIELD_COLOR_ADVANCED_HEX') . '"';
$labelStrings .= ' redLabel="' . Text::_('JFIELD_COLOR_ADVANCED_RED') . '"';
$labelStrings .= ' greenLabel="' . Text::_('JFIELD_COLOR_ADVANCED_GREEN') . '"';
$labelStrings .= ' blueLabel="' .Text::_('JFIELD_COLOR_ADVANCED_BLUE') . '"';
$labelStrings .= ' hueLabel="' . Text::_('JFIELD_COLOR_ADVANCED_HUE') . '"';
$labelStrings .= ' saturationLabel="' . Text::_('JFIELD_COLOR_ADVANCED_SATURATION') . '"';
$labelStrings .= ' lightnessLabel="' . Text::_('JFIELD_COLOR_ADVANCED_LIGHTNESS') . '"';
$labelStrings .= ' colorLabels="' . Text::_('JFIELD_COLOR_ADVANCED_COLOR_NAMES') . '"';
$labelStrings .= ' formatLabel="' . Text::_('JFIELD_COLOR_ADVANCED_FORMAT_' . strtoupper($format)) . '"';

if ($required)
{
	$labelStrings .= ' requiredLabel="' . Text::_('JOPTION_REQUIRED') . '"';
}

if ($keywords !== 'false')
{
	$labelStrings .= ' toggleLabel="' . Text::_('JFIELD_COLOR_ADVANCED_PRESETS_BTN_LABEL') . '"';
	$labelStrings .= ' menuLabel="' . Text::_('JFIELD_COLOR_ADVANCED_PRESETS_LABEL') . '"';
}

$labelStrings .= ' inputLabel="' . Text::_('JFIELD_COLOR_ADVANCED_INPUT_LABEL') . '"';
$labelStrings .= ' pickerLabel="' . Text::_('JFIELD_COLOR_ADVANCED_COLOR_PICKER') . '"';

// set attributes
$disabled = $disabled ? ' disabled' : '';
$required = $required ? ' required' : '';
$readonly = $readonly ? ' readonly' : '';
$keywords = !empty($keywords) ? ' keywords="' . $this->escape($keywords) . '"' : '';
$onchange = !empty($onchange) ? ' onchange="' . $this->escape($onchange) . '"' : '';
$class = !empty($class) ? ' class="' . trim($class) . '"' : '';
$hint = strlen($hint) ? ' placeholder="' . $this->escape($hint) . '"' : '';

$format = ' format="' . $format . '"';
$autocomplete = ' autocomplete="' . (!empty($autocomplete) ? $autocomplete : 'off') . '"';
$spellcheck = ' spellcheck="false"';
$value = ' value="' . $this->escape($value) . '"';

// The dir="rtl" attribute will set all color picker style
// color values of any format can never be RTL
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
		$labelStrings,
		$keywords,
		$spellcheck,
		$dataAttribute;
	?>>
	<input type="hidden">
</joomla-field-color-picker>