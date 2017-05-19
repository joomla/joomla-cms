<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Imagelist
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

if ($field->value == '')
{
	return;
}

$class = $fieldParams->get('image_class');

if ($class)
{
	$class = 'class="' . htmlentities($class, ENT_COMPAT, true) . '"';
}

$value  = (array) $field->value;
$buffer = '';

foreach ($value as $path)
{
	if (!$path || $path == '-1')
	{
		continue;
	}

	if ($fieldParams->get('directory', '/') !== '/')
	{
		$buffer .= sprintf('<img src="images/%s/%s" %s />',
			$fieldParams->get('directory'),
			htmlentities($path, ENT_COMPAT, true),
			$class
		);
	}
	else
	{
		$buffer .= '<img src="images/' . htmlentities($path, ENT_COMPAT, true) . '"' . $class . '/>';
	}
}

echo $buffer;
