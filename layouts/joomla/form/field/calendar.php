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
use Joomla\Utilities\ArrayHelper;

extract($displayData);

// Get some system objects.
$document = Factory::getApplication()->getDocument();
$lang     = Factory::getApplication()->getLanguage();

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
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attributes for eg, data-*.
 *
 * Calendar Specific
 * @var   string   $helperPath      The relative path for the helper file
 * @var   string   $minYear         The minimum year, that will be subtracted/added to current year
 * @var   string   $maxYear         The maximum year, that will be subtracted/added to current year
 * @var   integer  $todaybutton     The today button
 * @var   integer  $weeknumbers     The week numbers display
 * @var   integer  $showtime        The time selector display
 * @var   integer  $filltable       The previous/next month filling
 * @var   integer  $timeformat      The time format
 * @var   integer  $singleheader    Display different header row for month/year
 * @var   string   $direction       The document direction
 * @var   string   $calendar        The calendar type
 * @var   array    $weekend         The weekends days
 * @var   integer  $firstday        The first day of the week
 * @var   string   $format          The format of date and time
 */

$inputvalue = '';

// Build the attributes array.
$attributes = array();

empty($size)      ? null : $attributes['size'] = $size;
empty($maxlength) ? null : $attributes['maxlength'] = $maxLength;
empty($class)     ? $attributes['class'] = 'form-control' : $attributes['class'] = 'form-control ' . $class;
!$readonly        ? null : $attributes['readonly'] = 'readonly';
!$disabled        ? null : $attributes['disabled'] = 'disabled';
empty($onchange)  ? null : $attributes['onchange'] = $onchange;

if ($required) {
    $attributes['required'] = '';
}

// Handle the special case for "now".
if (strtoupper($value) === 'NOW') {
    $value = Factory::getDate()->format('Y-m-d H:i:s');
}

$readonly = isset($attributes['readonly']) && $attributes['readonly'] === 'readonly';
$disabled = isset($attributes['disabled']) && $attributes['disabled'] === 'disabled';

if (is_array($attributes)) {
    $attributes = ArrayHelper::toString($attributes);
}

$calendarAttrs = [
    'data-inputfield'      => $id,
    'data-button'          => $id . '_btn',
    'data-date-format'     => $format,
    'data-firstday'        => empty($firstday) ? '' : $firstday,
    'data-weekend'         => empty($weekend) ? '' : implode(',', $weekend),
    'data-today-btn'       => $todaybutton,
    'data-week-numbers'    => $weeknumbers,
    'data-show-time'       => $showtime,
    'data-show-others'     => $filltable,
    'data-time24'          => $timeformat,
    'data-only-months-nav' => $singleheader,
    'data-min-year'        => $minYear,
    'data-max-year'        => $maxYear,
    'data-date-type'       => strtolower($calendar),
];

$calendarAttrsStr = ArrayHelper::toString($calendarAttrs);

// Add language strings
$strings = [
    // Days
    'SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY',
    // Short days
    'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT',
    // Months
    'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER',
    // Short months
    'JANUARY_SHORT', 'FEBRUARY_SHORT', 'MARCH_SHORT', 'APRIL_SHORT', 'MAY_SHORT', 'JUNE_SHORT',
    'JULY_SHORT', 'AUGUST_SHORT', 'SEPTEMBER_SHORT', 'OCTOBER_SHORT', 'NOVEMBER_SHORT', 'DECEMBER_SHORT',
    // Buttons
    'JCLOSE', 'JCLEAR', 'JLIB_HTML_BEHAVIOR_TODAY',
    // Miscellaneous
    'JLIB_HTML_BEHAVIOR_WK',
];

foreach ($strings as $c) {
    Text::script($c);
}

// These are new strings. Make sure they exist. Can be generalised at later time: eg in 4.1 version.
if ($lang->hasKey('JLIB_HTML_BEHAVIOR_AM')) {
    Text::script('JLIB_HTML_BEHAVIOR_AM');
}

if ($lang->hasKey('JLIB_HTML_BEHAVIOR_PM')) {
    Text::script('JLIB_HTML_BEHAVIOR_PM');
}

// Redefine locale/helper assets to use correct path, and load calendar assets
$document->getWebAssetManager()
    ->registerAndUseScript('field.calendar.helper', $helperPath, [], ['defer' => true])
    ->useStyle('field.calendar' . ($direction === 'rtl' ? '-rtl' : ''))
    ->useScript('field.calendar');

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
            <?php echo !empty($description) ? ' aria-describedby="' . ($id ?: $name) . '-desc"' : ''; ?>
            <?php echo $attributes; ?>
            <?php echo $dataAttribute ?? ''; ?>
            <?php echo !empty($hint) ? 'placeholder="' . htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') . '"' : ''; ?>
            data-alt-value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" autocomplete="off">
        <button type="button" class="<?php echo ($readonly || $disabled) ? 'hidden ' : ''; ?>btn btn-primary"
            id="<?php echo $id; ?>_btn"
            title="<?php echo Text::_('JLIB_HTML_BEHAVIOR_OPEN_CALENDAR'); ?>"
            <?php echo $calendarAttrsStr; ?>
        ><span class="icon-calendar" aria-hidden="true"></span>
        <span class="visually-hidden"><?php echo Text::_('JLIB_HTML_BEHAVIOR_OPEN_CALENDAR'); ?></span>
        </button>
        <?php if (!$readonly && !$disabled) : ?>
    </div>
        <?php endif; ?>
</div>
