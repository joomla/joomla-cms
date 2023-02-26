<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Sql
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;

$value = $field->value;

if ($value == '') {
    return;
}

$db    = Factory::getDbo();
$value = (array) $value;
$query = $db->getQuery(true);
$sql   = $fieldParams->get('query', '');

$bindNames = $query->bindArray($value, ParameterType::STRING);

// Run the query with a having condition because it supports aliases
$query->setQuery($sql . ' HAVING ' . $db->quoteName('value') . ' IN (' . implode(',', $bindNames) . ')');

try {
    $db->setQuery($query);
    $items = $db->loadObjectList();
} catch (Exception $e) {
    // If the query failed, we fetch all elements
    $db->setQuery($sql);
    $items = $db->loadObjectList();
}

$texts = array();

foreach ($items as $item) {
    if (in_array($item->value, $value)) {
        $texts[] = $item->text;
    }
}

echo htmlentities(implode(', ', $texts));
