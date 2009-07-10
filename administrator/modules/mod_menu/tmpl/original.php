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
if ($enabled)
{
	$menu->addChild(new JMenuNode(JText::_('Site'), '#'), true);
	$menu->addChild(new JMenuNode(JText::_('Control Panel'), 'index.php', 'class:cpanel'));
	$menu->addSeparator();
	if ($user->authorize('core.users.manage')) {
		$menu->addChild(new JMenuNode(JText::_('User Manager'), 'index.php?option=com_users', 'class:user'));
	}
	if ($user->authorize('core.media.manage')) {
		$menu->addChild(new JMenuNode(JText::_('Media Manager'), 'index.php?option=com_media', 'class:media'));
	}
	$menu->addSeparator();
	if ($user->authorize('core.config.manage')) {
		$menu->addChild(new JMenuNode(JText::_('Configuration'), 'index.php?option=com_config', 'class:config'));
		$menu->addSeparator();
	}
	$menu->addChild(new JMenuNode(JText::_('Logout'), 'index.php?option=com_login&task=logout', 'class:logout'));

	$menu->getParent();
}
else {
	$menu->addChild(new JMenuNode(JText::_('Site'), null, 'disabled'));
}

//
// Menus SubMenu
//
if ($user->authorize('core.menus.manage'))
{
	if ($enabled)
	{
		$menu->addChild(new JMenuNode(JText::_('Menus'), '#'), true);
		$menu->addChild(new JMenuNode(JText::_('Menu Manager'), 'index.php?option=com_menus&view=menus', 'class:menu'));
		$menu->addSeparator();

		// Menu Types
		foreach (ModMenuHelper::getMenus() as $menuType)
		{
			$menu->addChild(
				new JMenuNode(
					$menuType->title.($menuType->home ? ' *' : ''),
					'index.php?option=com_menus&view=items&menutype='.$menuType->menutype, 'class:menu'
				)
			);
		}
		$menu->getParent();
	}
	else {
		$menu->addChild(new JMenuNode(JText::_('Menus'), null, 'disabled'));
	}
}

//
// Components SubMenu
//
if ($enabled)
{
	$db		= &JFactory::getDbo();
	$menu->addChild(new JMenuNode(JText::_('Components'), '#'), true);

	// Get the authorised components and sub-menus.
	$components = ModMenuHelper::getComponents();

	foreach ($components as &$component)
	{
		$text = $lang->hasKey($component->option) ? JText::_($component->option) : $component->name;

		if (!empty($component->submenu))
		{
			// This component has a db driven submenu.
			$menu->addChild(new JMenuNode($text, 'index.php?'.$component->admin_menu_link, $component->admin_menu_img), true);
			foreach ($component->submenu as $sub)
			{
				$key  = $component->option.'_'.str_replace(' ', '_', $sub->name);
				$text = $lang->hasKey($key) ? JText::_($key) : $sub->name;
				$menu->addChild(new JMenuNode($text, 'index.php?'.$component->admin_menu_link, $sub->admin_menu_img));
			}
			$menu->getParent();
		}
		else {
			$menu->addChild(new JMenuNode($text, $link, $component->admin_menu_img));
		}
	}
	$menu->getParent();
}
else {
	$menu->addChild(new JMenuNode(JText::_('Components'), null, 'disabled'));
}

//
// Extensions SubMenu
//
$im = $user->authorize('core.installer.manage');
$mm = $user->authorize('core.modules.manage');
$pm = $user->authorize('core.plugins.manage');
$tm = $user->authorize('core.templates.manage');
$lm = $user->authorize('core.languages.manage');

if ($im || $mm || $pm || $tm || $lm)
{
	if ($enabled)
	{
		$menu->addChild(new JMenuNode(JText::_('Extensions'), '#'), true);

		if ($im)
		{
			$menu->addChild(new JMenuNode(JText::_('Extension Manager'), 'index.php?option=com_installer', 'class:install'));
			$menu->addSeparator();
		}
		if ($mm) {
			$menu->addChild(new JMenuNode(JText::_('Module Manager'), 'index.php?option=com_modules', 'class:module'));
		}
		if ($pm) {
			$menu->addChild(new JMenuNode(JText::_('Plugin Manager'), 'index.php?option=com_plugins', 'class:plugin'));
		}
		if ($tm) {
			$menu->addChild(new JMenuNode(JText::_('Template Manager'), 'index.php?option=com_templates', 'class:themes'));
		}
		if ($lm) {
			$menu->addChild(new JMenuNode(JText::_('Language Manager'), 'index.php?option=com_languages', 'class:language'));
		}
		$menu->getParent();
	}
	else {
		$menu->addChild(new JMenuNode(JText::_('Extensions'), null, 'disabled'));
	}
}

//
// Tools SubMenu
//
if ($enabled)
{
	$lang->load('com_redirect.menu');

	$menu->addChild(new JMenuNode(JText::_('Tools'), '#'), true);

	$menu->addChild(new JMenuNode(JText::_('com_redirect'), 'index.php?option=com_redirect', 'class:component'));
	$menu->addSeparator();

	if ($user->authorize('core.messages.manage'))
	{
		$menu->addChild(new JMenuNode(JText::_('Read Messages'), 'index.php?option=com_messages', 'class:messages'));
		$menu->addChild(new JMenuNode(JText::_('Write Message'), 'index.php?option=com_messages&task=add', 'class:messages'));
		$menu->addSeparator();
	}
	if ($user->authorize('core.massmail.manage'))
	{
		$menu->addChild(new JMenuNode(JText::_('Mass Mail'), 'index.php?option=com_massmail', 'class:massmail'));
		$menu->addSeparator();
	}
	if ($user->authorize('core.checkin.manage'))
	{
		$menu->addChild(new JMenuNode(JText::_('Global Checkin'), 'index.php?option=com_checkin', 'class:checkin'));
		$menu->addSeparator();
	}
	if ($user->authorize('core.cache.manage'))
	{
		$menu->addChild(new JMenuNode(JText::_('Clean Cache'), 'index.php?option=com_cache', 'class:config'));
		$menu->addChild(new JMenuNode(JText::_('Purge Expired Cache'), 'index.php?option=com_cache&view=purge', 'class:config'));
	}

	$menu->getParent();
}
else {
	$menu->addChild(new JMenuNode(JText::_('Tools'),  null, 'disabled'));
}

//
// Help SubMenu
//
if ($enabled)
{
	$menu->addChild(new JMenuNode(JText::_('Help'), '#'), true);
	$menu->addChild(new JMenuNode(JText::_('Joomla! Help'), 'index.php?option=com_admin&view=help', 'class:help'));
	$menu->addChild(new JMenuNode(JText::_('System Info'), 'index.php?option=com_admin&view=sysinfo', 'class:info'));
	$menu->getParent();
}
else {
	$menu->addChild(new JMenuNode(JText::_('Help'),  null, 'disabled'));
}

$menu->renderMenu('menu', $enabled ? '' : 'disabled');
