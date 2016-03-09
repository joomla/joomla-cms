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

$texts = array();
foreach ($value as $userId)
{
	if (! $userId)
	{
		continue;
	}
	$user = JFactory::getUser($userId);
	if ($user)
	{
		$texts[] = $user->name;
	}
	else
	{
		$texts[] = $userId;
	}
}

echo htmlentities(implode(', ', $texts));
