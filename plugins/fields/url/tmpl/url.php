<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.URL
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

$attributes = '';

if (!JUri::isInternal($value))
{
	$attributes = ' rel="nofollow noopener noreferrer" target="_blank"';
}

echo sprintf('<a href="%s"%s>%s</a>',
	htmlspecialchars($value),
	$attributes,
	htmlspecialchars($value)
);
