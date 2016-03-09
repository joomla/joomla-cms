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

$db = JFactory::getDbo();
$value = (array) $value;

$condition = '';
foreach ($value as $v)
{
	if (! $v)
	{
		continue;
	}
	$condition .= ', ' . $db->q($v);
}

$query = $field->fieldparams->get('query', 'select id as value, name as text from #__users');

// Run the query with a having condition because it support aliases
$db->setQuery($query . ' having value in (' . trim($condition, ',') . ')');

$items = array();
try
{
	$items = $db->loadObjectlist();
}
catch (Exception $e)
{
	// If the query failed, we fetch all elements
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
