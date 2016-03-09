<?php
/**
 * @package    Fields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (! key_exists('field', $displayData))
{
	return;
}

$field = $displayData['field'];
$value = $field->value;
if (! $value)
{
	return;
}

$attributes = '';
if (! JUri::isInternal($value))
{
	$attributes = 'rel="nofollow" target="_blank"';
}
echo '<a href="' . $value . '" ' . $attributes . '>' . $value . '</a>';
