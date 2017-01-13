<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$direction = JFactory::getDocument()->direction == 'rtl' ? 'float-right' : '';

require JModuleHelper::getLayoutPath('mod_menu', $enabled ? 'default_enabled' : 'default_disabled');

$menu->renderMenu('menu', $enabled ? 'nav ' . $direction : 'nav disabled ' . $direction);
