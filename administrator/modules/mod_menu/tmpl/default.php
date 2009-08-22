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
	$menu->addChild(
		new JMenuNode(JText::_('Site'), '#'), true
	);
	$menu->addChild(
		new JMenuNode(JText::_('Control Panel'), 'index.php', 'class:cpanel')
	);
	
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

		$menu->addChild(new JMenuNode(JText::_('Global_Checkin'), 'index.php?option=com_checkin', 'class:checkin'));

		$menu->addSeparator();
		$menu->addChild(new JMenuNode(JText::_('Clear_Cache'), 'index.php?option=com_cache', 'class:clear'));
		$menu->addChild(new JMenuNode(JText::_('Purge_Expired_Cache'), 'index.php?option=com_cache&view=purge', 'class:purge'));
		$menu->getParent();
	}

	$menu->addSeparator();
		$menu->addChild(
		new JMenuNode(JText::_('System Information'), 'index.php?option=com_admin&view=sysinfo', 'class:info')
	);
	$menu->addSeparator();

	$menu->addChild(new JMenuNode(JText::_('Logout'), 'index.php?option=com_login&task=logout', 'class:logout'));

	$menu->getParent();
}
else {
	$menu->addChild(new JMenuNode(JText::_('Site'), null, 'disabled'));
}


//
// Users SubMenu
//

if ($user->authorize('core.users.manage'))
	{
	if ($enabled)
	{
		$menu->addChild(
			new JMenuNode(JText::_('Com_users_Users'), '#'), true
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_users_User_Manager'), 'index.php?option=com_users&view=users', 'class:user')
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
			$menu->addChild(new JMenuNode(JText::_('Mass_Mail_Users'), 'index.php?option=com_massmail', 'class:massmail'));
			$menu->addChild(new JMenuNode(JText::_('Read_Messages'), 'index.php?option=com_messages', 'class:readmess'));
			$menu->addChild(new JMenuNode(JText::_('New_Message'), 'index.php?option=com_messages&task=add', 'class:writemess'));
		}
		$menu->getParent();
		}
	else {
		$menu->addChild(new JMenuNode(JText::_('Users'), null, 'disabled'));
	}
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
			new JMenuNode(JText::_('Com_Content'), '#'), true
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_Content_Article_Manager'), 'index.php?option=com_content', 'class:article')
		);
		
		$menu->addChild(
			new JMenuNode(JText::_('Com_content_Category_Manager'), 'index.php?option=com_categories&extension=com_content', 'class:category')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_content_Featured'), 'index.php?option=com_content&view=featured', 'class:featured')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_content_Keywords'), 'index.php?option=com_content&view=keywords', 'class:keywords')
		);
		$menu->addSeparator();
		$menu->addChild(
			new JMenuNode(JText::_('Com_content_New_article'), 'index.php?option=com_content&task=article.add', 'class:newarticle')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Com_content_New_category'), 'index.php?option=com_categories&task=category.add&extension=com_content', 'class:newcategory')
		);
		
		$menu->addSeparator();
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
			$menu->addChild(new JMenuNode($text, $component->admin_menu_link, $component->admin_menu_img));
		}
	}
	$menu->addChild(new JMenuNode(JText::_('Redirect'), 'index.php?option=com_redirect', 'class:redirect'));
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
		$menu->addChild(new JMenuNode(JText::_('Mod_Extensions_Extensions'), '#'), true);

		if ($im)
		{
			$menu->addChild(new JMenuNode(JText::_('Mod_Extensions_Extension_Manager'), 'index.php?option=com_installer', 'class:install'));
			$menu->addSeparator();
		}
		if ($mm) {
			$menu->addChild(new JMenuNode(JText::_('Mod_Extensions_Module_Manager'), 'index.php?option=com_modules', 'class:module'));
		}
		if ($pm) {
			$menu->addChild(new JMenuNode(JText::_('Mod_Extensions_Plugin_Manager'), 'index.php?option=com_plugins', 'class:plugin'));
		}
		if ($tm) {
			$menu->addChild(new JMenuNode(JText::_('Mod_Extensions_Template_Manager'), 'index.php?option=com_templates', 'class:themes'));
		}
		if ($lm) {
			$menu->addChild(new JMenuNode(JText::_('Mod_Extensions_Language_Manager'), 'index.php?option=com_languages', 'class:language'));
		}
		$menu->getParent();
	}
	else {
		$menu->addChild(new JMenuNode(JText::_('Mod_Extensions_Extensions'), null, 'disabled'));
	}
}

//
// Tools SubMenu
//
 if ($enabled)
 {
 	$lang->load('com_redirect.menu');

 	$menu->addChild(new JMenuNode(JText::_('Mod_Tools'), '#'), true);

// 	$menu->addChild(new JMenuNode(JText::_('com_redirect'), 'index.php?option=com_redirect', 'class:component'));

 	$menu->getParent();
 }
 else {
 	$menu->addChild(new JMenuNode(JText::_('Mod_Tools'),  null, 'disabled'));
 }

//
// Help SubMenu
//
if ($enabled)
{
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help'), '#'), true
	);
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Joomla_Help'), 'index.php?option=com_admin&view=help', 'class:help')
	);
	$menu->addSeparator();
	
	// TO DO: ADD TARGET=BLANK TO EXTERNAL LINKS
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Support_Forum'), 'http://forum.joomla.org', 'class:help-forum')
	);
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Documentation'), 'http://docs.joomla.org', 'class:help-docs')
	);
	$menu->addSeparator();
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Extensions'), 'http://extensions.joomla.org', 'class:help-jed')
	);
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Resources'), 'http://resources.joomla.org', 'class:help-jrd')
	);
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Community'), 'http://community.joomla.org', 'class:help-community')
	);
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Security'), 'http://developer.joomla.org/security.html', 'class:help-security')
	);
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Developer'), 'http://developer.joomla.org', 'class:help-dev')
	);
	$menu->addChild(
		new JMenuNode(JText::_('Mod_Menu_Help_Shop'), 'http://shop.joomla.org', 'class:help-shop')
	);
	$menu->getParent();
}
else {
	$menu->addChild(new JMenuNode(JText::_('Mod_Help'),  null, 'disabled'));
}

$menu->renderMenu('menu', $enabled ? '' : 'disabled');
