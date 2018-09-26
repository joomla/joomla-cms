<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Calendar
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

$value = $field->value;

if ($value == '')
{
	return;
}

if (is_array($value))
{
	$value = implode(', ', $value);
}

// Remove the %-sign as not used by PHP
$formatString = str_ireplace('%', '', $field->fieldparams->get('format', ''));

if ($formatString === '')
{
	$formatString = $field->fieldparams->get('showtime', 0) ? 'DATE_FORMAT_LC5' : 'DATE_FORMAT_LC4';
}

echo htmlentities(HTMLHelper::_('date', $value, Text::_($formatString)));
