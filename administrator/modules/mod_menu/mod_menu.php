<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Menu\Administrator\Menu\CssMenu;
use Joomla\Registry\Registry;

/** @var  Registry  $params */
$lang    = Factory::getLanguage();
$user    = Factory::getUser();
$input   = Factory::getApplication()->input;
$enabled = !$input->getBool('hidemainmenu');

$menu = new CssMenu;
$menu->load($params, $enabled);

// Render the module layout
require ModuleHelper::getLayoutPath('mod_menu', $params->get('layout', 'default'));
