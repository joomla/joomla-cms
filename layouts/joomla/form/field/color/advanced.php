<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
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
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attributes for eg, data-*.
 */

// set hsla / rgba to short format
// both tinycolor and <color-picker> handle alpha automatically
// an invalid format defaults to 'hex'
$format = in_array($format, array('hex', 'rgb', 'hsl', 'rgba', 'hsla'), true) ? str_replace('a', '', $format) : 'hex';

// set a generic placeholder
if ($validate !== 'color' && $format === 'rgb')
{
  $placeholder = 'rgba(0, 0, 0, 0.5) / rgb(0, 0, 0)';
}
else if ($validate !== 'color' && $format === 'hsl')
{
  $placeholder = 'hsla(0, 0%, 0%, 0.5) / hsl(0, 50%, 50%)';
}
else
{
  $placeholder = '#ff0000';
}

// set attributes
$disabled     = $disabled ? ' disabled' : '';
$readonly     = $readonly ? ' readonly' : '';
$class				=  $class . ' ' . $format;
$class				=  $class . ' ' . ($validate === 'color' ? '' : ' invalid');
$class        = ' class="' . trim($class) . '"';
$format       = ' format="' . $format . '"';
$hint         = ' placeholder="' . (strlen($hint) ?  $this->escape($hint) : $placeholder) . '"';
$autocomplete = ' autocomplete="' . (!empty($autocomplete) ?  $autocomplete : 'off') . '"';
$tabindex 		= ' tabindex="' . (!empty($tabindex) ?  $tabindex : '0') . '"';
$spellcheck 	= ' spellcheck="' . (!empty($spellcheck) ?  $spellcheck : 'false') . '"';

// Force LTR input value in RTL, due to display issues with rgba/hex colors
$direction = $lang->isRtl() ? ' dir="ltr"' : '';

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

$wa->usePreset('tinycolor')
  ->useScript('field.color-picker');

// the color-picker requires value, format and placeholder (hint)
// the color-form component of the color-picker requires labels
?>

<color-picker value="<?php echo $this->escape($color); ?>"<?php echo $format, $hint; ?>>
  <input type="hidden" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo $this->escape($color); ?>"
  <?php
    echo $hint,
      $class,
      $readonly,
      $disabled,
      $required,
      $onchange,
      $autocomplete,
      $autofocus,
      $format,
      $direction,
      $validate,
      $tabindex,
      $spellcheck,
      $dataAttribute;
  ?>/>

  <template name="hex-form">
    <label for="<?php echo $id; ?>_hex" class="hex-label">HEX:<span class="visually-hidden"><?php echo Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HEX'); ?></span></label>
    <input id="<?php echo $id; ?>_hex" name="<?php echo $id; ?>_hex" value="#000"<?php echo $hint; ?> class="color-input color-input-hex" type="text" autocomplete="off" spellcheck="false">
  </template>

  <template name="rgb-form">
    <label for="<?php echo $id; ?>_red">R:
      <span class="visually-hidden">
        <?php echo Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB') . ' - ', Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB_RED'); ?>
      </span>
    </label>
    <input id="<?php echo $id; ?>_red" name="<?php echo $id; ?>_red" value="0" class="color-input" type="number" placeholder="[0-255]" min="0" max="255" autocomplete="off" spellcheck="false">

    <label for="<?php echo $id; ?>_green">G:
      <span class="visually-hidden"><?php echo Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB') . ' - ', Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB_GREEN'); ?></span>
    </label>
    <input id="<?php echo $id; ?>_green" name="<?php echo $id; ?>_green" value="0" class="color-input" type="number" placeholder="[0-255]" min="0" max="255" autocomplete="off" spellcheck="false">

    <label for="<?php echo $id; ?>_blue">B:
      <span class="visually-hidden">
        <?php echo Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB') . ' - ', Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB_BLUE'); ?>
      </span>
    </label>
    <input id="<?php echo $id; ?>_blue" name="<?php echo $id; ?>_blue" value="0" class="color-input" type="number" placeholder="[0-255]" min="0" max="255" autocomplete="off" spellcheck="false">

    <label for="<?php echo $id; ?>_alpha">A:
      <span class="visually-hidden">
        <?php echo Text::_('JFIELD_COLOR_ADVANCED_FORMAT_RGB') . ' - ', Text::_('JFIELD_COLOR_ADVANCED_ALPHA'); ?>
      </span>
    </label>
    <input id="<?php echo $id; ?>_alpha" name="<?php echo $id; ?>_alpha" value="0" class="color-input" type="number" placeholder="[0-1]" min="0" max="1" step="0.01" autocomplete="off" spellcheck="false">
  </template>

  <template name="hsl-form">
    <label for="<?php echo $id; ?>_hue">H:
      <span class="visually-hidden">
        <?php echo Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL') . ' - ', Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL_HUE'); ?>
      </span>
    </label>
    <input id="<?php echo $id; ?>_hue" name="<?php echo $id; ?>_hue" value="0" class="color-input" type="number" placeholder="[0-360]" min="0" max="360" autocomplete="off" spellcheck="false">

    <label for="<?php echo $id; ?>_saturation">S:
      <span class="visually-hidden">
        <?php echo Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL') . ' - ', Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL_SATURATION'); ?>
      </span>
    </label>
    <input id="<?php echo $id; ?>_saturation" name="<?php echo $id; ?>_saturation" value="0" class="color-input" type="number" placeholder="[0-100]" min="0" max="100" autocomplete="off" spellcheck="false">

    <label for="<?php echo $id; ?>_lightness">L:
      <span class="visually-hidden">
        <?php echo Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL') . ' - ', Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL_LIGHTNESS'); ?>
      </span>
    </label>
    <input id="<?php echo $id; ?>_lightness" name="<?php echo $id; ?>_lightness" value="0" class="color-input" type="number" placeholder="[0-100]" min="0" max="100" autocomplete="off" spellcheck="false">

    <label for="<?php echo $id; ?>_alpha">A:
      <span class="visually-hidden">
        <?php echo Text::_('JFIELD_COLOR_ADVANCED_FORMAT_HSL') . ' - ', Text::_('JFIELD_COLOR_ADVANCED_ALPHA'); ?>
      </span>
    </label>
    <input id="<?php echo $id; ?>_alpha" name="<?php echo $id; ?>_alpha" value="0" class="color-input" type="number" placeholder="[0-1]" min="0" max="1" step="0.01" autocomplete="off" spellcheck="false">
  </template>
</color-picker>
