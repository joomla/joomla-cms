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
JLoader::register('UsersHelper', JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php');

$texts = array();
foreach (UsersHelper::getGroups() as $group)
{
	if (in_array($group->value, $value))
	{
		$texts[] = htmlentities(trim($group->text, '- '));
	}
}

echo htmlentities(implode(', ', $texts));
