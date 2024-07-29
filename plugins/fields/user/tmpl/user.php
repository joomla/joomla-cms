<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.User
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var \Joomla\CMS\Layout\FileLayout $this */
$value = $field->value;

if ($value == '') {
    return;
}

$value = (array) $value;
$texts = [];

foreach ($value as $userId) {
    if (!$userId) {
        continue;
    }

    $user = $this->getUserFactory()->loadUserById($userId);

    if ($user) {
        // Use the Username
        $texts[] = $user->name;
        continue;
    }

    // Fallback and add the User ID if we get no JUser Object
    $texts[] = $userId;
}

echo htmlentities(implode(', ', $texts));
