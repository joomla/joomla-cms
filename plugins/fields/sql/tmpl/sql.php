<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Sql
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;

$value = $field->value;

if ($value == '') {
    return;
}

// SECURITY: SQL fields can execute arbitrary database queries and must only be
// rendered for users with Super User privileges (same as field creation requirement).
// This prevents an authorization bypass where a malicious super admin creates an SQL
// field with sensitive query, and then any visitor triggers query execution by viewing
// the content. This check ensures runtime authorization matches creation-time authorization.
$app  = Factory::getApplication();
$user = $app->getIdentity();

// Check if current user is a Super User (has core.admin on root asset)
if (!Access::getAssetRules(1)->allow('core.admin', $user->getAuthorisedGroups())) {
    // Non-super-admin users cannot view SQL field results for security reasons
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

$texts = [];

foreach ($items as $item) {
    if (in_array($item->value, $value)) {
        $texts[] = $item->text;
    }
}

echo htmlentities(implode(', ', $texts));
