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

if (!$value)
{
	return;
}

$value   = (array) $value;
$texts   = array();$options = JFormAbstractlist::getOptionsFromField($field);

foreach ($options as $index => $optionsValue)
{
	if (in_array($index, $value))
	{
		$texts[] = $optionsValue;
	}
}

echo htmlentities(implode(', ', $texts));
