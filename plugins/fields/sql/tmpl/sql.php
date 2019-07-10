<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Sql
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;

$value = $field->value;

if ($value == '')
{
	return;
}

$db    = Factory::getDbo();
$value = (array) $value;
$query = $db->getQuery(true);
$sql   = $fieldParams->get('query', '');

$bindNames = $query->bindArray($value, ParameterType::STRING);

// Run the query with a having condition because it supports aliases
$sql .= ' HAVING VALUE IN (' . implode(',', $bindNames) . ')';

$query->setQuery($sql);

try
{
	$items = $db->loadObjectlist();
}
catch (Exception $e)
{
	// If the query failed, we fetch all elements
	$query->setQuery($fieldParams->get('query', ''));
	$db->setQuery($query);
	$items = $db->loadObjectlist();
}

$texts = array();

foreach ($items as $item)
{
	if (in_array($item->value, $value))
	{
		$texts[] = $item->text;
	}
}

echo htmlentities(implode(', ', $texts));
