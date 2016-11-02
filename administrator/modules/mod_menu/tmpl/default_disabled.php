<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();

/**
 * Site SubMenu
 */
$menu->addChild(new JMenuNode(JText::_('MOD_MENU_SYSTEM'), null, 'class:cog fa-fw'));

/**
 * Users Submenu
 */
if ($user->authorise('core.manage', 'com_users'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS'), null, 'class:users fa-fw'));
}

/**
 * Menus Submenu
 */
if ($user->authorise('core.manage', 'com_menus'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_MENUS'), null, 'class:list fa-fw'));
}

/**
 * Media Submenu
 */
if ($user->authorise('core.manage', 'com_media'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_MEDIA_MANAGER'), null, 'class:file-picture-o fa-fw'));
}

/**
 * Content Submenu
 */
if ($user->authorise('core.manage', 'com_content'))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT'), null, 'class:file-text-o fa-fw'));
}

/**
 * Components Submenu
 */

// Get the authorised components and sub-menus.
$components = ModMenuHelper::getComponents(true);

// Check if there are any components, otherwise, don't display the components menu item
if ($components)
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_COMPONENTS'), null, 'class:cube fa-fw'));
}

/**
 * Extensions Submenu
 */
$im = $user->authorise('core.manage', 'com_installer');
$mm = $user->authorise('core.manage', 'com_modules');
$pm = $user->authorise('core.manage', 'com_plugins');
$tm = $user->authorise('core.manage', 'com_templates');
$lm = $user->authorise('core.manage', 'com_languages');

if ($im || $mm || $pm || $tm || $lm)
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSIONS'), null, 'class:cubes fa-fw'));
}

/**
 * Help Submenu
 */
if ($params->get('showhelp', 1))
{
	$menu->addChild(new JMenuNode(JText::_('MOD_MENU_HELP'), null, 'class:info-circle fa-fw'));
}

/*
 * User Submenu
 */
$menu->addChild(new JMenuNode($user->username, null, 'class:user fa-fw'));