<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Media
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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
	$class = ' class="' . htmlentities($class, ENT_COMPAT, 'UTF-8', true) . '"';
}

$value  = (array) $field->value;
$buffer = '';

foreach ($value as $imagedata)
{
	if (!$imagedata)
	{
		continue;
	}
	
	$imagedata = json_decode($imagedata , true);

	$filename = '';
	$alt = ' alt=""';

	if ((is_array($imagedata) && array_key_exists('filename', $imagedata)))
	{
		$filename = $imagedata['filename'];
	}

	if ((is_array($imagedata) && array_key_exists('alt', $imagedata)))
	{
		$alt = ' alt="' . $imagedata['alt'] . '"';
	}
	
	$buffer .= sprintf('<img src="%s"%s%s>',
		htmlentities($filename, ENT_COMPAT, 'UTF-8', true),
		$class,
		$alt
	);
}

echo $buffer;
