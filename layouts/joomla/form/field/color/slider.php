<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @var array $displayData Data for this field collected by ColorField
 */
extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string  $autocomplete Autocomplete attribute for the field.
 * @var   boolean $autofocus    Is autofocus enabled?
 * @var   string  $class        Classes for the input
 * @var   boolean $disabled     Is this field disabled?
 * @var   string  $display      Which kind of slider should be displayed?
 * @var   string  $default      Default value for this field
 * @var   string  $format       Format of color value
 * @var   string  $hint         Text for inputs placeholder
 * @var   string  $id           ID of field and label
 * @var   string  $name         Name of the input field
 * @var   string  $onchange     Onchange attribute for the field
 * @var   string  $onclick      Onclick attribute for the field
 * @var   string  $position     Position of input
 * @var   boolean $preview      Should the selected value be displayed separately?
 * @var   boolean $readonly     Is this field read only?
 * @var   boolean $required     Is this field required?
 * @var   string  $saveFormat   Format to save the color
 * @var   integer $size         Size attribute of the input
 * @var   string  $validate     Validation rules to apply.
 * @var   string  $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array   $dataAttributes  Miscellaneous data attributes for eg, data-*.
 */

if ($color === 'none' || is_null($color)) {
    $color = '';
}

$alpha        = $format === 'hsla' || $format === 'rgba' || $format === 'alpha';
$autocomplete = !empty($autocomplete) ? 'autocomplete="' . $autocomplete . '"' : '';
$autofocus    = $autofocus ? ' autofocus' : '';
$color        = ' data-color="' . $color . '"';
$class        = $class ? ' class="' . $class . '"' : '';
$default      = $default ? ' data-default="' . $default . '"' : '';
$disabled     = $disabled ? ' disabled' : '';
$format       = $format ? ' data-format="' . $format . '"' : '';
$hint         = strlen($hint) ? ' placeholder="' . $this->escape($hint) . '"' : '';
$onchange     = $onchange ? ' onchange="' . $onchange . '"' : '';
$onclick      = $onclick ? ' onclick="' . $onclick . '"' : '';
$preview      = $preview ? ' data-preview="' . $preview . '"' : '';
$readonly     = $readonly ? ' readonly' : '';
$saveFormat   = $saveFormat ? ' data-format="' . $saveFormat . '"' : '';
$size         = $size ? ' size="' . $size . '"' : '';
$validate     = $validate ? ' data-validate="' . $validate . '"' : '';

$displayValues = explode(',', $display);
$allSliders    = $display === 'full' || empty($display);

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('field.color-slider');

Text::script('JFIELD_COLOR_ERROR_CONVERT_HSL');
Text::script('JFIELD_COLOR_ERROR_CONVERT_HUE');
Text::script('JFIELD_COLOR_ERROR_NO_COLOUR');
Text::script('JFIELD_COLOR_ERROR_WRONG_FORMAT');
?>

<div class="color-slider-wrapper"
    <?php echo
    $class,
    $color,
    $default,
    $preview,
    $size,
    $dataAttribute;
    ?>
>
    <!-- The data to save at the end (label created in form by Joomla) -->
    <input type="text" class="form-control color-input" id="<?php echo $id; ?>" name="<?php echo $name; ?>"
        <?php echo
        $disabled,
        $readonly,
        $required,
        $saveFormat,
        $validate;
        ?>
    >
    <!-- Shows value which is allowed to manipulate like 'hue' -->
    <label for="slider-input" class="visually-hidden"><?php echo Text::_('JFIELD_COLOR_LABEL_SLIDER_INPUT'); ?></label>
    <input type="text" class="form-control" id="slider-input"
        <?php echo
        $autocomplete,
        $disabled,
        $hint,
        $onchange,
        $onclick,
        $position,
        $readonly,
        $required,
        $format,
        $validate;
        ?>
    >
    <span class="form-control-feedback"></span>

    <?php if ($allSliders || in_array('hue', $displayValues)) : ?>
        <label for="hue-slider" class="visually-hidden"><?php echo Text::_('JFIELD_COLOR_LABEL_SLIDER_HUE'); ?></label>
        <input type="range" min="0" max="360" class="form-control color-slider" id="hue-slider" data-type="hue"
            <?php echo
            $autofocus,
            $disabled
            ?>
        >
    <?php endif ?>
    <?php if ($allSliders || in_array('saturation', $displayValues)) : ?>
        <label for="saturation-slider" class="visually-hidden"><?php echo Text::_('JFIELD_COLOR_LABEL_SLIDER_SATURATION'); ?></label>
        <input type="range" min="0" max="100" class="form-control color-slider" id="saturation-slider" data-type="saturation"
            <?php echo
            $autofocus,
            $disabled
            ?>
        >
    <?php endif ?>
    <?php if ($allSliders || in_array('light', $displayValues)) : ?>
        <label for="light-slider" class="visually-hidden"><?php echo Text::_('JFIELD_COLOR_LABEL_SLIDER_LIGHT'); ?></label>
        <input type="range" min="0" max="100" class="form-control color-slider" id="light-slider" data-type="light"
            <?php echo
            $autofocus,
            $disabled
            ?>
        >
    <?php endif ?>
    <?php if ($alpha && ($allSliders || in_array('alpha', $displayValues))) : ?>
        <label for="alpha-slider" class="visually-hidden"><?php echo Text::_('JFIELD_COLOR_LABEL_SLIDER_ALPHA'); ?></label>
        <input type="range" min="0" max="100" class="form-control color-slider" id="alpha-slider" data-type="alpha"
            <?php echo
            $autofocus,
            $disabled
            ?>
        >
    <?php endif ?>
</div>
