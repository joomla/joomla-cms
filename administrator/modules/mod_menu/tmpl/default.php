<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$direction = \Joomla\CMS\Factory::getDocument()->direction == 'rtl' ? 'float-right' : '';

$menu->renderMenu('menu', $enabled ? 'nav ' . $direction : 'nav disabled ' . $direction);
