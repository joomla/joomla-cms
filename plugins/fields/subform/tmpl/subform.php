<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Subform
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$fieldValue = $field->value;

if ($fieldValue === '')
{
	return;
}

$fieldValue = (array) $fieldValue;

$texts    = array();
$subForms = $this->getSubFormsFromField($field);

foreach ($subForms as $value => $name)
{
	foreach ($fieldValue as $index => $subForm)
	{

		if (in_array((string) $value, $subForm, true))
		{

			$texts[] = JText::_('[' . $index . '] ' . $name . ': ' . $value);
		}
	}

}

echo htmlentities(implode(', ', $texts));
