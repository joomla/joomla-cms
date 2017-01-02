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

$field      = $displayData['field'];
$fieldValue = $field->value;

if ($fieldValue == '')
{
	return;
}

$fieldValue = (array) $fieldValue;
$texts      = array();
$options    = JFormAbstractlist::getOptionsFromField($field);

foreach ($options as $value => $name)
{
	if (in_array((string) $value, $fieldValue))
	{
		$texts[] = JText::_($name);
	}
}

echo htmlentities(implode(', ', $texts));
