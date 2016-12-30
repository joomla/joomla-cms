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

$value = (array) $value;
$texts = array();

foreach ($value as $userId)
{
	if (!$userId)
	{
		continue;
	}

	$user = JFactory::getUser($userId);

	if ($user)
	{
		// Use the Username
		$texts[] = $user->name;
		continue;
	}

	// Fallback and add the User ID if we get no JUser Object
	$texts[] = $userId;
}

echo htmlentities(implode(', ', $texts));
