<?php
/**
 * @version		$Id:mod_menu.php 2463 2006-02-18 06:05:38Z webImagery $
 * @package		Joomla.Administrator
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

//
// Site SubMenu
//
if ($enabled)
{
	$menu->addChild(
		new JMenuNode(JText::_('Site'), '#'), true
	);
	$menu->addChild(
		new JMenuNode(JText::_('Control Panel'), 'index.php', 'class:cpanel')
	);
	if ($user->authorize('core.users.manage'))
	{
		$menu->addChild(
			new JMenuNode(JText::_('Com_users_User_Manager'), 'index.php?option=com_users&view=users', 'class:user'), true
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_users_Users'), 'index.php?option=com_users&view=users', 'class:user')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_users_Groups'), 'index.php?option=com_users&view=groups', 'class:groups')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_users_Levels'), 'index.php?option=com_users&view=levels', 'class:levels')
		);

		$menu->addSeparator();
		$menu->addChild(
			new JMenuNode(JText::_('Com_users_Add_User'), 'index.php?option=com_users&task=user.add', 'class:newuser')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_users_Add_Group'), 'index.php?option=com_users&task=group.add', 'class:newgroup')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_users_Add_Level'), 'index.php?option=com_users&task=level.add', 'class:newlevel')
		);

		$menu->addSeparator();
		if ($user->authorize('core.massmail.manage'))
		{
			$menu->addChild(new JMenuNode(JText::_('Mass Mail'), 'index.php?option=com_massmail', 'class:massmail'));
			$menu->addSeparator();
		}
		$menu->getParent();
	}
	$menu->addSeparator();
	if ($user->authorize('core.config.manage')) {
		$menu->addChild(new JMenuNode(JText::_('Configuration'), 'index.php?option=com_config', 'class:config'));
		$menu->addSeparator();
	}

$com = $user->authorize('core.config.manage');
$chm = $user->authorize('core.checkin.manage');
$cam = $user->authorize('core.cache.manage');

if ($com || $chm || $cam )

	{
		$menu->addChild(
			new JMenuNode(JText::_('Site Maintenance'), '#', 'class:maintenance'), true
		);

		$menu->addChild(new JMenuNode(JText::_('Global Checkin'), 'index.php?option=com_checkin', 'class:checkin'));

		$menu->addSeparator();
		$menu->addChild(new JMenuNode(JText::_('Clear Cache'), 'index.php?option=com_cache', 'class:clear'));
		$menu->addChild(new JMenuNode(JText::_('Purge Expired Cache'), 'index.php?option=com_cache&view=purge', 'class:purge'));
		$menu->getParent();
	}

	$menu->addSeparator();
		$menu->addChild(
		new JMenuNode(JText::_('System Info'), 'index.php?option=com_admin&view=sysinfo', 'class:info')
	);
	$menu->addSeparator();

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
		$menu->addChild(
			new JMenuNode(JText::_('Menus'), '#'), true
		);
		$menu->addChild(
			new JMenuNode(JText::_('Menu Manager'), 'index.php?option=com_menus&view=menus', 'class:menumgr')
		);
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
// Content SubMenu
//

if ($user->authorize('com_content.manage'))
{
	if ($enabled)
	{
		$menu->addChild(
			new JMenuNode(JText::_('Content'), '#'), true
		);
		//
		$menu->addChild(
			new JMenuNode(JText::_('Com_content_Article_Manager'), 'index.php?option=com_content', 'class:article'), true
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_content_Articles'), 'index.php?option=com_content&view=articles', 'class:article')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_content_Categories'), 'index.php?option=com_categories&extension=com_content', 'class:category')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_content_Featured'), 'index.php?option=com_content&view=featured', 'class:featured')
		);
		$menu->addSeparator();
		$menu->addChild(
			new JMenuNode(JText::_('Com_content_New_article'), 'index.php?option=com_content&task=article.add', 'class:newarticle')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_content_New_category'), 'index.php?option=com_categories&task=category.add&extension=com_content', 'class:newcategory')
		);
		$menu->getParent();

		if ($user->authorize('core.media.manage')) {
			$menu->addChild(new JMenuNode(JText::_('Media Manager'), 'index.php?option=com_media', 'class:media'));
		}

		$menu->getParent();
	}
	else {
		$menu->addChild(new JMenuNode(JText::_('Content'), null, 'disabled'));
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
	$components = ModMenuHelper::getComponents(array('com_content'));

	foreach ($components as &$component)
	{
		$text = $lang->hasKey($component->option) ? JText::_($component->option) : $component->name;

		if (!empty($component->submenu))
		{
			// This component has a db driven submenu.
			$menu->addChild(new JMenuNode($text, $component->admin_menu_link, $component->admin_menu_img), true);
			foreach ($component->submenu as $sub)
			{
				$key  = $component->option.'_'.str_replace(' ', '_', $sub->name);
				$text = $lang->hasKey($key) ? JText::_($key) : $sub->name;
				$menu->addChild(new JMenuNode($text, $sub->admin_menu_link, $sub->admin_menu_img));
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
		$menu->addChild(new JMenuNode(JText::_('Read Messages'), 'index.php?option=com_messages', 'class:readmess'));
		$menu->addChild(new JMenuNode(JText::_('Write Message'), 'index.php?option=com_messages&task=add', 'class:writemess'));
		$menu->addSeparator();
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
	$menu->addChild(
		new JMenuNode(JText::_('Help'), '#'), true
	);
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help'), 'index.php?option=com_admin&view=help', 'class:help')
	);
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Support_Forum'), 'http://forum.joomla.org', 'class:help')
	);
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Documentation'), 'http://docs.joomla.org', 'class:help')
	);
	$menu->addSeparator();
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Extensions'), 'http://extensions.joomla.org', 'class:help')
	);
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Resources'), 'http://resources.joomla.org', 'class:help')
	);
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Community'), 'http://community.joomla.org', 'class:help')
	);
	$menu->getParent();
}
else {
	$menu->addChild(new JMenuNode(JText::_('Help'),  null, 'disabled'));
}

$menu->renderMenu('menu', $enabled ? '' : 'disabled');
