<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.User
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

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

	$user = Factory::getUser($userId);

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
