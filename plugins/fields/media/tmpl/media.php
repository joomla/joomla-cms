<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Media
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

if ($field->value == '')
{
	return;
}

$class = $fieldParams->get('image_class');

if ($class)
{
	$class = ' class="' . htmlentities($class, ENT_COMPAT, 'UTF-8', true) . '"';
}

$value  = $field->value;

$buffer = '';

if ($value)
{
	$alt       = ' alt="' . htmlentities($value['alt_text'], ENT_COMPAT, 'UTF-8', true) . '"';
	$img       = HTMLHelper::cleanImageURL($value['imagefile']);
	$imgUrl    = htmlentities($img->url, ENT_COMPAT, 'UTF-8', true);
	if (file_exists($img->url))
	{
		$buffer .= sprintf('<img loading="lazy" width="%s" height="%s" src="%s"%s>',
			$img->attributes['width'],
			$img->attributes['height'],
			$imgUrl,
			$class,
			$alt
		);
	}
}

echo $buffer;
