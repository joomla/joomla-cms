<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Media
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
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

foreach ($value as $path)
{
	if (!$path)
	{
		continue;
	}

	$buffer .= sprintf('<img src="%s"%s>',
		htmlentities($path, ENT_COMPAT, 'UTF-8', true),
		$class
	);
}

echo $buffer;
