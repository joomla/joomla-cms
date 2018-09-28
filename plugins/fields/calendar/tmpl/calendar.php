<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Calendar
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

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

$formatString =  $field->fieldparams->get('showtime', 0) ? 'DATE_FORMAT_LC5' : 'DATE_FORMAT_LC4';

echo htmlentities(JHtml::_('date', $value, JText::_($formatString)));
