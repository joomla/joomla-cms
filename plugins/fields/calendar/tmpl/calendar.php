<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Calendar
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$value = $field->value;

if ($value == '')
{
	return;
}

// Set up the date based on the user and Joomla timezone
$date = JFactory::getDate($value, 'UTC');
$date->setTimezone(new DateTimeZone(JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'))));

// Get the format for PHP date
$format = $this->changeFormat($fieldParams->get('format', '%Y-%m-%d'), 'strftime');

// Transform the value with the date format of the plugin
$value = $date->format($format, true);

// Render the date
echo htmlentities($value);
