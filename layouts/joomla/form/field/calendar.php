<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

extract($displayData);

// Get some system objects.
$document = JFactory::getDocument();

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
 *
 * Calendar Specific
 * @var   string   $localesPath     The relative path for the locale file
 * @var   string   $helperPath      The relative path for the helper file
 * @var   string   $minYear         The minimum year, that will be subtracted/added to current year
 * @var   string   $maxYear         The maximum year, that will be subtracted/added to current year
 * @var   integer  $todaybutton     The today button
 * @var   integer  $weeknumbers     The week numbers display
 * @var   integer  $showtime        The time selector display
 * @var   integer  $filltable       The previous/next month filling
 * @var   integer  $timeformat      The time format
 * @var   integer  $singleheader    Display different header row for month/year
 * @var   integer  $direction       The document direction
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

// Load polyfills for older IE
JHtml::_('behavior.polyfill', array('event', 'classlist', 'map'), 'lte IE 11');

// The static assets for the calendar
JHtml::_('script', $localesPath, false, true, false, false, true);
JHtml::_('script', $helperPath, false, true, false, false, true);
JHtml::_('script', 'system/fields/calendar.min.js', false, true, false, false, true);
JHtml::_('stylesheet', 'system/fields/calendar' . $cssFileExt, array(), true);
?>
<div class="field-calendar">
	<?php if (!$readonly && !$disabled) : ?>
	<div class="input-append">
		<?php endif; ?>
		<input type="text" id="<?php echo $id; ?>" name="<?php
		echo $name; ?>" value="<?php
		echo htmlspecialchars(($value !== '0000-00-00 00:00:00') ? $value : '', ENT_COMPAT, 'UTF-8'); ?>" <?php echo $attributes; ?>
		<?php echo !empty($hint) ? 'placeholder="' . htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') . '"' : ''; ?> data-alt-value="<?php
		echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" autocomplete="off"/>
		<button type="button" class="<?php echo ($readonly || $disabled) ? 'hidden ' : ''; ?>btn btn-secondary"
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
			data-only-months-nav="<?php echo $singleheader; ?>"
			<?php echo isset($minYear) && strlen($minYear) ? 'data-min-year="' . $minYear . '"' : ''; ?>
			<?php echo isset($maxYear) && strlen($maxYear) ? 'data-max-year="' . $maxYear . '"' : ''; ?>
			title="<?php echo JText::_('JLIB_HTML_BEHAVIOR_OPEN_CALENDAR'); ?>"
		><span class="icon-calendar" aria-hidden="true"></span></button>
		<?php if (!$readonly && !$disabled) : ?>
	</div>
<?php endif; ?>
</div>
