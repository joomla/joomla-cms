<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Utilities\ArrayHelper;

extract($displayData);

// Get some system objects.
$document = JFactory::getDocument();
$tag      = JFactory::getLanguage()->getTag();

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
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 * @var   array    $inputType       Options available for this field.
 */

$inputvalue = '';

// Build the attributes array.
$attributes = array();

empty($size)      ? null : $attributes['size'] = $size;
empty($maxlength) ? null : $attributes['maxlength'] = ' maxlength="' . $maxLength . '"';
empty($class)     ? null : $attributes['class'] = $class;
!$readonly        ? null : $attributes['readonly'] = 'readonly';
!$disabled        ? null : $attributes['disabled'] = 'disabled';
empty($onchange)  ? null : $attributes['onchange'] = $onchange;
empty($hint)      ? null : $attributes['placeholder'] = $hint;
$autocomplete     ? null : $attributes['autocomplete'] = 'off';
!$autofocus       ? null : $attributes['autofocus'] = '';

/**
 * These variables control the date/time picker
 */
$todaybutton  = !empty($todaybutton) ? (bool) $todaybutton : true;
$weeknumbers  = !empty($weeknumbers) ? (bool) $weeknumbers : false;
$showtime     = !empty($showtime) ? (bool) $showtime : false;
$filltable    = !empty($filltable) ? (bool) $filltable : true;
// $multiple     = !empty($multiple) ? (bool) $multiple : false;  //  Needed??????
$timeformat   = !empty($timeformat) ? (int) $timeformat : 24;
$minyear      = !empty($minyear) ? (int) $minyear : 1970;
$maxyear      = !empty($maxyear) ? (int) $maxyear : 2050;
$singleheader = !empty($singleheader) ?(bool) $singleheader : false;

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

JHtml::_('script', $tag . '/date-helper.js', false, true, false, false, true);
JHtml::_('script', 'system/calendar-vanilla.min.js', false, true, false, false, true);

// To keep the code simple here, run strings through JText::_() using array_map()
$callback = array('JText','_');
$weekdays_full = array_map(
	$callback, array(
		'SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'
	)
);
$weekdays_short = array_map(
	$callback,
	array(
		'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'
	)
);
$months_long = array_map(
	$callback, array(
		'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
		'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'
	)
);
$months_short = array_map(
	$callback, array(
		'JANUARY_SHORT', 'FEBRUARY_SHORT', 'MARCH_SHORT', 'APRIL_SHORT', 'MAY_SHORT', 'JUNE_SHORT',
		'JULY_SHORT', 'AUGUST_SHORT', 'SEPTEMBER_SHORT', 'OCTOBER_SHORT', 'NOVEMBER_SHORT', 'DECEMBER_SHORT'
	)
);
?>
<div class="field-calendar">
	<?php if (!$readonly && !$disabled) : ?>
	<div class="input-append">
		<?php endif; ?>
		<input type="text" name="<?php
		echo $name; ?>" value="<?php
		echo htmlspecialchars(($value != "0000-00-00 00:00:00") ? $value : '', ENT_COMPAT, 'UTF-8'); ?>"<?php echo  $attributes; ?>
			placeholder="<?php
			echo empty($description) ? null : $description; ?>" data-alt-value="<?php
		echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8') ?>" readonly />
		<button type="button" class="<?php echo ($readonly || $disabled) ? "hidden " : ''; ?>btn btn-secondary"
			id="<?php echo  $id; ?>_btn"
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
			data-min-year="<?php echo $minyear; ?>"
			data-max-year="<?php echo $maxyear; ?>"
			data-only-months-nav="<?php echo $singleheader; ?>"
			data-today_trans="<?php echo JText::_('JLIB_HTML_BEHAVIOR_TODAY'); ?>"
			data-weekdays-full="<?php echo implode("_", $weekdays_full); ?>"
			data-weekdays-short="<?php echo implode("_", $weekdays_short); ?>"
			data-months-long="<?php echo implode("_", $months_long); ?>"
			data-months-short="<?php echo implode("_", $months_short); ?>"
			data-day-first="<?php echo JText::_('JLIB_HTML_BEHAVIOR_DISPLAY_S_FIRST'); ?>"
			data-wk="<?php echo JText::_('JLIB_HTML_BEHAVIOR_WK'); ?>"
			data-time="<?php echo JText::_('JLIB_HTML_BEHAVIOR_TIME'); ?>"
			data-time-am="<?php echo JText::_('JLIB_HTML_BEHAVIOR_TIME_AM'); ?>"
			data-time-pm="<?php echo JText::_('JLIB_HTML_BEHAVIOR_TIME_PM'); ?>"
			data-cal-type="<?php echo JText::_('JLIB_HTML_BEHAVIOR_CALENDAR_TYPE'); ?>"
		><span class="icon-calendar"></span></button>
		<?php if (!$readonly && !$disabled) : ?>
	</div>
<?php endif; ?>
</div>
