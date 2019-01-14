<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Subfields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * One possible way to render subfields, just by iterating over the rendered values:
 */
echo '<ul>';
foreach ($field->value as $row)
{
	echo '<li>';
	$buffer = array();
	foreach (((array) $row) as $key => $value)
	{
		$buffer[] = ($key . ': ' . $value);
	}
	echo implode(', ', $buffer);
	echo '</li>';
}
echo '</ul>';
