<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

use Joomla\Utilities\ArrayHelper;

extract($displayData);

// Get some system objects.
$document = JFactory::getDocument();

$inputvalue = '';

// Build the attributes array.
$attributes = array();

empty($size)      ? null : $attributes['size'] = $size;
empty($maxlength) ? null : $attributes['maxlength'] = $maxLength;
empty($class)     ? $attributes['class'] = 'form-control' : $attributes['class'] = 'form-control ' . $class;
!$readonly        ? null : $attributes['readonly'] = 'readonly';
!$disabled        ? null : $attributes['disabled'] = 'disabled';
empty($onchange)  ? null : $attributes['onchange'] = $onchange;

if ($required)
{
	$attributes['required'] = '';
	$attributes['aria-required'] = 'true';
}

// Handle the special case for "now".
if (strtoupper($value) == 'NOW')
{
	$value = JFactory::getDate()->format('Y-m-d H:i:s');
}

$readonly = isset($attributes['readonly']) && $attributes['readonly'] == 'readonly';
$disabled = isset($attributes['disabled']) && $attributes['disabled'] == 'disabled';

if (is_array($attributes))
{
	$attributes = ArrayHelper::toString($attributes);
}

$cssFileExt = ($direction === 'rtl') ? '-rtl.css' : '.css';

// The static assets for the calendar
JHtml::_('script', $localesPath, false, true, false, false, true);
JHtml::_('script', $helperPath, false, true, false, false, true);
JHtml::_('script', 'system/fields/calendar.min.js', false, true, false, false, true);
JHtml::_('stylesheet', 'system/fields/calendar' . $cssFileExt, array(), true);
?>
<div class="field-calendar">
	<?php if (!$readonly && !$disabled) : ?>
	<div class="input-group">
		<?php endif; ?>
		<input
			type="text"
            id="<?php echo $id; ?>"
            name="<?php echo $name; ?>"
			value="<?php echo htmlspecialchars(($value !== '0000-00-00 00:00:00') ? $value : '', ENT_COMPAT, 'UTF-8'); ?>"
			<?php echo $attributes; ?>
			<?php echo !empty($hint) ? 'placeholder="' . htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') . '"' : ''; ?>
			data-alt-value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" autocomplete="off">
		<span class="input-group-append">
				<button type="button" class="<?php echo ($readonly || $disabled) ? 'hidden ' : ''; ?>btn btn-secondary"
					id="<?php echo $id; ?>_btn"
					data-inputfield="<?php echo $id; ?>"
					data-dayformat="<?php echo $format; ?>"
					data-button="<?php echo $id; ?>_btn"
					data-firstday="<?php echo JFactory::getLanguage()->getFirstDay(); ?>"
					data-weekend="<?php echo JFactory::getLanguage()->getWeekEnd(); ?>"
					data-today-btn="<?php echo $todaybutton; ?>"
					data-week-numbers="<?php echo $weeknumbers; ?>"
					data-show-time="<?php echo $showtime; ?>"
					data-show-others="<?php echo $filltable; ?>"
					data-time-24="<?php echo $timeformat; ?>"
					data-only-months-nav="<?php echo $singleheader; ?>"
					<?php echo !empty($minYear) ? 'data-min-year="' . $minYear . '"' : ''; ?>
					<?php echo !empty($maxYear) ? 'data-max-year="' . $maxYear . '"' : ''; ?>
				><span class="fa fa-calendar"></span></button>
		</span>
		<?php if (!$readonly && !$disabled) : ?>
	</div>
<?php endif; ?>
</div>
