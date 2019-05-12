<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Color
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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

echo htmlentities($value);
