<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
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
