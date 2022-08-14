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
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

$value = (int) $field->value;

if ($value == '') {
    return;
}

$url   = Route::_('index.php?Itemid=' . $value);
$title = Factory::getApplication()->getMenu()->getItem($value)->title;

echo HTMLHelper::_('link', $url, $title);
