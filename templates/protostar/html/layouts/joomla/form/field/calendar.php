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
$config   = JFactory::getConfig();
$user     = JFactory::getUser();
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

// If a known filter is given use it.
switch (strtoupper($filter))
{
	case 'SERVER_UTC':
		// Convert a date to UTC based on the server timezone.
		if ($value && $value != JFactory::getDbo()->getNullDate())
		{
			// Get a date object based on the correct timezone.
			$date = JFactory::getDate($value, 'UTC');
			$date->setTimezone(new DateTimeZone($config->get('offset')));

			// Transform the date string.
			$value = $date->format('Y-m-d H:i:s', true, false);
		}

		break;

	case 'USER_UTC':
		// Convert a date to UTC based on the user timezone.
		if ($value && $value != JFactory::getDbo()->getNullDate())
		{
			// Get a date object based on the correct timezone.
			$date = JFactory::getDate($value, 'UTC');

			$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

			// Transform the date string.
			$value = $date->format('Y-m-d H:i:s', true, false);
		}

		break;
}

JHtml::_('script', $tag . '/date.js', false, true, false, false, true);
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
<?php if ($readonly || $disabled) : ?>
	<input type="text" title="<?php echo ($inputvalue ? JHtml::_('date', $value, null, null) : ''); ?>" name="<?php
	echo $name; ?>" id="<?php echo $id; ?>" value="<?php
	echo htmlspecialchars(($inputvalue ? JHtml::_('date', $value, null, null) : ''), ENT_COMPAT, 'UTF-8'); ?>"<?php echo  $attributes; ?> />
<?php else : ?>
	<div class="field-calendar">
		<div class="input-append">
			<input type="text" title="<?php echo $inputvalue ? JHtml::_('date', $value, null, null) : ''; ?>" name="<?php
			echo $name; ?>" id="<?php echo $id; ?>" value="<?php
			echo htmlspecialchars(($value != "0000-00-00 00:00:00") ? $value : '', ENT_COMPAT, 'UTF-8'); ?>"<?php echo  $attributes; ?>
				placeholder="<?php
				echo empty($description) ? null : $description; ?>" data-alt-value="<?php
			echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8') ?>"/>
			<button type="button" class="btn btn-secondary"
				id="<?php echo  $id; ?>_btn"
				data-inputfield="<?php echo $id; ?>"
				data-dayformat="<?php echo $format; ?>"
				data-button="<?php echo $id; ?>_btn"
				data-firstday="<?php echo JFactory::getLanguage()->getFirstDay(); ?>"
				data-weekend="<?php echo JFactory::getLanguage()->getWeekEnd(); ?>"
				data-today_btn="<?php echo ($todaybutton === true) ? 1 : 0; ?>"
				data-week_numbers="<?php echo ($weeknumbers === true) ? 1 : 0; ?>"
				data-shows_time="<?php echo ($showtime === true) ? 1 : 0; ?>"
				data-show_others="<?php echo ($filltable === true) ? 1 : 0; ?>"
				data-time_24="<?php echo $timeformat; ?>"
				data-min_year="<?php echo $minyear; ?>"
				data-max_year="<?php echo $maxyear; ?>"
				data-only_months_nav="<?php echo ($singleheader === true) ? 1 : 0; ?>"
				data-today_trans="<?php echo JText::_('JLIB_HTML_BEHAVIOR_TODAY'); ?>"
				data-weekdays_full="<?php echo implode("_", $weekdays_full); ?>"
				data-weekdays_short="<?php echo implode("_", $weekdays_short); ?>"
				data-months_long="<?php echo implode("_", $months_long); ?>"
				data-months_short="<?php echo implode("_", $months_short); ?>"
				data-day_first="<?php echo JText::_('JLIB_HTML_BEHAVIOR_DISPLAY_S_FIRST'); ?>"
				data-wk="<?php echo JText::_('JLIB_HTML_BEHAVIOR_WK'); ?>"
				data-time="<?php echo JText::_('JLIB_HTML_BEHAVIOR_TIME'); ?>"
				data-time_am="<?php echo JText::_('JLIB_HTML_BEHAVIOR_TIME_AM'); ?>"
				data-time_pm="<?php echo JText::_('JLIB_HTML_BEHAVIOR_TIME_PM'); ?>"
				data-cal-type="<?php echo JText::_('JLIB_HTML_BEHAVIOR_CALENDAR_TYPE'); ?>"
			><span class="icon-calendar"></span></button>
		</div>
	</div>
<?php endif; ?>
