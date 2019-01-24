<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Usergrouplist
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

JLoader::register('UsersHelper', JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php');

$value  = (array) $value;
$texts  = array();
$groups = UsersHelper::getGroups();

foreach ($groups as $group)
{
	if (in_array($group->value, $value))
	{
		$texts[] = htmlentities(trim($group->text, '- '));
	}
}

echo htmlentities(implode(', ', $texts));
