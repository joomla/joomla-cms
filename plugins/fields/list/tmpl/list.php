<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.List
 *
 * @copyright   © 2016 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$fieldValue = $field->value;

if ($fieldValue == '')
{
	return;
}

$fieldValue = (array) $fieldValue;
$texts      = array();
$options    = $this->getOptionsFromField($field);

foreach ($options as $value => $name)
{
	if (in_array((string) $value, $fieldValue))
	{
		$texts[] = JText::_($name);
	}
}


echo htmlentities(implode(', ', $texts));
