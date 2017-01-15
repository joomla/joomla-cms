<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the module helper classes.
JLoader::register('ModMenuHelper', __DIR__ . '/helper.php');
JLoader::register('JAdminCssMenu', __DIR__ . '/menu.php');

$lang    = JFactory::getLanguage();
$user    = JFactory::getUser();
$input   = JFactory::getApplication()->input;
$menu    = new JAdminCssMenu;
$enabled = $input->getBool('hidemainmenu') ? false : true;

// Render the module layout
require JModuleHelper::getLayoutPath('mod_menu', $params->get('layout', 'default'));
