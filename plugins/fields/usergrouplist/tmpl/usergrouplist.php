<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Usergrouplist
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Users\Administrator\Helper\UsersHelper;

$value = $field->value;

if ($value == '') {
    return;
}

$value  = (array) $value;
$texts  = [];
$groups = UsersHelper::getGroups();

foreach ($groups as $group) {
    if (in_array($group->value, $value)) {
        $texts[] = htmlentities(trim((string) $group->text, '- '));
    }
}

echo htmlentities(implode(', ', $texts));
