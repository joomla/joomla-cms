<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Menu\Administrator\Menu\CssMenu;

$enabled = !$app->input->getBool('hidemainmenu');

$menu = new CssMenu($app);
$root = $menu->load($params, $enabled);
$root->level = 0;

// Render the module layout
require ModuleHelper::getLayoutPath('mod_menu', $params->get('layout', 'default'));
