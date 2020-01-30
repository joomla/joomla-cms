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
	
	$path = $imagedata;
	$alt = ' alt=""';
	
	// Parameter alt_text is set to YES
	if (json_decode($imagedata, true) !== null)
	{
		$accessiblemedia = json_decode($imagedata, true)['accessiblemedia'];
		$path = $accessiblemedia['imagefile'];
		$alt = ' alt="' . htmlentities($accessiblemedia['alt_text'], ENT_COMPAT, 'UTF-8', true) . '"';
	}

	$buffer .= sprintf('<img src="%s"%s%s>',
		htmlentities($path, ENT_COMPAT, 'UTF-8', true),
		$class,
		$alt
	);
}

echo $buffer;
