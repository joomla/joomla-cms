<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;

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
 * @var   string  $name         Name of the input field
 * @var   string  $onchange     Onchange attribute for the field
 * @var   string  $onclick      Onclick attribute for the field
 * @var   string  $position     Position of input
 * @var   boolean $preview      Should the selected value be displayed separately?
 * @var   boolean $readonly     Is this field read only?
 * @var   boolean $required     Is this field required?
 * @var   integer $size         Size attribute of the input
 * @var   string  $validate     Validation rules to apply.
 */

$alpha        = $format === 'hsla' || $format === 'rgba' || $format === 'alpha';
$autocomplete = !$autocomplete ? ' autocomplete="off"' : '';
$autofocus    = $autofocus ? ' autofocus' : '';
$color        = $color ? ' data-color="' . $color . '"' : '';
$class        = $class ? ' class="' . $class . '"' : '';
$default      = $default ? ' data-default="' . $default . '"' : '';
$disabled     = $disabled ? ' disabled' : '';
$format       = $format ? ' data-format="' . $format . '"' : '';
$hint         = strlen($hint) ? ' placeholder="' . $this->escape($hint) . '"' : '';
$onchange     = $onchange ? ' onchange="' . $onchange . '"' : '';
$onclick      = $onclick ? ' onclick="' . $onclick . '"' : '';
$preview      = $preview ? ' data-preview="' . $preview . '"' : '';
$readonly     = $readonly ? ' readonly' : '';
$size         = $size ? ' size="' . $size . '"' : '';
$validate     = $validate ? ' data-validate="' . $validate . '"' : '';

$displayValues = explode(',', $display);
$allSliders    = $display === 'full' || empty($display);
$displayData   = ' data-display="' . ($display ? $display : 'full') . '"';

HTMLHelper::_('stylesheet', 'system/fields/joomla-field-color-slider.min.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'system/fields/joomla-field-color-slider.min.js', ['version' => 'auto', 'relative' => true]);
?>

<div class="color-slider-wrapper"
	<?php echo
	$class,
	$color,
	$default,
	$displayData,
	$format,
	$preview,
	$size;
	?>
>
	<input type="text" class="form-control color-input" id="<?php echo $id; ?>" name="<?php echo $name; ?>"
		<?php echo
		$autocomplete,
		$disabled,
		$hint,
		$onchange,
		$onclick,
		$readonly,
		$required,
		$position,
		$validate;
		?>
	/>
	<?php if ($allSliders || in_array('hue', $displayValues)) : ?>
		<input type="range" min="0" max="360" class="form-control color-slider hue-slider" data-type="hue"
			<?php echo
			$autofocus,
			$disabled
			?>
		/>
	<?php endif ?>
	<?php if ($allSliders || in_array('saturation', $displayValues)) : ?>
		<input type="range" min="0" max="100" class="form-control color-slider saturation-slider" data-type="saturation"
			<?php echo
			$autofocus,
			$disabled
			?>
		/>
	<?php endif ?>
	<?php if ($allSliders || in_array('light', $displayValues)) : ?>
		<input type="range" min="0" max="100" class="form-control color-slider light-slider" data-type="light"
			<?php echo
			$autofocus,
			$disabled
			?>
		/>
	<?php endif ?>
	<?php if ($alpha && ($allSliders || in_array('alpha', $displayValues))) : ?>
		<input type="range" min="0" max="100" class="form-control color-slider alpha-slider" data-type="alpha"
			<?php echo
			$autofocus,
			$disabled
			?>
		/>
	<?php endif ?>
</div>
