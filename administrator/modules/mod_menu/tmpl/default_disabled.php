<?php
/**
 * @version		$Id:mod_menu.php 2463 2006-02-18 06:05:38Z webImagery $
 * @package		Joomla.Administrator
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

//
// Site SubMenu
//
$menu->addChild(new JMenuNode(JText::_('Mod_Menu_Site'), null, 'disabled'));

//
// Users Submenu
//
if ($user->authorise('core.manage', 'com_users'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS'), null, 'disabled'));
}

//
// Menus Submenu
//
if ($user->authorise('core.manage', 'com_menus'))
{
	$menu->addChild(new JMenuNode(JText::_('Menus'), null, 'disabled'));
}

//
// Content Submenu
//
if ($user->authorise('core.manage', 'com_content'))
{
	$menu->addChild(new JMenuNode(JText::_('Mod_Menu_Com_Content'), null, 'disabled'));
}

//
// Components Submenu
//
$menu->addChild(new JMenuNode(JText::_('Mod_Menu_Components'),  null, 'disabled'));

//
// Extensions Submenu
//
$im = $user->authorise('core.manage', 'com_installer');
$mm = $user->authorise('core.manage', 'com_modules');
$pm = $user->authorise('core.manage', 'com_plugins');
$tm = $user->authorise('core.manage', 'com_templates');
$lm = $user->authorise('core.manage', 'com_languages');

if ($im || $mm || $pm || $tm || $lm)
{
	$menu->addChild(new JMenuNode(JText::_('Mod_Menu_Extensions_Extensions'), null, 'disabled'));
}

//
// Help Submenu
//
$menu->addChild(new JMenuNode(JText::_('Mod_Menu_Help'),  null, 'disabled'));
