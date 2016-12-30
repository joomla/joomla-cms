<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$value = $field->value;

if ($value == '')
{
	return;
}

$value  = (array) $value;
$buffer = '';

foreach ($value as $path)
{
	if (!$path)
	{
		continue;
	}

	$buffer .= '<img src="' . $path . '" class="' . $fieldParams->get('image_class') . '"/>';
}

echo $buffer;
