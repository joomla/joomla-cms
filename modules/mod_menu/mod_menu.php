<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Menu\Site\Helper\MenuHelper;

$list       = MenuHelper::getList($params);
$base       = MenuHelper::getBase($params);
$active     = MenuHelper::getActive($params);
$default    = MenuHelper::getDefault();
$active_id  = $active->id;
$default_id = $default->id;
$path       = $base->tree;
$showAll    = $params->get('showAllChildren', 1);
$class_sfx  = htmlspecialchars($params->get('class_sfx', ''), ENT_COMPAT, 'UTF-8');

// Fixing PHP 8 error out if $list is not an array or countable
// When the minimum supported version of Joomla is PHP 8 it will
// be better to do the following instead:
// if(is_countable($list) && count($list)) { ...

$list = is_array($list) ? $list : [];

if (count($list)) {
    require ModuleHelper::getLayoutPath('mod_menu', $params->get('layout', 'default'));
}
