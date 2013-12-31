<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$showhelp = $params->get('showhelp', 1);

/**
 * Site SubMenu
**/
$menu->addChild(new JMenuNode(JText::_('MOD_MENU_SYSTEM'), null, 'disabled'));

/**
 * Users Submenu
**/
if ($user->authorise('core.manage', 'com_users'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS'), null, 'disabled'));
}

/**
 * Menus Submenu
**/
if ($user->authorise('core.manage', 'com_menus'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_MENUS'), null, 'disabled'));
}

/**
 * Content Submenu
**/
if ($user->authorise('core.manage', 'com_content'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT'), null, 'disabled'));
}

/**
 * Components Submenu
**/

// Get the authorised components and sub-menus.
$components = ModMenuHelper::getComponents(true);

// Check if there are any components, otherwise, don't display the components menu item
if ($components)
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COMPONENTS'), null, 'disabled'));
}

/**
 * Extensions Submenu
**/
$im = $user->authorise('core.manage', 'com_installer');
$mm = $user->authorise('core.manage', 'com_modules');
$pm = $user->authorise('core.manage', 'com_plugins');
$tm = $user->authorise('core.manage', 'com_templates');
$lm = $user->authorise('core.manage', 'com_languages');

if ($im || $mm || $pm || $tm || $lm)
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSIONS'), null, 'disabled'));
}

/**
 * Help Submenu
**/
if ($showhelp == 1) {
$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP'), null, 'disabled'));
}
