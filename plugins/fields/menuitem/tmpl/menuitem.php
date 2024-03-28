<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Menuitem
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$value = $field->value;

if ($value == '') {
    return;
}

$url   = \Joomla\CMS\Router\Route::_("index.php?Itemid={$value}");
$title = \Joomla\CMS\Factory::getApplication()->getMenu()->getItem($value)->title;

echo "<a href=\"{$url}\">{$title}</a>";
