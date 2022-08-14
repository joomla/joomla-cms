<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Menuitem
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

$value = (int)$field->value;
$menu  = Factory::getApplication()->getMenu()->getItem($value);

if (!$menu instanceof MenuItem) {
    return;
}

$url   = Route::_('index.php?Itemid=' . $value);
$title = $menu->title;

echo HTMLHelper::_('link', $url, $title);
