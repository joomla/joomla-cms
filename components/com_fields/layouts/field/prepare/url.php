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

$attributes = '';

if (!JUri::isInternal($value))
{
	$attributes = 'rel="nofollow" target="_blank"';
}

echo '<a href="' . $value . '" ' . $attributes . '>' . $value . '</a>';
