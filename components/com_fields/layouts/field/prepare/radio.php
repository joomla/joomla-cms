<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

if (!key_exists('field', $displayData))
{
	return;
}

$field = $displayData['field'];
$value = $field->value;

if ($value == '')
{
	return;
}

$value   = (array) $value;
$texts   = array();
$options = JFormFieldList::getOptionsFromField($field);

foreach ($options as $optionValue => $optionText)
{
	if (in_array((string) $optionValue, $value))
	{
		$texts[] = JText::_($optionText);
	}
}


echo htmlentities(implode(', ', $texts));
