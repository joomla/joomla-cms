<?php

use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Menuitem
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$value = $field->value;

if ($value == '') {
    return;
}

$url   = Route::_("index.php?Itemid={$value}");
$title = Factory::getApplication()->getMenu()->getItem($value)->title;

echo "<a href=\"{$url}\">{$title}</a>";
