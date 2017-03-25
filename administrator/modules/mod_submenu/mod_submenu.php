<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$list    = JSubMenuHelper::getEntries();
$filters = JSubMenuHelper::getFilters();
$action  = JSubMenuHelper::getAction();

$displayMenu    = count($list);
$displayFilters = count($filters);

$hide = JFactory::getApplication()->input->getBool('hidemainmenu');

if ($displayMenu || $displayFilters)
{
	require JModuleHelper::getLayoutPath('mod_submenu', $params->get('layout', 'default'));
}
