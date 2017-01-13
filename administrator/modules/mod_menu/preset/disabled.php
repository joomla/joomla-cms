<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var  $this    JAdminCSSMenu */
/* @var  $params  Joomla\Registry\Registry */
$user = JFactory::getUser();

/**
 * Site SubMenu
 */
$this->addChild(new JMenuNode(JText::_('MOD_MENU_SYSTEM'), null, 'disabled'));

/**
 * Users Submenu
 */
if ($user->authorise('core.manage', 'com_users'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_USERS'), null, 'disabled'));
}

/**
 * Menus Submenu
 */
if ($user->authorise('core.manage', 'com_menus'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_MENUS'), null, 'disabled'));
}

/**
 * Content Submenu
 */
if ($user->authorise('core.manage', 'com_content'))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COM_CONTENT'), null, 'disabled'));
}

/**
 * Components Submenu
 */

// Get the authorised components and sub-menus.
$components = ModMenuHelper::getComponents(true);

// Check if there are any components, otherwise, don't display the components menu item
if ($components)
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_COMPONENTS'), null, 'disabled'));
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
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSIONS'), null, 'disabled'));
}

/**
 * Help Submenu
 */
if ($params->get('showhelp', 1))
{
	$this->addChild(new JMenuNode(JText::_('MOD_MENU_HELP'), null, 'disabled'));
}
