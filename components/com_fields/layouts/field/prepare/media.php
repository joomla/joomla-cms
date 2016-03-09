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

$value = (array) $value;

$buffer = '';
foreach ($value as $path)
{
	if (! $path)
	{
		continue;
	}
	$buffer .= '<img src="' . $path . '" class="' . $field->fieldparams->get('image_class') . '"/>';
}
echo $buffer;
